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
        $cart    = new \App\Cart($oldCart);

        // Filter to selected items if POSTed
        $selectedItems = null;
        if ($request->isMethod('post') && $request->filled('selected_items')) {
            $selectedItems = json_decode($request->selected_items, true);
        }

        $products = $cart->items;
        $totalPrice = $cart->totalPrice;

        if ($selectedItems) {
            $products   = array_intersect_key($cart->items, array_flip($selectedItems));
            $totalPrice = collect($products)->sum('price');
            // Store selection in session for postCheckout
            Session::put('checkout_selected_items', $selectedItems);
        }

        return view('shop.checkout', ['products' => $products, 'totalPrice' => $totalPrice]);
    }

    public function postCheckout(Request $request)
    {
        if (!Session::has('cart')) {
            return redirect()->route('getCart')->with('error', 'Your cart is empty');
        }

        $oldCart = Session::get('cart');
        $cart    = new \App\Cart($oldCart);

        // Use session-stored selection, fallback to all items
        $selectedIds = Session::get('checkout_selected_items', array_keys($cart->items));
        $itemsToCheckout = array_intersect_key($cart->items, array_flip($selectedIds));

        try {
            DB::beginTransaction();

            $order = new \App\Models\Order();
            $order->customer_id = auth()->user()->customer->id;
            $order->date_placed = now();
            $order->date_shipped = Carbon::now()->addDays(5);
            $order->shipping = 10.00;
            $order->save();

            foreach ($itemsToCheckout as $itemId => $item) {
                DB::table('orderline')->insert([
                    'orderinfo_id' => $order->orderinfo_id,
                    'item_id'      => $itemId,
                    'quantity'     => $item['qty'],
                    'price'        => $item['item']->price_per_unit, // use actual price, not sell_price
                    'created_at'   => now(),
                    'updated_at'   => now()
                ]);
            }

            DB::commit();

            // Only remove checked-out items from cart, not everything
            foreach ($selectedIds as $id) {
                $cart->removeItem($id);
            }
            if (count($cart->items) > 0) {
                Session::put('cart', $cart);
            } else {
                Session::forget('cart');
            }
            Session::forget('checkout_selected_items');

            return redirect()->route('checkout.success')->with('success', 'Order placed successfully!');

        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Order placement failed: ' . $e->getMessage());
        }
    }

    public function success()
    {
        return view('checkout.success');
    }
}
