<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PickupManagerController extends Controller
{
    public function index()
    {
        // Get today's pickup statistics
        $today = now()->format('Y-m-d');
        
        $stats = [
            'total_ready_orders' => DB::table('orders')
                ->where('order_status', 'ready')
                ->where('fulfillment_type', 'weekend_pickup')
                ->count(),
            'total_completed_today' => DB::table('orders')
                ->where('order_status', 'completed')
                ->whereDate('updated_at', $today)
                ->where('fulfillment_type', 'weekend_pickup')
                ->count(),
            'total_pending_pickups' => DB::table('pickup_codes as pc')
                ->join('orders as o', 'pc.order_id', '=', 'o.id')
                ->where('is_used', false)
                ->where('o.order_status', '!=', 'cancelled')
                ->where('expires_at', '>', now())
                ->count(),
        ];

        // Get recent pickup activity
        $recentPickups = DB::table('pickup_codes as pc')
            ->join('orders as o', 'pc.order_id', '=', 'o.id')
            ->join('customers as c', 'o.customer_id', '=', 'c.id')
            ->where('pc.is_used', true)
            ->orderBy('pc.used_at', 'desc')
            ->limit(10)
            ->select(
                'pc.code',
                'pc.used_at',
                'o.order_reference',
                'c.first_name',
                'c.last_name'
            )
            ->get();

        // Item-level pickup transactions across all stores for admin visibility.
        $pickupTransactions = DB::table('orders as o')
            ->join('customers as c', 'o.customer_id', '=', 'c.id')
            ->join('order_items as oi', 'o.id', '=', 'oi.order_id')
            ->join('vendors as v', 'oi.vendor_id', '=', 'v.id')
            ->join('products as p', 'oi.product_id', '=', 'p.id')
            ->leftJoin('pickup_codes as pc', 'o.id', '=', 'pc.order_id')
            ->where('o.fulfillment_type', 'weekend_pickup')
            ->where('o.order_status', '!=', 'cancelled')
            ->orderBy('o.order_date', 'desc')
            ->select(
                'o.id as order_id',
                'o.order_reference',
                'o.order_date',
                'o.order_status',
                'o.pickup_code as order_pickup_code',
                'c.first_name',
                'c.last_name',
                'v.business_name',
                'p.product_name',
                'oi.quantity',
                'oi.unit_price',
                'pc.code as generated_pickup_code',
                'pc.is_used',
                'pc.expires_at',
                'pc.used_at'
            )
            ->limit(200)
            ->get();

        return view('admin.pickups.index', compact('stats', 'recentPickups', 'pickupTransactions'));
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
            ->where('o.fulfillment_type', 'weekend_pickup')
            ->where('o.order_status', '!=', 'cancelled')
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

        $pickupCode = DB::table('pickup_codes as pc')
            ->join('orders as o', 'pc.order_id', '=', 'o.id')
            ->where('pc.code', $request->pickup_code)
            ->where('pc.is_used', false)
            ->where('o.fulfillment_type', 'weekend_pickup')
            ->where('o.order_status', '!=', 'cancelled')
            ->select('pc.*')
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

    public function searchOrders(Request $request)
    {
        $search = $request->input('search');
        
        $orders = DB::table('orders as o')
            ->join('customers as c', 'o.customer_id', '=', 'c.id')
            ->leftJoin('pickup_codes as pc', 'o.id', '=', 'pc.order_id')
            ->where('o.fulfillment_type', 'weekend_pickup')
            ->where('o.order_status', '!=', 'cancelled')
            ->where(function($query) use ($search) {
                $query->where('o.order_reference', 'like', "%{$search}%")
                      ->orWhere('c.first_name', 'like', "%{$search}%")
                      ->orWhere('c.last_name', 'like', "%{$search}%")
                      ->orWhere('pc.code', 'like', "%{$search}%");
            })
            ->select(
                'o.*',
                'c.first_name',
                'c.last_name',
                'pc.code as pickup_code',
                'pc.is_used',
                'pc.used_at'
            )
            ->orderBy('o.order_date', 'desc')
            ->limit(20)
            ->get();

        return response()->json([
            'orders' => $orders
        ]);
    }
}
