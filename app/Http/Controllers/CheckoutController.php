<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;
use App\Models\Product;
use App\Models\StockLog;

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

        // Get selected items from request and keep only those for checkout preview
        $selectedItems = $this->normalizeSelectedItems($request->input('selected_items'));
        if (!empty($selectedItems)) {
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

        // Reload product data from database to get latest images and pricing
        foreach ($cart->items as $itemId => $item) {
            $freshProduct = Product::find($item['item']->id);
            if ($freshProduct) {
                // Update the cart item with fresh product data
                $cart->items[$itemId]['item'] = $freshProduct;
            }
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

        // Ensure customer record exists and keep relation fresh
        $customer = Auth::user()->customer;
        if (!$customer) {
            // Create customer record if it doesn't exist
            $customer = \App\Models\Customer::create([
                'user_id' => Auth::id(),
                'first_name' => Auth::user()->name ?? 'First Name',
                'last_name' => 'Last Name',
                'phone' => '',
                'delivery_address' => '',
            ]);
            Auth::user()->setRelation('customer', $customer);
        } else {
            if (empty($customer->first_name) || empty($customer->last_name)) {
                $nameParts = explode(' ', Auth::user()->name ?? 'First Name Last Name', 2);
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

        // prepare customer info values for view
        $customerName = Auth::user()->name;
        if ($customer && $customer->first_name && $customer->last_name) {
            $customerName = trim($customer->first_name . ' ' . $customer->last_name);
        }
        $customerPhone = $customer->phone ?? '';

        // build selected address text
        $selectedAddress = $addresses ? $addresses->firstWhere('is_default', true) : null;
        $selectedAddressText = $selectedAddress
            ? trim($selectedAddress->address_line . ', ' . $selectedAddress->city . ($selectedAddress->province ? ', ' . $selectedAddress->province : '') . ($selectedAddress->postal_code ? ' ' . $selectedAddress->postal_code : ''))
            : 'No address selected';

        return view('checkout.index', [
            'groupedCart' => $groupedCart,
            'vendorInfo' => $vendorInfo,
            'totalPrice' => $cart->totalPrice,
            'selectedItems' => $selectedItems,
            'addresses' => $addresses,
            'customerName' => $customerName,
            'customerPhone' => $customerPhone,
            'selectedAddressText' => $selectedAddressText,
        ]);
    }

    public function postCheckout(Request $request)
    {
        if (!Session::has('cart')) {
            return redirect()->route('getCart')->with('error', 'Your cart is empty');
        }
        
        $oldCart = Session::get('cart');
        $originalCart = new \App\Cart($oldCart);
        $cart = new \App\Cart($oldCart);

        // Honor selected cart items from checkout flow.
        $selectedItems = $this->normalizeSelectedItems($request->input('selected_items'));
        if (!empty($selectedItems)) {
            $filteredItems = [];
            foreach ($selectedItems as $itemId) {
                if (isset($cart->items[$itemId])) {
                    $filteredItems[$itemId] = $cart->items[$itemId];
                }
            }

            $cart->items = $filteredItems;
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
        
        $request->validate([
            'customer_name' => 'required|string|max:255',
            'customer_phone' => 'required|string|max:20',
            'delivery_notes' => 'nullable|string|max:1000'
        ]);
        
        // If customer name is empty, use the authenticated user's name
        $customerName = $request->customer_name ?: Auth::user()->name;
        // make sure a customer record exists here too
        $customer = Auth::user()->customer;
        if (!$customer) {
            $customer = \App\Models\Customer::firstOrCreate([
                'user_id' => Auth::id()
            ], [
                'first_name' => Auth::user()->name ?? '',
                'last_name' => '',
                'phone' => '',
                'delivery_address' => '',
            ]);
            Auth::user()->setRelation('customer', $customer);
        }
        $customerPhone = $request->customer_phone ?: ($customer->phone ?? '');
        
        // Validate stock availability before creating the order
        $outOfStock = [];
        foreach ($cart->items as $itemId => $item) {
            $product = Product::find($itemId);
            if (! $product) {
                $outOfStock[] = "Product ID {$itemId} not found";
                continue;
            }

            if ($product->track_stock) {
                // If backorders are not allowed, ensure enough stock
                if (! $product->allow_backorder && $item['qty'] > $product->stock_quantity) {
                    $outOfStock[] = "{$product->product_name} has insufficient stock (available: {$product->stock_quantity})";
                }
            }
        }

        if (! empty($outOfStock)) {
            return redirect()->route('getCart')->with('error', implode('; ', $outOfStock));
        }

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

            // Determine fulfillment choices and whether delivery address is needed
            $vendorFulfillment = [];
            $hasDelivery = false;
            foreach ($groupedCart as $vendorId => $items) {
                $fulfillment = $request->input('delivery_type_' . $vendorId);
                if (!$fulfillment) {
                    DB::rollBack();
                    return redirect()->back()->with('error', 'Please select a delivery option for each shop.');
                }
                if (in_array($fulfillment, ['weekday_delivery', 'weekend_delivery'], true)) {
                    $hasDelivery = true;
                }
                $vendorFulfillment[$vendorId] = $fulfillment;
            }

            $deliveryAddressText = null;
            if ($hasDelivery) {
                $selectedAddressId = $request->input('selected_address');
                if (!$selectedAddressId) {
                    DB::rollBack();
                    return redirect()->back()->with('error', 'Please select a delivery address.');
                }

                $address = DB::table('customer_addresses')
                    ->where('id', $selectedAddressId)
                    ->where('customer_id', Auth::user()->customer->id)
                    ->first();

                if (!$address) {
                    DB::rollBack();
                    return redirect()->back()->with('error', 'Selected delivery address is invalid.');
                }

                $deliveryAddressText = trim(sprintf(
                    '%s, %s, %s%s',
                    $address->address_line,
                    $address->city,
                    $address->province ?? '',
                    $address->postal_code ? ' ' . $address->postal_code : ''
                ));
            }

            foreach ($groupedCart as $vendorId => $items) {
                $fulfillment = $vendorFulfillment[$vendorId];
                $orderId = DB::table('orders')->insertGetId([
                    'order_reference' => 'ORD-' . strtoupper(uniqid()),
                    'customer_id' => Auth::user()->customer->id,
                    'order_date' => now(),
                    'fulfillment_type' => $fulfillment,
                    'order_status' => 'pending',
                    'delivery_address' => $hasDelivery && in_array($fulfillment, ['weekday_delivery', 'weekend_delivery'], true)
                        ? $deliveryAddressText
                        : null,
                    'notes' => $request->delivery_notes,
                    'created_at' => now(),
                    'updated_at' => now()
                ]);

                foreach ($items as $itemId => $item) {
                    DB::table('order_items')->insert([
                        'order_id' => $orderId,
                        'vendor_id' => $vendorId,
                        'product_id' => $itemId,
                        'quantity' => $item['qty'],
                        'unit_price' => $item['item']->price_per_unit,
                        'item_status' => 'pending',
                        'created_at' => now(),
                        'updated_at' => now()
                    ]);

                    // Deduct stock and record StockLog when tracking is enabled
                    $product = Product::find($itemId);
                    if ($product && $product->track_stock) {
                        $previous = $product->stock_quantity;
                        $new = $previous - $item['qty'];
                        // If backorder allowed, new can be negative; otherwise floor at 0
                        if (! $product->allow_backorder) {
                            $new = max(0, $new);
                        }

                        $product->stock_quantity = $new;
                        $product->save();

                        StockLog::create([
                            'product_id' => $product->id,
                            'vendor_id' => $product->vendor_id,
                            'previous_stock' => $previous,
                            'new_stock' => $new,
                            'quantity_changed' => $new - $previous,
                            'change_type' => 'sale',
                            'notes' => 'Order ' . $orderId,
                        ]);
                    }
                }
            }
            
            DB::commit();
            
            // Remove only purchased items from cart, keep unchecked items.
            if (!empty($selectedItems)) {
                foreach ($selectedItems as $itemId) {
                    $originalCart->removeItem($itemId);
                }

                if (!empty($originalCart->items)) {
                    Session::put('cart', $originalCart);
                } else {
                    Session::forget('cart');
                }
            } else {
                Session::forget('cart');
            }
            
            return redirect()->route('home')->with('success', 'Order placed successfully!');
            
        } catch (\Exception $e) {
            DB::rollBack();
            
            // Log the error for debugging
            Log::error('Order placement failed: ' . $e->getMessage());
            Log::error('Stack trace: ' . $e->getTraceAsString());
            
            return redirect()->back()->with('error', 'Order placement failed. Please try again. Error: ' . $e->getMessage());
        }
    }

    public function success()
    {
        return view('checkout.success');
    }

    private function normalizeSelectedItems($rawSelectedItems): array
    {
        if (is_string($rawSelectedItems)) {
            $decoded = json_decode($rawSelectedItems, true);
            $rawSelectedItems = is_array($decoded) ? $decoded : [];
        }

        if (!is_array($rawSelectedItems)) {
            return [];
        }

        return array_values(array_unique(array_map(static function ($itemId) {
            return (string) $itemId;
        }, $rawSelectedItems)));
    }
}
