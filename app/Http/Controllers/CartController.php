<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use App\Cart;
use App\Models\Product;
use App\Models\Vendor;

class CartController extends Controller
{
    public function addToCart(Request $request, $id)
    {
        $product = Product::find($id);
        if (!$product) {
            return redirect()->back()->with('error', 'Product not found');
        }

        // Check if the vendor's store is open
        $vendor = Vendor::find($product->vendor_id);
        if (!$vendor || !$vendor->is_live) {
            $message = 'This store is currently closed. You cannot order at this time.';
            if ($request->expectsJson() || $request->header('X-Requested-With') === 'XMLHttpRequest') {
                return response()->json(['success' => false, 'message' => $message], 403);
            }
            return redirect()->back()->with('error', $message);
        }

        $oldCart = Session::has('cart') ? Session::get('cart') : null;
        $cart    = new Cart($oldCart);

        $quantity = $request->query('quantity');

        if ($quantity !== null) {
            // Fetch from JS — SET absolute quantity, never accumulate
            $quantity = max(1, (int) $quantity);
            if ($cart->items && array_key_exists($id, $cart->items)) {
                $cart->setQuantity($id, $quantity);
            } else {
                $cart->add($product, $product->id, $quantity);
            }
        } else {
            // Normal add to cart (e.g. from product page) — add 1
            $cart->add($product, $product->id, 1);
        }

        Session::put('cart', $cart);

        if ($request->expectsJson() || $request->header('X-Requested-With') === 'XMLHttpRequest') {
            return response()->json(['success' => true]);
        }

        return redirect()->back()->with('success', 'Product added to cart successfully!');
    }

    public function getCart()
{
    if (!Session::has('cart')) {
        return view('cart.view', ['products' => [], 'totalPrice' => 0]);
    }

    $oldCart = Session::get('cart');
    $cart    = new Cart($oldCart);

    if ($cart->items) {
        foreach ($cart->items as $itemId => $item) {
            // Reload product from database to get latest data (images, pricing, etc)
            $freshProduct = Product::find($item['item']->id);
            if ($freshProduct) {
                $cart->items[$itemId]['item'] = $freshProduct;
            }
            
            if (isset($item['item']) && $item['item']->vendor_id) {
                $cart->items[$itemId]['item']->vendor = Vendor::find($item['item']->vendor_id);
            }
        }
    }

    return view('cart.view', [
        'products'   => $cart->items,
        'totalPrice' => $cart->totalPrice,
    ]);
}

    public function getReduceByOne($id)
    {
        $oldCart = Session::has('cart') ? Session::get('cart') : null;
        $cart    = new Cart($oldCart);
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
        $cart    = new Cart($oldCart);
        $cart->removeItem($id);

        if (count($cart->items) > 0) {
            Session::put('cart', $cart);
        } else {
            Session::forget('cart');
        }

        return redirect()->route('getCart');
    }
}