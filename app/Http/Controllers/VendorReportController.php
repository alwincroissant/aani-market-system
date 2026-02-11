<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use App\Models\Vendor;
use App\Models\Order;
use App\Models\Product;

class VendorReportController extends Controller
{
    public function sales(Request $request)
    {
        $vendor = Vendor::where('user_id', Auth::id())->first();
        
        if (!$vendor) {
            return redirect()->route('auth.login')->with('error', 'Vendor profile not found.');
        }

        // Get date range from request
        $startDate = $request->get('start_date', now()->subDays(30)->toDateString());
        $endDate = $request->get('end_date', now()->toDateString());

        // Get vendor's sales data
        $sales = DB::table('order_items')
            ->join('orders', 'order_items.order_id', '=', 'orders.id')
            ->where('order_items.vendor_id', $vendor->id)
            ->whereBetween('orders.created_at', [$startDate, $endDate])
            ->selectRaw('DATE(orders.created_at) as date, SUM(order_items.quantity * order_items.unit_price) as total')
            ->groupBy('date')
            ->orderBy('date', 'desc')
            ->get();

        // Calculate totals
        $totalSales = $sales->sum('total');
        $totalOrders = $sales->count();

        return view('vendor.reports.sales', compact('sales', 'totalSales', 'totalOrders', 'startDate', 'endDate'));
    }

    public function products(Request $request)
    {
        $vendor = Vendor::where('user_id', Auth::id())->first();
        
        if (!$vendor) {
            return redirect()->route('auth.login')->with('error', 'Vendor profile not found.');
        }

        // Get vendor's products with sales data
        $products = DB::table('products')
            ->leftJoin('order_items', 'products.id', '=', 'order_items.product_id')
            ->where('products.vendor_id', $vendor->id)
            ->selectRaw('products.*, COALESCE(SUM(order_items.quantity), 0) as total_sold')
            ->groupBy('products.id')
            ->orderBy('products.created_at', 'desc')
            ->get();

        return view('vendor.reports.products', compact('products'));
    }

    public function orders(Request $request)
    {
        $vendor = Vendor::where('user_id', Auth::id())->first();
        
        if (!$vendor) {
            return redirect()->route('auth.login')->with('error', 'Vendor profile not found.');
        }

        // Get vendor's orders
        $orders = DB::table('order_items')
            ->join('orders', 'order_items.order_id', '=', 'orders.id')
            ->where('order_items.vendor_id', $vendor->id)
            ->select('orders.*', 'order_items.item_status')
            ->orderBy('orders.created_at', 'desc')
            ->get();

        return view('vendor.reports.orders', compact('orders'));
    }
}
