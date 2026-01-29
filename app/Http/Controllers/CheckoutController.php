<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;

class CheckoutController extends Controller
{
    public function index(Request $request)
    {
        $cart = Session::get('cart', []);
        
        if (empty($cart)) {
            return redirect()->route('cart.view')->with('error', 'Your cart is empty');
        }

        // Get selected items from form submission
        $selectedKeys = $request->input('selected_items', []);
        
        // Debug: Log what we received
        \Log::info('Checkout request data:', [
            'all_request_data' => $request->all(),
            'selected_keys' => $selectedKeys,
            'cart_before' => $cart
        ]);
        
        // If specific items were selected from the cart, filter to those only
        if (!empty($selectedKeys) && is_array($selectedKeys)) {
            $cart = array_intersect_key($cart, array_flip($selectedKeys));
        }

        if (empty($cart)) {
            \Log::info('Cart is empty after filtering', [
                'selected_keys' => $selectedKeys,
                'cart_after' => $cart
            ]);
            return redirect()->route('cart.view')->with('error', 'Please select at least one item to checkout.');
        }

        // Debug: Log successful checkout data
        \Log::info('Proceeding to checkout with items:', ['filtered_cart' => $cart]);

        // Group cart items by vendor
        $groupedCart = collect($cart)->groupBy('vendor_id');

        // Get vendor service information for each vendor
        $vendorServices = [];
        foreach ($groupedCart as $vendorId => $items) {
            $vendor = DB::table('vendors')
                ->where('id', $vendorId)
                ->whereNull('deleted_at')
                ->select(
                    'business_name',
                    'weekend_pickup_enabled',
                    'weekday_delivery_enabled',
                    'weekend_delivery_enabled'
                )
                ->first();
            
            if ($vendor) {
                $vendorServices[$vendorId] = $vendor;
            }
        }

        // Calculate totals
        $subtotal = collect($cart)->sum(function($item) {
            return $item['price_per_unit'] * $item['quantity'];
        });
        $marketFee = 0; // Removed 5% market fee
        $total = $subtotal + $marketFee;

        // Get customer addresses
        $addresses = DB::table('customer_addresses')
            ->where('customer_id', DB::raw('(SELECT id FROM customers WHERE user_id = ' . auth()->id() . ')'))
            ->orderBy('is_default', 'desc')
            ->orderBy('created_at', 'desc')
            ->get();

        return view('checkout.details', compact('groupedCart', 'vendorServices', 'subtotal', 'marketFee', 'total', 'addresses'));
    }

