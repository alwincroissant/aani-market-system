<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class AdminOrderController extends Controller
{
    public function index(Request $request)
{
        $query = DB::table('orders as o')
            ->join('customers as c', 'o.customer_id', '=', 'c.id')
            ->join('users as u', 'c.user_id', '=', 'u.id')
            ->leftJoin('order_items as oi', 'o.id', '=', 'oi.order_id')
            ->orderBy('o.order_date', 'desc')
            ->select(
                'o.id',
                'o.order_reference',
                'o.customer_id',
                'o.order_date',
                'o.fulfillment_type',
                'o.order_status',
                'o.delivery_address',
                'o.pickup_code',
                'o.notes',
                'o.created_at',
                'o.updated_at',
                'c.first_name',
                'c.last_name',
                'u.email',
                DB::raw('COUNT(DISTINCT oi.id) as item_count'),
                DB::raw('SUM(oi.quantity * oi.unit_price) as total_amount')
            )
            ->groupBy(
                'o.id',
                'o.order_reference',
                'o.customer_id',
                'o.order_date',
                'o.fulfillment_type',
                'o.order_status',
                'o.delivery_address',
                'o.pickup_code',
                'o.notes',
                'o.created_at',
                'o.updated_at',
                'c.first_name',
                'c.last_name',
                'u.email'
            );

        // Apply filters
        if ($request->filled('status')) {
            $query->where('o.order_status', $request->input('status'));
        }

        if ($request->filled('fulfillment_type')) {
            $query->where('o.fulfillment_type', $request->input('fulfillment_type'));
        }

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function($q) use ($search) {
                $q->where('o.order_reference', 'like', "%{$search}%")
                  ->orWhere('c.first_name', 'like', "%{$search}%")
                  ->orWhere('c.last_name', 'like', "%{$search}%")
                  ->orWhere('u.email', 'like', "%{$search}%");
            });
        }

        if ($request->filled('date_from')) {
            $query->whereDate('o.order_date', '>=', $request->input('date_from'));
        }

        if ($request->filled('date_to')) {
            $query->whereDate('o.order_date', '<=', $request->input('date_to'));
        }

        if ($request->filled('has_pickup_code')) {
            if ($request->input('has_pickup_code') === 'yes') {
                $query->whereNotNull('o.pickup_code');
            } elseif ($request->input('has_pickup_code') === 'no') {
                $query->whereNull('o.pickup_code');
            }
        }

        $orders = $query->get();

        // Get filter options for dropdowns
        $statuses = ['pending', 'confirmed', 'ready', 'completed', 'cancelled'];
        $fulfillmentTypes = ['weekend_pickup', 'weekday_delivery', 'weekend_delivery'];

        return view('admin.orders.index', compact('orders', 'statuses', 'fulfillmentTypes'));
    }

    public function show($id)
    {
        $order = DB::table('orders as o')
            ->join('customers as c', 'o.customer_id', '=', 'c.id')
            ->join('users as u', 'c.user_id', '=', 'u.id')
            ->where('o.id', $id)
            ->select(
                'o.*',
                'c.first_name',
                'c.last_name',
                'c.phone',
                'u.email'
            )
            ->first();

        if (!$order) {
            return redirect()->route('admin.orders.index')->with('error', 'Order not found.');
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

        return view('admin.orders.show', compact('order', 'orderItems'));
    }

    public function updateStatus(Request $request, $id)
    {
        $request->validate([
            'order_status' => 'required|in:pending,confirmed,ready,completed,cancelled'
        ]);

        $order = DB::table('orders')->where('id', $id)->first();
        if (!$order) {
            return redirect()->back()->with('error', 'Order not found.');
        }

        // Generate pickup code when order is marked as ready (for pickup orders)
        $pickupCode = null;
        if ($request->order_status === 'ready' && strpos($order->fulfillment_type, 'pickup') !== false) {
            // Check if pickup code already exists
            $existingCode = DB::table('pickup_codes')
                ->where('order_id', $order->id)
                ->first();

            if (!$existingCode) {
                $pickupCode = strtoupper(substr(md5(uniqid()), 0, 6));
                
                // Store pickup code in separate table
                DB::table('pickup_codes')->insert([
                    'order_id' => $order->id,
                    'code' => $pickupCode,
                    'expires_at' => now()->addDays(7),
                    'created_at' => now(),
                    'updated_at' => now()
                ]);
                
                // Update order with pickup code
                DB::table('orders')
                    ->where('id', $order->id)
                    ->update(['pickup_code' => $pickupCode]);
            }
        }

        DB::table('orders')
            ->where('id', $id)
            ->update([
                'order_status' => $request->order_status,
                'updated_at' => now()
            ]);

        return redirect()->back()->with('success', 'Order status updated successfully.');
    }

    public function verifyPickupCode(Request $request)
    {
        $request->validate([
            'pickup_code' => 'required|string|max:8'
        ]);

        $pickupCode = DB::table('pickup_codes as pc')
            ->join('orders as o', 'pc.order_id', '=', 'o.id')
            ->join('customers as c', 'o.customer_id', '=', 'c.id')
            ->where('pc.code', $request->pickup_code)
            ->where('pc.is_used', false)
            ->where('pc.expires_at', '>', now())
            ->select(
                'pc.*',
                'o.order_reference',
                'o.fulfillment_type',
                'o.order_status',
                'c.first_name',
                'c.last_name'
            )
            ->first();

        if (!$pickupCode) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid or expired pickup code.'
            ]);
        }

        if ($pickupCode->order_status !== 'ready') {
            return response()->json([
                'success' => false,
                'message' => 'Order is not ready for pickup yet.'
            ]);
        }

        return response()->json([
            'success' => true,
            'order' => $pickupCode
        ]);
    }

    public function markPickupCodeUsed(Request $request)
    {
        $request->validate([
            'pickup_code' => 'required|string|max:8'
        ]);

        $pickupCode = DB::table('pickup_codes')
            ->where('code', $request->pickup_code)
            ->where('is_used', false)
            ->first();

        if (!$pickupCode) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid pickup code.'
            ]);
        }

        // Mark pickup code as used
        DB::table('pickup_codes')
            ->where('id', $pickupCode->id)
            ->update([
                'is_used' => true,
                'used_at' => now(),
                'updated_at' => now()
            ]);

        // Update order status to completed
        DB::table('orders')
            ->where('id', $pickupCode->order_id)
            ->update([
                'order_status' => 'completed',
                'updated_at' => now()
            ]);

        return response()->json([
            'success' => true,
            'message' => 'Pickup verified successfully. Order marked as completed.'
        ]);
    }
}
