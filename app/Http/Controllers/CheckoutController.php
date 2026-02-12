<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;
use Carbon\Carbon;

class CheckoutController extends Controller
{
    public function index(Request $request)
    {
        if (!Session::has('cart')) {
            return redirect()->route('getCart')->with('error', 'Your cart is empty');
        }
        
        $oldCart = Session::get('cart');
        $cart = new \App\Cart($oldCart);
        
        if (empty($cart->items)) {
            return redirect()->route('getCart')->with('error', 'Your cart is empty');
        }

        // Get selected items from request
        $selectedItems = $request->input('selected_items');
        if ($selectedItems) {
            $selectedItems = json_decode($selectedItems, true);
            // Filter cart items to only include selected ones
            $filteredItems = [];
            foreach ($selectedItems as $itemId) {
                if (isset($cart->items[$itemId])) {
                    $filteredItems[$itemId] = $cart->items[$itemId];
                }
            }
            $cart->items = $filteredItems;
            
            // Recalculate totals
            $cart->totalQty = 0;
            $cart->totalPrice = 0;
            foreach ($cart->items as $item) {
                $cart->totalQty += $item['qty'];
                $cart->totalPrice += $item['price'];
            }
        }

        if (empty($cart->items)) {
            return redirect()->route('getCart')->with('error', 'Please select at least one item to checkout.');
        }

        // Group cart items by vendor
        $groupedCart = [];
        foreach ($cart->items as $itemId => $item) {
            $vendorId = $item['item']->vendor_id;
            if (!isset($groupedCart[$vendorId])) {
                $groupedCart[$vendorId] = [];
            }
            $groupedCart[$vendorId][$itemId] = $item;
        }

        // Get vendor information for each vendor
        $vendorInfo = [];
        foreach ($groupedCart as $vendorId => $items) {
            $vendor = DB::table('vendors')
                ->join('users', 'vendors.user_id', '=', 'users.id')
                ->where('vendors.id', $vendorId)
                ->whereNull('vendors.deleted_at')
                ->select(
                    'vendors.id',
                    'vendors.business_name',
                    'vendors.weekend_pickup_enabled',
                    'vendors.weekday_delivery_enabled',
                    'vendors.weekend_delivery_enabled'
                )
                ->first();

            if ($vendor) {
                $vendorInfo[$vendorId] = $vendor;
            }
        }

        // Ensure customer record exists
        $customer = auth()->user()->customer;
        if (!$customer) {
            // Create customer record if it doesn't exist
            $customer = \App\Models\Customer::create([
                'user_id' => auth()->id(),
                'first_name' => auth()->user()->name ?? 'First Name',
                'last_name' => 'Last Name',
                'phone' => '',
                'delivery_address' => '',
            ]);
        } else {
            // Update customer record if first_name or last_name are empty
            if (empty($customer->first_name) || empty($customer->last_name)) {
                $nameParts = explode(' ', auth()->user()->name ?? 'First Name Last Name', 2);
                $customer->first_name = $customer->first_name ?: ($nameParts[0] ?? 'First Name');
                $customer->last_name = $customer->last_name ?: ($nameParts[1] ?? 'Last Name');
                $customer->save();
            }
        }

        // Get customer addresses
        $addresses = DB::table('customer_addresses')
            ->where('customer_id', $customer->id)
            ->orderBy('is_default', 'desc')
            ->orderBy('created_at', 'desc')
            ->get();

        return view('checkout.index', [
            'groupedCart' => $groupedCart,
            'vendorInfo' => $vendorInfo,
            'totalPrice' => $cart->totalPrice,
            'addresses' => $addresses
        ]);
    }

    public function postCheckout(Request $request)
    {
        if (!Session::has('cart')) {
            return redirect()->route('getCart')->with('error', 'Your cart is empty');
        }
        
        $oldCart = Session::get('cart');
        $cart = new \App\Cart($oldCart);
        
        $request->validate([
            'customer_name' => 'required|string|max:255',
            'customer_phone' => 'required|string|max:20',
            'delivery_notes' => 'nullable|string|max:1000'
        ]);
        
        // If customer name is empty, use the authenticated user's name
        $customerName = $request->customer_name ?: auth()->user()->name;
        $customerPhone = $request->customer_phone ?: auth()->user()->customer->phone;
        
        try {
            DB::beginTransaction();
            
            // Group cart items by vendor for processing
            $groupedCart = [];
            foreach ($cart->items as $itemId => $item) {
                $vendorId = $item['item']->vendor_id;
                if (!isset($groupedCart[$vendorId])) {
                    $groupedCart[$vendorId] = [];
                }
                $groupedCart[$vendorId][$itemId] = $item;
            }
            
            // Create order
            $orderId = DB::table('orders')->insertGetId([
                'order_reference' => 'ORD-' . strtoupper(uniqid()),
                'customer_id' => auth()->user()->customer->id,
                'order_date' => now(),
                'fulfillment_type' => 'weekend_pickup',
                'order_status' => 'pending',
                'delivery_address' => $request->delivery_notes,
                'notes' => $request->delivery_notes,
                'created_at' => now(),
                'updated_at' => now()
            ]);
            
            // Save order items with vendor information
            foreach ($cart->items as $itemId => $item) {
                DB::table('order_items')->insert([
                    'order_id' => $orderId,
                    'vendor_id' => $item['item']->vendor_id,
                    'product_id' => $itemId,
                    'quantity' => $item['qty'],
                    'unit_price' => $item['item']->price_per_unit,
                    'item_status' => 'pending',
                    'created_at' => now(),
                    'updated_at' => now()
                ]);
            }
            
            DB::commit();
            
            // Clear cart
            Session::forget('cart');
            
            return redirect()->route('home')->with('success', 'Order placed successfully!');
            
        } catch (\Exception $e) {
            DB::rollBack();
            
            // Log the error for debugging
            \Log::error('Order placement failed: ' . $e->getMessage());
            \Log::error('Stack trace: ' . $e->getTraceAsString());
            
            return redirect()->back()->with('error', 'Order placement failed. Please try again. Error: ' . $e->getMessage());
        }
    }

    public function success()
    {
        return view('checkout.success');
    }
}