    public function process(Request $request)
    {
        $cart = Session::get('cart', []);
        
        if (empty($cart)) {
            return redirect()->route('cart.view')->with('error', 'Your cart is empty');
        }

        $request->validate([
            'payment_method' => 'required|string|in:online,pickup',
            'delivery_address_id' => 'required_if:payment_method,online|exists:customer_addresses,id',
            'delivery_instructions' => 'nullable|string|max:1000',
            'pickup_instructions' => 'nullable|string|max:1000'
        ]);

        try {
            DB::beginTransaction();

            // Get customer record
            $customer = DB::table('customers')
                ->where('user_id', auth()->id())
                ->first();

            if (!$customer) {
                return redirect()->back()->with('error', 'Customer profile not found.');
            }

            // Get selected delivery address only if delivery is selected
            $deliveryAddress = null;
            if ($request->payment_method === 'online' && $request->delivery_address_id) {
                $deliveryAddress = DB::table('customer_addresses')
                    ->where('id', $request->delivery_address_id)
                    ->where('customer_id', $customer->id)
                    ->first();

                if (!$deliveryAddress) {
                    return redirect()->back()->with('error', 'Delivery address not found.');
                }
            }

            $groupedCart = collect($cart)->groupBy('vendor_id');
            $orderNumbers = [];

            foreach ($groupedCart as $vendorId => $items) {
                // Validate selected delivery type against vendor capabilities
                $vendor = DB::table('vendors')
                    ->where('id', $vendorId)
                    ->whereNull('deleted_at')
                    ->select(
                        'id',
                        'business_name',
                        'weekend_pickup_enabled',
                        'weekday_delivery_enabled',
                        'weekend_delivery_enabled'
                    )
                    ->first();

                if (!$vendor) {
                    throw new \Exception('Vendor not found for checkout.');
                }

                $deliveryType = $request->input("delivery_type_{$vendorId}");
                $allowedTypes = [];
                if ($vendor->weekend_pickup_enabled) {
                    $allowedTypes[] = 'weekend_pickup';
                }
                if ($vendor->weekday_delivery_enabled) {
                    $allowedTypes[] = 'weekday_delivery';
                }
                if ($vendor->weekend_delivery_enabled) {
                    $allowedTypes[] = 'weekend_delivery';
                }

                if (empty($allowedTypes) || !in_array($deliveryType, $allowedTypes, true)) {
                    throw new \Exception('Invalid delivery option selected for vendor: ' . $vendor->business_name);
                }

                // Calculate vendor subtotal
                $vendorSubtotal = 0;
                foreach ($items as $item) {
                    $vendorSubtotal += $item['price_per_unit'] * $item['quantity'];
                }

                // Calculate market fee (removed - set to 0)
                $marketFee = 0;
                $total = $vendorSubtotal + $marketFee;

                // Generate order number
                $orderNumber = 'ORD-' . strtoupper(uniqid());
                $orderNumbers[] = $orderNumber;

                // Create order record
                $orderId = DB::table('orders')->insertGetId([
                    'order_reference' => $orderNumber,
                    'customer_id' => $customer->id,
                    'order_date' => now(),
                    'fulfillment_type' => $deliveryType ?? 'weekend_pickup',
                    'order_status' => 'pending',
                    'delivery_address' => $deliveryAddress ? 
                        $deliveryAddress->address_line . ', ' . $deliveryAddress->city . ', ' . $deliveryAddress->province . ' ' . $deliveryAddress->postal_code : 
                        'AANI Weekend Market, 1-A Palayan Rd, FTI-ARCA South, Taguig, 1630 Metro Manila, Philippines',
                    'notes' => strpos($deliveryType, 'pickup') !== false ? 
                        $request->pickup_instructions : 
                        $request->delivery_instructions,
                    'created_at' => now(),
                    'updated_at' => now()
                ]);
                
                // Generate pickup code for pickup orders
                $pickupCode = null;
                if ($deliveryType && strpos($deliveryType, 'pickup') !== false) {
                    $pickupCode = strtoupper(substr(md5(uniqid()), 0, 6));
                    
                    // Store pickup code in separate table
                    DB::table('pickup_codes')->insert([
                        'order_id' => $orderId,
                        'code' => $pickupCode,
                        'expires_at' => now()->addDays(7), // Expires in 7 days
                        'created_at' => now(),
                        'updated_at' => now()
                    ]);
                    
                    // Update order with pickup code
                    DB::table('orders')
                        ->where('id', $orderId)
                        ->update(['pickup_code' => $pickupCode]);
                }

                // Create order items
                foreach ($items as $item) {
                    DB::table('order_items')->insert([
                        'order_id' => $orderId,
                        'vendor_id' => $vendorId,
                        'product_id' => $item['product_id'],
                        'quantity' => $item['quantity'],
                        'unit_price' => $item['price_per_unit'],
                        'item_status' => 'pending',
                        'created_at' => now(),
                        'updated_at' => now()
                    ]);
                }
            }

            DB::commit();

            // Clear cart
            Session::forget('cart');

            return redirect()->route('checkout.success')->with('success', 'Order placed successfully! Order numbers: ' . implode(', ', $orderNumbers));

        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Checkout error: ' . $e->getMessage(), [
                'request_data' => $request->all(),
                'cart_data' => $cart,
                'trace' => $e->getTraceAsString()
            ]);
            return redirect()->back()->with('error', 'Failed to place order. Please try again.');
        }
    }

    public function success()
    {
        return view('checkout.success');
    }
}
