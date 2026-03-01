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

        // Get top vendors by sales (including both online and physical sales)
        $onlineVendorSales = DB::table('order_items as oi')
            ->join('vendors as v', 'oi.vendor_id', '=', 'v.id')
            ->select(
                'v.id',
                'v.business_name', 
                DB::raw('COUNT(DISTINCT oi.order_id) as total_orders'), 
                DB::raw('SUM(oi.quantity * oi.unit_price) as total_sales')
            )
            ->groupBy('v.id', 'v.business_name')
            ->get();

        $physicalVendorSales = DB::table('walk_in_sales as ws')
            ->join('vendors as v', 'ws.vendor_id', '=', 'v.id')
            ->select(
                'v.id',
                'v.business_name',
                DB::raw('0 as total_orders'),
                DB::raw('SUM(ws.quantity * ws.unit_price) as total_sales')
            )
            ->groupBy('v.id', 'v.business_name')
            ->get();

        // Merge vendor sales data
        $vendorSalesMap = collect();
        foreach ($onlineVendorSales as $vendor) {
            $vendorSalesMap->put($vendor->id, [
                'business_name' => $vendor->business_name,
                'total_orders' => $vendor->total_orders,
                'total_sales' => $vendor->total_sales
            ]);
        }
        foreach ($physicalVendorSales as $vendor) {
            if ($vendorSalesMap->has($vendor->id)) {
                $existing = $vendorSalesMap->get($vendor->id);
                $vendorSalesMap->put($vendor->id, [
                    'business_name' => $vendor->business_name,
                    'total_orders' => $existing['total_orders'],
                    'total_sales' => $existing['total_sales'] + $vendor->total_sales
                ]);
            } else {
                $vendorSalesMap->put($vendor->id, [
                    'business_name' => $vendor->business_name,
                    'total_orders' => 0,
                    'total_sales' => $vendor->total_sales
                ]);
            }
        }
        
        $topVendors = $vendorSalesMap->sortByDesc('total_sales')->take(5)->map(function($item) {
            return (object) $item;
        })->values();

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
