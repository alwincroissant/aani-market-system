<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;
use Carbon\Carbon;

class CheckoutController extends Controller
{
    public function index()
    {
        if (!Session::has('cart')) {
            return redirect()->route('getCart')->with('error', 'Your cart is empty');
        }
        
        $oldCart = Session::get('cart');
        $cart = new \App\Cart($oldCart);
        
        return view('shop.checkout', ['products' => $cart->items, 'totalPrice' => $cart->totalPrice]);
    }

    public function postCheckout(Request $request)
    {
        if (!Session::has('cart')) {
            return redirect()->route('getCart')->with('error', 'Your cart is empty');
        }
        
        $oldCart = Session::get('cart');
        $cart = new \App\Cart($oldCart);
        
        try {
            DB::beginTransaction();
            
            // Create order
            $order = new \App\Models\Order();
            $order->customer_id = auth()->user()->customer->id;
            $order->date_placed = now();
            $order->date_shipped = Carbon::now()->addDays(5);
            $order->shipping = 10.00;
            $order->save();
            
            // Save order items
            foreach ($cart->items as $itemId => $item) {
                DB::table('orderline')->insert([
                    'orderinfo_id' => $order->orderinfo_id,
                    'item_id' => $itemId,
                    'quantity' => $item['qty'],
                    'price' => $item['item']->sell_price,
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
            return redirect()->back()->with('error', 'Order placement failed. Please try again.');
        }
    }
}
