<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;
use App\Cart;
use App\Models\Product;

class CartController extends Controller
{
    public function addToCart(Request $request, $id)
    {
        $product = Product::find($id);
        if (!$product) {
            return redirect()->back()->with('error', 'Product not found');
        }

        // Check stock availability
        if ($product->track_stock && $product->stock_quantity <= 0 && !$product->allow_backorder) {
            return redirect()->back()->with('error', 'Product is out of stock');
        }

        $quantity = $request->get('quantity', 1);
        
        // Validate quantity against stock
        if ($product->track_stock && $product->stock_quantity > 0) {
            $maxQuantity = $product->stock_quantity;
            if ($quantity > $maxQuantity) {
                return redirect()->back()->with('error', "Only {$maxQuantity} items available in stock");
            }
        }

        $oldCart = Session::has('cart') ? Session::get('cart') : null;
        $cart = new Cart($oldCart);
        $cart->add($product, $product->id, $quantity);

        Session::put('cart', $cart);
        return redirect()->back()->with('success', 'Product added to cart successfully!');
    }

    public function getCart()
    {
        if (!Session::has('cart')) {
            return view('cart.view', ['products' => [], 'totalPrice' => 0]);
        }
        $oldCart = Session::get('cart');
        $cart = new Cart($oldCart);
        
        // Debug: Log cart state
        \Log::info('Cart state: ' . json_encode([
            'has_items' => !empty($cart->items),
            'items_count' => $cart->items ? count($cart->items) : 0,
            'total_price' => $cart->totalPrice
        ]));
        
        // Load vendor relationships for cart items
        if ($cart->items) {
            foreach ($cart->items as $itemId => $item) {
                if (isset($item['item']) && $item['item']->vendor_id) {
                    $item['item']->vendor = \App\Models\Vendor::find($item['item']->vendor_id);
                }
            }
        }
        
        return view('cart.view', ['products' => $cart->items, 'totalPrice' => $cart->totalPrice]);
    }

    public function getReduceByOne($id)
    {
        $oldCart = Session::has('cart') ? Session::get('cart') : null;
        $cart = new Cart($oldCart);
        $cart->reduceByOne($id);

        if (count($cart->items) > 0) {
            Session::put('cart', $cart);
        } else {
            Session::forget('cart');
        }

        return redirect()->route('getCart');
    }

    public function getRemoveItem($id)
    {
        $oldCart = Session::has('cart') ? Session::get('cart') : null;
        $cart = new Cart($oldCart);
        $cart->removeItem($id);

        if (count($cart->items) > 0) {
            Session::put('cart', $cart);
        } else {
            Session::forget('cart');
        }

        return redirect()->route('getCart');
    }
}
