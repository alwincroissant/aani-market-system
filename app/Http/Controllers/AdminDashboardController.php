<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AdminDashboardController extends Controller
{
    public function index()
    {
        // Get dashboard statistics
        $stats = [
            'total_users' => DB::table('users')->count(),
            'total_vendors' => DB::table('vendors')->count(),
            'total_products' => DB::table('products')->count(),
            'total_orders' => DB::table('orders')->count(),
            'total_stalls' => DB::table('stalls')->count(),
            'occupied_stalls' => DB::table('stalls')->where('status', 'occupied')->count(),
        ];

        // Get count of pending vendors for notification badge
        $pendingVendorsCount = DB::table('users')
            ->join('vendors', 'users.id', '=', 'vendors.user_id')
            ->leftJoin('stall_assignments', 'vendors.id', '=', 'stall_assignments.vendor_id')
            ->where('users.role', 'vendor')
            ->where('users.is_active', false)
            ->whereNull('stall_assignments.vendor_id')
            ->count();

        // Get recent orders (through order_items to get vendor info)
        $recentOrders = DB::table('orders as o')
            ->join('order_items as oi', 'o.id', '=', 'oi.order_id')
            ->join('vendors as v', 'oi.vendor_id', '=', 'v.id')
            ->select(
                'o.order_reference as order_number',
                'v.business_name', 
                DB::raw('SUM(oi.quantity * oi.unit_price) as total'),
                'o.order_status as status',
                'o.created_at'
            )
            ->groupBy('o.id', 'o.order_reference', 'v.business_name', 'o.order_status', 'o.created_at')
            ->orderBy('o.created_at', 'desc')
            ->limit(5)
            ->get();

        // Get top vendors by sales (through order_items)
        $topVendors = DB::table('order_items as oi')
            ->join('vendors as v', 'oi.vendor_id', '=', 'v.id')
            ->select(
                'v.business_name', 
                DB::raw('COUNT(DISTINCT oi.order_id) as total_orders'), 
                DB::raw('SUM(oi.quantity * oi.unit_price) as total_sales')
            )
            ->groupBy('v.id', 'v.business_name')
            ->orderBy('total_sales', 'desc')
            ->limit(5)
            ->get();

        // Get top products across the entire market
        $topProducts = DB::table('order_items as oi')
            ->join('products as p', 'oi.product_id', '=', 'p.id')
            ->join('vendors as v', 'oi.vendor_id', '=', 'v.id')
            ->selectRaw('p.product_name, v.business_name as vendor_name, SUM(oi.quantity) as total_sold, SUM(oi.quantity * oi.unit_price) as total_revenue')
            ->groupBy('p.id', 'p.product_name', 'v.business_name')
            ->orderBy('total_sold', 'desc')
            ->limit(10)
            ->get();

        return view('admin.dashboard.index', compact('stats', 'recentOrders', 'topVendors', 'topProducts', 'pendingVendorsCount'));
    }
}
