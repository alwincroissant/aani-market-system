<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class CustomerOrderController extends Controller
{
    public function index()
    {
        $customer = DB::table('customers')
            ->where('user_id', Auth::id())
            ->first();

        if (!$customer) {
            return redirect()->route('home')->with('error', 'Customer profile not found.');
        }

        $orders = DB::table('orders as o')
            ->join('customers as c', 'o.customer_id', '=', 'c.id')
            ->where('o.customer_id', $customer->id)
            ->orderBy('o.order_date', 'desc')
            ->select(
                'o.*',
                'c.first_name',
                'c.last_name'
            )
            ->get();

        // Calculate total for each order
        $orders = $orders->map(function($order) {
            $orderItems = DB::table('order_items')
                ->where('order_id', $order->id)
                ->get();
            $order->total_amount = $orderItems->sum(function($item) {
                return $item->unit_price * $item->quantity;
            });
            return $order;
        });

        return view('customer.orders.index', compact('orders'));
    }

    public function show($orderReference)
    {
        $customer = DB::table('customers')
            ->where('user_id', Auth::id())
            ->first();

        if (!$customer) {
            return redirect()->route('home')->with('error', 'Customer profile not found.');
        }

        $order = DB::table('orders as o')
            ->join('customers as c', 'o.customer_id', '=', 'c.id')
            ->where('o.customer_id', $customer->id)
            ->where('o.order_reference', $orderReference)
            ->select(
                'o.*',
                'c.first_name',
                'c.last_name',
                'c.phone',
                'c.delivery_address'
            )
            ->first();

        if (!$order) {
            return redirect()->route('customer.orders.index')->with('error', 'Order not found.');
        }

        $orderItems = DB::table('order_items as oi')
            ->join('products as p', 'oi.product_id', '=', 'p.id')
            ->join('vendors as v', 'oi.vendor_id', '=', 'v.id')
            ->where('oi.order_id', $order->id)
            ->select(
                'oi.*',
                'p.product_name',
                'p.unit_type',
                'v.business_name'
            )
            ->get();

        // Calculate total amount
        $subtotal = $orderItems->sum(function($item) {
            return $item->unit_price * $item->quantity;
        });
        $marketFee = 0; // Removed 5% market fee
        $totalAmount = $subtotal + $marketFee;

        return view('customer.orders.show', compact('order', 'orderItems', 'subtotal', 'marketFee', 'totalAmount'));
    }

    public function markComplete($orderReference)
    {
        $customer = DB::table('customers')
            ->where('user_id', Auth::id())
            ->first();

        if (!$customer) {
            return redirect()->route('home')->with('error', 'Customer profile not found.');
        }

        $order = DB::table('orders')
            ->where('customer_id', $customer->id)
            ->where('order_reference', $orderReference)
            ->first();

        if (!$order) {
            return redirect()->route('customer.orders.index')->with('error', 'Order not found.');
        }

        // Check if order is in a completable status
        $completableStatuses = ['ready', 'preparing', 'awaiting_rider', 'out_for_delivery', 'delivered'];
        if (!in_array($order->order_status, $completableStatuses)) {
            return redirect()->route('customer.orders.show', $orderReference)
                ->with('error', 'This order cannot be marked as complete at this time.');
        }

        // Update order status to completed
        DB::table('orders')
            ->where('id', $order->id)
            ->update([
                'order_status' => 'completed',
                'updated_at' => now()
            ]);

        // Also update all order items
        DB::table('order_items')
            ->where('order_id', $order->id)
            ->update([
                'item_status' => 'completed',
                'updated_at' => now()
            ]);

        return redirect()->route('customer.orders.show', $orderReference)
            ->with('success', 'Order marked as completed successfully!');
    }
}
