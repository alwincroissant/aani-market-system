<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use App\Models\Vendor;
use Carbon\Carbon;
use App\Models\Order;
use App\Models\Product;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Log;

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

        // Debug: Log vendor info and date range
        Log::info('Sales Report Debug:', [
            'vendor_id' => $vendor->id,
            'vendor_name' => $vendor->business_name,
            'start_date' => $startDate,
            'end_date' => $endDate
        ]);

        // Get vendor's sales data
        $sales = DB::table('order_items')
            ->join('orders', 'order_items.order_id', '=', 'orders.id')
            ->where('order_items.vendor_id', $vendor->id)
            ->whereBetween('orders.created_at', [$startDate, $endDate])
            ->selectRaw('DATE(orders.created_at) as date, SUM(order_items.quantity * order_items.unit_price) as total, COUNT(DISTINCT orders.id) as order_count')
            ->groupBy('date')
            ->orderBy('date', 'desc')
            ->get();

        // Debug: Log query results
        Log::info('Sales Query Results:', [
            'sales_count' => $sales->count(),
            'sales_data' => $sales->toArray()
        ]);

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

        // Convert created_at to Carbon objects for formatting
        $orders = $orders->map(function($order) {
            $order->created_at = new Carbon($order->created_at);
            return $order;
        });

        return view('vendor.reports.orders', compact('orders'));
    }

    public function exportSalesPdf(Request $request)
    {
        $vendor = Vendor::where('user_id', Auth::id())->first();
        
        if (!$vendor) {
            return redirect()->route('auth.login')->with('error', 'Vendor profile not found.');
        }

        $startDate = $request->get('start_date', now()->subDays(30)->toDateString());
        $endDate = $request->get('end_date', now()->toDateString());

        $sales = DB::table('order_items')
            ->join('orders', 'order_items.order_id', '=', 'orders.id')
            ->where('order_items.vendor_id', $vendor->id)
            ->whereBetween('orders.created_at', [$startDate, $endDate])
            ->selectRaw('DATE(orders.created_at) as date, SUM(order_items.quantity * order_items.unit_price) as total, COUNT(DISTINCT orders.id) as order_count')
            ->groupBy('date')
            ->orderBy('date', 'desc')
            ->get();

        $totalSales = $sales->sum('total');
        $totalOrders = $sales->count();

        $pdf = Pdf::loadView('vendor.exports.sales-pdf', compact(
            'sales',
            'totalSales',
            'totalOrders',
            'startDate',
            'endDate',
            'vendor'
        ));

        return $pdf->download('sales-report-' . now()->format('Y-m-d') . '.pdf');
    }

    public function exportSalesCsv(Request $request)
    {
        $vendor = Vendor::where('user_id', Auth::id())->first();
        
        if (!$vendor) {
            return redirect()->route('auth.login')->with('error', 'Vendor profile not found.');
        }

        $startDate = $request->get('start_date', now()->subDays(30)->toDateString());
        $endDate = $request->get('end_date', now()->toDateString());

        $sales = DB::table('order_items')
            ->join('orders', 'order_items.order_id', '=', 'orders.id')
            ->where('order_items.vendor_id', $vendor->id)
            ->whereBetween('orders.created_at', [$startDate, $endDate])
            ->selectRaw('DATE(orders.created_at) as date, SUM(order_items.quantity * order_items.unit_price) as total, COUNT(DISTINCT orders.id) as order_count')
            ->groupBy('date')
            ->orderBy('date', 'desc')
            ->get();

        $totalSales = $sales->sum('total');

        return response()->streamDownload(function () use ($sales, $totalSales) {
            $output = fopen('php://output', 'w');
            fputcsv($output, ['Date', 'Orders', 'Total Sales']);

            foreach ($sales as $record) {
                fputcsv($output, [
                    $record->date,
                    $record->order_count,
                    number_format($record->total, 2)
                ]);
            }

            fputcsv($output, []);
            fputcsv($output, ['TOTAL', '', number_format($totalSales, 2)]);
            fclose($output);
        }, 'sales-report-' . now()->format('Y-m-d') . '.csv');
    }

    public function exportProductsPdf(Request $request)
    {
        $vendor = Vendor::where('user_id', Auth::id())->first();
        
        if (!$vendor) {
            return redirect()->route('auth.login')->with('error', 'Vendor profile not found.');
        }

        $products = DB::table('products')
            ->leftJoin('order_items', 'products.id', '=', 'order_items.product_id')
            ->where('products.vendor_id', $vendor->id)
            ->selectRaw('products.*, COALESCE(SUM(order_items.quantity), 0) as total_sold')
            ->groupBy('products.id')
            ->orderBy('products.created_at', 'desc')
            ->get();

        $pdf = Pdf::loadView('vendor.exports.products-pdf', compact(
            'products',
            'vendor'
        ));

        return $pdf->download('products-report-' . now()->format('Y-m-d') . '.pdf');
    }

    public function exportProductsCsv(Request $request)
    {
        $vendor = Vendor::where('user_id', Auth::id())->first();
        
        if (!$vendor) {
            return redirect()->route('auth.login')->with('error', 'Vendor profile not found.');
        }

        $products = DB::table('products')
            ->leftJoin('order_items', 'products.id', '=', 'order_items.product_id')
            ->where('products.vendor_id', $vendor->id)
            ->selectRaw('products.*, COALESCE(SUM(order_items.quantity), 0) as total_sold')
            ->groupBy('products.id')
            ->orderBy('products.created_at', 'desc')
            ->get();

        return response()->streamDownload(function () use ($products) {
            $output = fopen('php://output', 'w');
            fputcsv($output, ['Product Name', 'SKU', 'Price', 'Stock', 'Total Sold']);

            foreach ($products as $product) {
                fputcsv($output, [
                    $product->name,
                    $product->sku ?? 'N/A',
                    number_format($product->price, 2),
                    $product->quantity,
                    $product->total_sold
                ]);
            }
            fclose($output);
        }, 'products-report-' . now()->format('Y-m-d') . '.csv');
    }

    public function exportOrdersPdf(Request $request)
    {
        $vendor = Vendor::where('user_id', Auth::id())->first();
        
        if (!$vendor) {
            return redirect()->route('auth.login')->with('error', 'Vendor profile not found.');
        }

        $orders = DB::table('order_items')
            ->join('orders', 'order_items.order_id', '=', 'orders.id')
            ->where('order_items.vendor_id', $vendor->id)
            ->select('orders.*', 'order_items.item_status')
            ->orderBy('orders.created_at', 'desc')
            ->get();

        $pdf = Pdf::loadView('vendor.exports.orders-pdf', compact(
            'orders',
            'vendor'
        ));

        return $pdf->download('orders-report-' . now()->format('Y-m-d') . '.pdf');
    }

    public function exportOrdersCsv(Request $request)
    {
        $vendor = Vendor::where('user_id', Auth::id())->first();
        
        if (!$vendor) {
            return redirect()->route('auth.login')->with('error', 'Vendor profile not found.');
        }

        $orders = DB::table('order_items')
            ->join('orders', 'order_items.order_id', '=', 'orders.id')
            ->where('order_items.vendor_id', $vendor->id)
            ->select('orders.*', 'order_items.item_status')
            ->orderBy('orders.created_at', 'desc')
            ->get();

        return response()->streamDownload(function () use ($orders) {
            $output = fopen('php://output', 'w');
            fputcsv($output, ['Order Number', 'Order Status', 'Item Status', 'Date', 'Total Amount']);

            foreach ($orders as $order) {
                fputcsv($output, [
                    $order->order_reference,
                    $order->order_status,
                    $order->item_status,
                    $order->created_at,
                    number_format($order->total_amount ?? 0, 2)
                ]);
            }
            fclose($output);
        }, 'orders-report-' . now()->format('Y-m-d') . '.csv');
    }
}
