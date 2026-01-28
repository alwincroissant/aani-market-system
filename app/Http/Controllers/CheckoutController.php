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

        // If specific items were selected from the cart, filter to those only
        $selectedKeys = $request->input('selected_items', []);
        if (!empty($selectedKeys)) {
            $cart = array_intersect_key($cart, array_flip($selectedKeys));
        }

        if (empty($cart)) {
            return redirect()->route('cart.view')->with('error', 'Please select at least one item to checkout.');
        }

        // Group cart items by vendor
        $groupedCart = collect($cart)->groupBy('vendor_id');

        // Get vendor service information for each vendor
        $vendorServices = [];
        foreach ($groupedCart as $vendorId => $items) {
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

            if ($vendor) {
                $vendorServices[$vendorId] = $vendor;
            }
        }

        return view('checkout.index', compact('groupedCart', 'vendorServices'));
    }

    public function process(Request $request)
    {
        $cart = Session::get('cart', []);
        
        if (empty($cart)) {
            return redirect()->route('cart.view')->with('error', 'Your cart is empty');
        }

        $request->validate([
            'customer_name' => 'required|string|max:255',
            'customer_email' => 'required|email|max:255',
            'customer_phone' => 'required|string|max:20',
            'delivery_notes' => 'nullable|string|max:1000'
        ]);

        try {
            DB::beginTransaction();

            // Create customer record first
            $customer = DB::table('customers')->insertGetId([
                'first_name' => explode(' ', $request->customer_name)[0],
                'last_name' => implode(' ', array_slice(explode(' ', $request->customer_name), 1)),
                'phone' => $request->customer_phone,
                'delivery_address' => $request->delivery_notes,
                'created_at' => now(),
                'updated_at' => now()
            ]);

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

                // Calculate market fee (5%)
                $marketFee = $vendorSubtotal * 0.05;
                $total = $vendorSubtotal + $marketFee;

                // Generate order number
                $orderNumber = 'ORD-' . strtoupper(uniqid());
                $orderNumbers[] = $orderNumber;

                // Create order record
                $orderId = DB::table('orders')->insertGetId([
                    'order_reference' => $orderNumber,
                    'customer_id' => $customer,
                    'order_date' => now(),
                    'fulfillment_type' => $deliveryType ?? 'weekend_pickup',
                    'order_status' => 'pending',
                    'delivery_address' => $request->delivery_notes,
                    'notes' => $request->delivery_notes,
                    'created_at' => now(),
                    'updated_at' => now()
                ]);

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
            return redirect()->back()->with('error', 'Failed to place order. Please try again.');
        }
    }

    public function success()
    {
        return view('checkout.success');
    }
}
