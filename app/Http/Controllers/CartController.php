<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;

class CartController extends Controller
{
    public function add(Request $request)
    {
        $request->validate([
            'product_id' => 'required|integer|min:1',
            'quantity' => 'required|integer|min:1|max:99'
        ]);

        $product = DB::table('products as p')
            ->join('vendors as v', 'p.vendor_id', '=', 'v.id')
            ->where('p.id', $request->product_id)
            ->whereNull('p.deleted_at')
            ->where('p.is_available', true)
            ->whereNull('v.deleted_at')
            ->select(
                'p.id',
                'p.product_name',
                'p.price_per_unit',
                'p.unit_type',
                'p.vendor_id',
                'v.business_name'
            )
            ->first();

        if (!$product) {
            return response()->json([
                'success' => false,
                'message' => 'Product not available'
            ], 400);
        }

        // Get current cart from session
        $cart = Session::get('cart', []);

        // Add item to cart
        $cartKey = $product->vendor_id . '_' . $product->id;
        
        if (isset($cart[$cartKey])) {
            // Update quantity if item already exists
            $newQuantity = $cart[$cartKey]['quantity'] + $request->quantity;
            if ($newQuantity > 99) {
                return response()->json([
                    'success' => false,
                    'message' => 'Maximum quantity per item is 99'
                ], 400);
            }
            $cart[$cartKey]['quantity'] = $newQuantity;
        } else {
            // Add new item
            $cart[$cartKey] = [
                'product_id' => $product->id,
                'vendor_id' => $product->vendor_id,
                'vendor_name' => $product->business_name,
                'product_name' => $product->product_name,
                'price_per_unit' => $product->price_per_unit,
                'unit_type' => $product->unit_type,
                'quantity' => $request->quantity
            ];
        }

        // Save cart to session
        Session::put('cart', $cart);

        return response()->json([
            'success' => true,
            'message' => 'Product added to cart successfully'
        ]);
    }

    public function view()
    {
        $cart = Session::get('cart', []);
        
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

        $totalItems = collect($cart)->sum('quantity');
        $totalAmount = collect($cart)->sum(function ($item) {
            return $item['price_per_unit'] * $item['quantity'];
        });

        return view('cart.view', compact('groupedCart', 'vendorServices', 'totalItems', 'totalAmount'));
    }

    public function update(Request $request)
    {
        $request->validate([
            'product_id' => 'required',
            'vendor_id' => 'required',
            'quantity' => 'required|integer|min:0|max:99'
        ]);

        $cart = Session::get('cart', []);
        $cartKey = $request->vendor_id . '_' . $request->product_id;

        if ($request->quantity == 0) {
            // Remove item from cart
            unset($cart[$cartKey]);
        } elseif (isset($cart[$cartKey])) {
            // Update quantity
            $cart[$cartKey]['quantity'] = $request->quantity;
        }

        Session::put('cart', $cart);

        return response()->json([
            'success' => true,
            'message' => 'Cart updated'
        ]);
    }

    public function clear()
    {
        Session::forget('cart');
        return redirect()->route('cart.view')->with('success', 'Cart cleared');
    }
}
