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

        // Get vendor's ONLINE sales data
        $onlineSales = DB::table('order_items')
            ->join('orders', 'order_items.order_id', '=', 'orders.id')
            ->where('order_items.vendor_id', $vendor->id)
            ->whereIn('orders.order_status', ['completed', 'delivered'])
            ->whereBetween('orders.created_at', [$startDate, $endDate])
            ->selectRaw('DATE(orders.created_at) as date, SUM(order_items.quantity * order_items.unit_price) as total, COUNT(DISTINCT orders.id) as order_count, 0 as physical_count')
            ->groupBy('date')
            ->get();

        // Get vendor's PHYSICAL/walk-in sales data
        $physicalSales = DB::table('walk_in_sales')
            ->where('vendor_id', $vendor->id)
            ->whereBetween('sale_date', [$startDate, $endDate])
            ->selectRaw('DATE(sale_date) as date, SUM(quantity * unit_price) as total, 0 as order_count, COUNT(*) as physical_count')
            ->groupBy('date')
            ->get();

        // Merge online and physical sales by date
        $salesByDate = collect();
        
        // Add online sales
        foreach ($onlineSales as $sale) {
            $salesByDate->put($sale->date, [
                'date' => $sale->date,
                'total' => $sale->total,
                'order_count' => $sale->order_count,
                'physical_count' => 0
            ]);
        }
        
        // Add or merge physical sales
        foreach ($physicalSales as $sale) {
            if ($salesByDate->has($sale->date)) {
                $existing = $salesByDate->get($sale->date);
                $salesByDate->put($sale->date, [
                    'date' => $sale->date,
                    'total' => $existing['total'] + $sale->total,
                    'order_count' => $existing['order_count'],
                    'physical_count' => $sale->physical_count
                ]);
            } else {
                $salesByDate->put($sale->date, [
                    'date' => $sale->date,
                    'total' => $sale->total,
                    'order_count' => 0,
                    'physical_count' => $sale->physical_count
                ]);
            }
        }
        
        // Convert to collection of objects and sort by date descending
        $sales = $salesByDate->sortKeysDesc()->map(function($item) {
            return (object) $item;
        })->values();

        // Debug: Log query results
        Log::info('Sales Query Results:', [
            'sales_count' => $sales->count(),
            'online_sales_days' => $onlineSales->count(),
            'physical_sales_days' => $physicalSales->count()
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
        $products = DB::table('products as p')
            ->leftJoin('product_categories as pc', 'p.category_id', '=', 'pc.id')
            ->leftJoin('order_items as oi', 'p.id', '=', 'oi.product_id')
            ->leftJoin('orders as o', 'oi.order_id', '=', 'o.id')
            ->where('p.vendor_id', $vendor->id)
            ->select(
                'p.id', 'p.vendor_id', 'p.category_id', 'p.product_name', 'p.description', 
                'p.price_per_unit', 'p.unit_type', 'p.product_image_url', 'p.is_available', 
                'p.stock_quantity', 'p.minimum_stock', 'p.track_stock', 'p.allow_backorder', 
                'p.stock_notes', 'p.created_at', 'p.updated_at', 'p.deleted_at',
                'pc.category_name',
                DB::raw('COALESCE(SUM(CASE WHEN o.order_status IN (\'completed\', \'delivered\') THEN oi.quantity ELSE 0 END), 0) as total_sold')
            )
            ->groupBy(
                'p.id', 'p.vendor_id', 'p.category_id', 'p.product_name', 'p.description',
                'p.price_per_unit', 'p.unit_type', 'p.product_image_url', 'p.is_available',
                'p.stock_quantity', 'p.minimum_stock', 'p.track_stock', 'p.allow_backorder',
                'p.stock_notes', 'p.created_at', 'p.updated_at', 'p.deleted_at',
                'pc.category_name'
            )
            ->orderBy('p.created_at', 'desc')
            ->get();

        return view('vendor.reports.products', compact('products'));
    }

    public function orders(Request $request)
    {
        $vendor = Vendor::where('user_id', Auth::id())->first();
        
        if (!$vendor) {
            return redirect()->route('auth.login')->with('error', 'Vendor profile not found.');
        }

        $startDate = $request->get('start_date');
        $endDate = $request->get('end_date');
        $status = trim((string) $request->get('status', ''));

        // Get vendor's orders with vendor-specific totals.
        $ordersQuery = DB::table('order_items')
            ->join('orders', 'order_items.order_id', '=', 'orders.id')
            ->where('order_items.vendor_id', $vendor->id)
            ->select(
                'orders.id',
                'orders.order_reference',
                'orders.order_status',
                'orders.fulfillment_type',
                'orders.created_at',
                DB::raw('COUNT(order_items.id) as item_count'),
                DB::raw('SUM(order_items.quantity * order_items.unit_price) as vendor_total')
            )
            ->groupBy(
                'orders.id',
                'orders.order_reference',
                'orders.order_status',
                'orders.fulfillment_type',
                'orders.created_at'
            )
            ->orderBy('orders.created_at', 'desc');

        if ($startDate) {
            $ordersQuery->whereDate('orders.created_at', '>=', $startDate);
        }
        if ($endDate) {
            $ordersQuery->whereDate('orders.created_at', '<=', $endDate);
        }

        if ($status !== '') {
            $ordersQuery->where('orders.order_status', $status);
        }

        $orders = $ordersQuery->get();

        // Convert created_at to Carbon objects for formatting
        $orders = $orders->map(function($order) {
            $order->created_at = new Carbon($order->created_at);
            return $order;
        });

        return view('vendor.reports.orders', compact('orders', 'status'));
    }

    public function exportSalesPdf(Request $request)
    {
        $vendor = Vendor::where('user_id', Auth::id())->first();
        
        if (!$vendor) {
            return redirect()->route('auth.login')->with('error', 'Vendor profile not found.');
        }

        $startDate = $request->get('start_date', now()->subDays(30)->toDateString());
        $endDate = $request->get('end_date', now()->toDateString());

        // Get vendor's ONLINE sales data
        $onlineSales = DB::table('order_items')
            ->join('orders', 'order_items.order_id', '=', 'orders.id')
            ->where('order_items.vendor_id', $vendor->id)
            ->whereIn('orders.order_status', ['completed', 'delivered'])
            ->whereBetween('orders.created_at', [$startDate, $endDate])
            ->selectRaw('DATE(orders.created_at) as date, SUM(order_items.quantity * order_items.unit_price) as total, COUNT(DISTINCT orders.id) as order_count, 0 as physical_count')
            ->groupBy('date')
            ->get();

        // Get vendor's PHYSICAL/walk-in sales data
        $physicalSales = DB::table('walk_in_sales')
            ->where('vendor_id', $vendor->id)
            ->whereBetween('sale_date', [$startDate, $endDate])
            ->selectRaw('DATE(sale_date) as date, SUM(quantity * unit_price) as total, 0 as order_count, COUNT(*) as physical_count')
            ->groupBy('date')
            ->get();

        // Merge online and physical sales by date
        $salesByDate = collect();
        foreach ($onlineSales as $sale) {
            $salesByDate->put($sale->date, [
                'date' => $sale->date,
                'total' => $sale->total,
                'order_count' => $sale->order_count,
                'physical_count' => 0
            ]);
        }
        foreach ($physicalSales as $sale) {
            if ($salesByDate->has($sale->date)) {
                $existing = $salesByDate->get($sale->date);
                $salesByDate->put($sale->date, [
                    'date' => $sale->date,
                    'total' => $existing['total'] + $sale->total,
                    'order_count' => $existing['order_count'],
                    'physical_count' => $sale->physical_count
                ]);
            } else {
                $salesByDate->put($sale->date, [
                    'date' => $sale->date,
                    'total' => $sale->total,
                    'order_count' => 0,
                    'physical_count' => $sale->physical_count
                ]);
            }
        }
        $sales = $salesByDate->sortKeysDesc()->map(function($item) {
            return (object) $item;
        })->values();

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

        // Get vendor's ONLINE sales data
        $onlineSales = DB::table('order_items')
            ->join('orders', 'order_items.order_id', '=', 'orders.id')
            ->where('order_items.vendor_id', $vendor->id)
            ->whereIn('orders.order_status', ['completed', 'delivered'])
            ->whereBetween('orders.created_at', [$startDate, $endDate])
            ->selectRaw('DATE(orders.created_at) as date, SUM(order_items.quantity * order_items.unit_price) as total, COUNT(DISTINCT orders.id) as order_count, 0 as physical_count')
            ->groupBy('date')
            ->get();

        // Get vendor's PHYSICAL/walk-in sales data
        $physicalSales = DB::table('walk_in_sales')
            ->where('vendor_id', $vendor->id)
            ->whereBetween('sale_date', [$startDate, $endDate])
            ->selectRaw('DATE(sale_date) as date, SUM(quantity * unit_price) as total, 0 as order_count, COUNT(*) as physical_count')
            ->groupBy('date')
            ->get();

        // Merge online and physical sales by date
        $salesByDate = collect();
        foreach ($onlineSales as $sale) {
            $salesByDate->put($sale->date, [
                'date' => $sale->date,
                'total' => $sale->total,
                'order_count' => $sale->order_count,
                'physical_count' => 0
            ]);
        }
        foreach ($physicalSales as $sale) {
            if ($salesByDate->has($sale->date)) {
                $existing = $salesByDate->get($sale->date);
                $salesByDate->put($sale->date, [
                    'date' => $sale->date,
                    'total' => $existing['total'] + $sale->total,
                    'order_count' => $existing['order_count'],
                    'physical_count' => $sale->physical_count
                ]);
            } else {
                $salesByDate->put($sale->date, [
                    'date' => $sale->date,
                    'total' => $sale->total,
                    'order_count' => 0,
                    'physical_count' => $sale->physical_count
                ]);
            }
        }
        $sales = $salesByDate->sortKeysDesc()->map(function($item) {
            return (object) $item;
        })->values();

        $totalSales = $sales->sum('total');

        return response()->streamDownload(function () use ($sales, $totalSales) {
            $output = fopen('php://output', 'w');
            fputcsv($output, ['Date', 'Online Orders', 'Physical Sales', 'Total Sales']);

            foreach ($sales as $record) {
                fputcsv($output, [
                    $record->date,
                    $record->order_count,
                    $record->physical_count,
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

        $products = DB::table('products as p')
            ->leftJoin('product_categories as pc', 'p.category_id', '=', 'pc.id')
            ->leftJoin('order_items as oi', 'p.id', '=', 'oi.product_id')
            ->leftJoin('orders as o', 'oi.order_id', '=', 'o.id')
            ->where('p.vendor_id', $vendor->id)
            ->select(
                'p.id', 'p.vendor_id', 'p.category_id', 'p.product_name', 'p.description', 
                'p.price_per_unit', 'p.unit_type', 'p.product_image_url', 'p.is_available', 
                'p.stock_quantity', 'p.minimum_stock', 'p.track_stock', 'p.allow_backorder', 
                'p.stock_notes', 'p.created_at', 'p.updated_at', 'p.deleted_at',
                'pc.category_name',
                DB::raw('COALESCE(SUM(CASE WHEN o.order_status IN (\'completed\', \'delivered\') THEN oi.quantity ELSE 0 END), 0) as total_sold')
            )
            ->groupBy(
                'p.id', 'p.vendor_id', 'p.category_id', 'p.product_name', 'p.description',
                'p.price_per_unit', 'p.unit_type', 'p.product_image_url', 'p.is_available',
                'p.stock_quantity', 'p.minimum_stock', 'p.track_stock', 'p.allow_backorder',
                'p.stock_notes', 'p.created_at', 'p.updated_at', 'p.deleted_at',
                'pc.category_name'
            )
            ->orderBy('p.created_at', 'desc')
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

        $products = DB::table('products as p')
            ->leftJoin('product_categories as pc', 'p.category_id', '=', 'pc.id')
            ->leftJoin('order_items as oi', 'p.id', '=', 'oi.product_id')
            ->leftJoin('orders as o', 'oi.order_id', '=', 'o.id')
            ->where('p.vendor_id', $vendor->id)
            ->select(
                'p.id', 'p.vendor_id', 'p.category_id', 'p.product_name', 'p.description', 
                'p.price_per_unit', 'p.unit_type', 'p.product_image_url', 'p.is_available', 
                'p.stock_quantity', 'p.minimum_stock', 'p.track_stock', 'p.allow_backorder', 
                'p.stock_notes', 'p.created_at', 'p.updated_at', 'p.deleted_at',
                'pc.category_name',
                DB::raw('COALESCE(SUM(CASE WHEN o.order_status IN (\'completed\', \'delivered\') THEN oi.quantity ELSE 0 END), 0) as total_sold')
            )
            ->groupBy(
                'p.id', 'p.vendor_id', 'p.category_id', 'p.product_name', 'p.description',
                'p.price_per_unit', 'p.unit_type', 'p.product_image_url', 'p.is_available',
                'p.stock_quantity', 'p.minimum_stock', 'p.track_stock', 'p.allow_backorder',
                'p.stock_notes', 'p.created_at', 'p.updated_at', 'p.deleted_at',
                'pc.category_name'
            )
            ->orderBy('p.created_at', 'desc')
            ->get();

        return response()->streamDownload(function () use ($products) {
            $output = fopen('php://output', 'w');
            fputcsv($output, ['Product Name', 'Category', 'Price', 'Stock', 'Total Sold', 'Revenue']);

            foreach ($products as $product) {
                fputcsv($output, [
                    $product->product_name ?? '[unnamed]',
                    $product->category_name ?? 'General',
                    number_format($product->price_per_unit ?? 0, 2),
                    $product->stock_quantity ?? 0,
                    $product->total_sold ?? 0,
                    number_format(($product->total_sold ?? 0) * ($product->price_per_unit ?? 0), 2)
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

        $startDate = $request->get('start_date');
        $endDate = $request->get('end_date');
        $status = trim((string) $request->get('status', ''));

        $orders = DB::table('order_items')
            ->join('orders', 'order_items.order_id', '=', 'orders.id')
            ->where('order_items.vendor_id', $vendor->id)
            ->select(
                'orders.id',
                'orders.order_reference',
                'orders.order_status',
                'orders.fulfillment_type',
                'orders.created_at',
                DB::raw('COUNT(order_items.id) as item_count'),
                DB::raw('SUM(order_items.quantity * order_items.unit_price) as vendor_total')
            )
            ->groupBy(
                'orders.id',
                'orders.order_reference',
                'orders.order_status',
                'orders.fulfillment_type',
                'orders.created_at'
            )
            ->orderBy('orders.created_at', 'desc');

        if ($startDate) {
            $orders->whereDate('orders.created_at', '>=', $startDate);
        }
        if ($endDate) {
            $orders->whereDate('orders.created_at', '<=', $endDate);
        }
        if ($status !== '') {
            $orders->where('orders.order_status', $status);
        }

        $orders = $orders->get();

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

        $startDate = $request->get('start_date');
        $endDate = $request->get('end_date');
        $status = trim((string) $request->get('status', ''));

        $orders = DB::table('order_items')
            ->join('orders', 'order_items.order_id', '=', 'orders.id')
            ->where('order_items.vendor_id', $vendor->id)
            ->select(
                'orders.id',
                'orders.order_reference',
                'orders.order_status',
                'orders.fulfillment_type',
                'orders.created_at',
                DB::raw('COUNT(order_items.id) as item_count'),
                DB::raw('SUM(order_items.quantity * order_items.unit_price) as vendor_total')
            )
            ->groupBy(
                'orders.id',
                'orders.order_reference',
                'orders.order_status',
                'orders.fulfillment_type',
                'orders.created_at'
            )
            ->orderBy('orders.created_at', 'desc');

        if ($startDate) {
            $orders->whereDate('orders.created_at', '>=', $startDate);
        }
        if ($endDate) {
            $orders->whereDate('orders.created_at', '<=', $endDate);
        }
        if ($status !== '') {
            $orders->where('orders.order_status', $status);
        }

        $orders = $orders->get();

        return response()->streamDownload(function () use ($orders) {
            $output = fopen('php://output', 'w');
            fputcsv($output, ['Order Number', 'Order Status', 'Fulfillment', 'Date', 'Items', 'Vendor Total']);

            foreach ($orders as $order) {
                fputcsv($output, [
                    $order->order_reference,
                    $order->order_status,
                    $order->fulfillment_type,
                    $order->created_at,
                    $order->item_count,
                    number_format($order->vendor_total ?? 0, 2)
                ]);
            }
            fclose($output);
        }, 'orders-report-' . now()->format('Y-m-d') . '.csv');
    }
}
