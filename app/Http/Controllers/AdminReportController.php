<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Barryvdh\DomPDF\Facade\Pdf;
use Symfony\Component\HttpFoundation\StreamedResponse;

class AdminReportController extends Controller
{
    public function sales(Request $request)
    {
        // Get count of pending vendors for notification badge
        $pendingVendorsCount = DB::table('users')
            ->join('vendors', 'users.id', '=', 'vendors.user_id')
            ->leftJoin('stall_assignments', 'vendors.id', '=', 'stall_assignments.vendor_id')
            ->where('users.role', 'vendor')
            ->where('users.is_active', false)
            ->whereNull('stall_assignments.vendor_id')
            ->count();

        $startDate = $request->input('start_date', now()->subDays(30)->toDateString());
        $endDate = $request->input('end_date', now()->toDateString());
        $isFiltered = $request->has('start_date') || $request->has('end_date');

        // Query ONLINE orders with vendor information through order_items
        $ordersQuery = DB::table('orders as o')
            ->join('order_items as oi', 'o.id', '=', 'oi.order_id')
            ->join('vendors as v', 'oi.vendor_id', '=', 'v.id')
            ->whereBetween('o.created_at', [$startDate . ' 00:00:00', $endDate . ' 23:59:59'])
            ->whereIn('o.order_status', ['completed', 'delivered'])
            ->select(
                'o.id',
                'o.order_reference as order_number',
                'v.business_name',
                DB::raw('SUM(oi.quantity * oi.unit_price) as total'),
                'o.order_status as status',
                'o.created_at',
                DB::raw("'online' as sale_type")
            )
            ->groupBy('o.id', 'o.order_reference', 'v.business_name', 'o.order_status', 'o.created_at')
            ->orderBy('o.created_at', 'desc');

        // Only limit to 10 if no filter was applied
        if (!$isFiltered) {
            $ordersQuery->limit(10);
        }

        $orders = $ordersQuery->get();

        // Get PHYSICAL/walk-in sales
        $physicalSalesQuery = DB::table('walk_in_sales as ws')
            ->join('vendors as v', 'ws.vendor_id', '=', 'v.id')
            ->whereBetween('ws.sale_date', [$startDate, $endDate])
            ->select(
                'ws.id',
                DB::raw("CONCAT('WS-', ws.id) as order_number"),
                'v.business_name',
                DB::raw('ws.quantity * ws.unit_price as total'),
                DB::raw("'completed' as status"),
                'ws.sale_date as created_at',
                DB::raw("'physical' as sale_type")
            )
            ->orderBy('ws.sale_date', 'desc');

        if (!$isFiltered) {
            $physicalSalesQuery->limit(5);
        }

        $physicalSales = $physicalSalesQuery->get();

        // Merge and sort all sales
        $allSales = $orders->concat($physicalSales)->sortByDesc('created_at');
        
        if (!$isFiltered) {
            $allSales = $allSales->take(10);
        }

        // Calculate totals from all sales (vendors keep 100% - no transaction fees)
        $totalRevenue = $allSales->sum('total');

        return view('admin.reports.sales', compact(
            'allSales',
            'startDate',
            'endDate',
            'totalRevenue',
            'pendingVendorsCount'
        ));
    }

    public function attendance(Request $request)
    {
        // Get count of pending vendors for notification badge
        $pendingVendorsCount = DB::table('users')
            ->join('vendors', 'users.id', '=', 'vendors.user_id')
            ->leftJoin('stall_assignments', 'vendors.id', '=', 'stall_assignments.vendor_id')
            ->where('users.role', 'vendor')
            ->where('users.is_active', false)
            ->whereNull('stall_assignments.vendor_id')
            ->count();

        $marketDate = $request->input('market_date', now()->toDateString());

        // Query vendor attendance for the specific date
        $attendance = DB::table('vendors as v')
        ->whereNull('v.deleted_at')
        ->select(
            'v.id',
            'v.business_name',
            'v.owner_name',
            'v.is_live'
        )
        ->orderBy('v.business_name', 'asc')
        ->get();

        $presentVendors = $attendance->where('is_live', true);
        $absentVendors  = $attendance->where('is_live', false);

        return view('admin.reports.attendance', compact(
            'attendance',
            'marketDate',
            'presentVendors',
            'absentVendors',
            'pendingVendorsCount'
        ));

    }

    public function exportSalesPdf(Request $request)
    {
        $startDate = $request->input('start_date', now()->subDays(30)->toDateString());
        $endDate = $request->input('end_date', now()->toDateString());

        // Get ONLINE orders
        $orders = DB::table('orders as o')
            ->join('order_items as oi', 'o.id', '=', 'oi.order_id')
            ->join('vendors as v', 'oi.vendor_id', '=', 'v.id')
            ->whereBetween('o.created_at', [$startDate . ' 00:00:00', $endDate . ' 23:59:59'])
            ->whereIn('o.order_status', ['completed', 'delivered'])
            ->select(
                'o.id',
                'o.order_reference as order_number',
                'v.business_name',
                DB::raw('SUM(oi.quantity * oi.unit_price) as total'),
                'o.order_status as status',
                'o.created_at',
                DB::raw("'online' as sale_type")
            )
            ->groupBy('o.id', 'o.order_reference', 'v.business_name', 'o.order_status', 'o.created_at')
            ->orderBy('o.created_at', 'desc')
            ->get();

        // Get PHYSICAL/walk-in sales
        $physicalSales = DB::table('walk_in_sales as ws')
            ->join('vendors as v', 'ws.vendor_id', '=', 'v.id')
            ->whereBetween('ws.sale_date', [$startDate, $endDate])
            ->select(
                'ws.id',
                DB::raw("CONCAT('WS-', ws.id) as order_number"),
                'v.business_name',
                DB::raw('ws.quantity * ws.unit_price as total'),
                DB::raw("'completed' as status"),
                'ws.sale_date as created_at',
                DB::raw("'physical' as sale_type")
            )
            ->orderBy('ws.sale_date', 'desc')
            ->get();

        // Merge both online and physical sales
        $allSales = $orders->concat($physicalSales)->sortByDesc('created_at');
        $totalRevenue = $allSales->sum('total');

        $pdf = Pdf::loadView('admin.exports.sales-pdf', compact(
            'allSales',
            'startDate',
            'endDate',
            'totalRevenue'
        ));

        return $pdf->download('admin-sales-report-' . now()->format('Y-m-d') . '.pdf');
    }

    public function exportSalesCsv(Request $request)
    {
        $startDate = $request->input('start_date', now()->subDays(30)->toDateString());
        $endDate = $request->input('end_date', now()->toDateString());

        // Get ONLINE orders
        $orders = DB::table('orders as o')
            ->join('order_items as oi', 'o.id', '=', 'oi.order_id')
            ->join('vendors as v', 'oi.vendor_id', '=', 'v.id')
            ->whereBetween('o.created_at', [$startDate . ' 00:00:00', $endDate . ' 23:59:59'])
            ->whereIn('o.order_status', ['completed', 'delivered'])
            ->select(
                'o.id',
                'o.order_reference as order_number',
                'v.business_name',
                DB::raw('SUM(oi.quantity * oi.unit_price) as total'),
                'o.order_status as status',
                'o.created_at',
                DB::raw("'online' as sale_type")
            )
            ->groupBy('o.id', 'o.order_reference', 'v.business_name', 'o.order_status', 'o.created_at')
            ->orderBy('o.created_at', 'desc')
            ->get();

        // Get PHYSICAL/walk-in sales
        $physicalSales = DB::table('walk_in_sales as ws')
            ->join('vendors as v', 'ws.vendor_id', '=', 'v.id')
            ->whereBetween('ws.sale_date', [$startDate, $endDate])
            ->select(
                'ws.id',
                DB::raw("CONCAT('WS-', ws.id) as order_number"),
                'v.business_name',
                DB::raw('ws.quantity * ws.unit_price as total'),
                DB::raw("'completed' as status"),
                'ws.sale_date as created_at',
                DB::raw("'physical' as sale_type")
            )
            ->orderBy('ws.sale_date', 'desc')
            ->get();

        // Merge both online and physical sales
        $allSales = $orders->concat($physicalSales)->sortByDesc('created_at');
        $totalRevenue = $allSales->sum('total');

        return response()->streamDownload(function () use ($allSales, $totalRevenue) {
            $output = fopen('php://output', 'w');
            fputcsv($output, ['Order Number', 'Vendor Name', 'Total', 'Type', 'Status', 'Date']);

            foreach ($allSales as $sale) {
                fputcsv($output, [
                    $sale->order_number,
                    $sale->business_name,
                    number_format($sale->total, 2),
                    ucfirst($sale->sale_type),
                    $sale->status,
                    $sale->created_at
                ]);
            }

            fputcsv($output, []);
            fputcsv($output, ['TOTALS', '', number_format($totalRevenue, 2), '', '', '']);
            fclose($output);
        }, 'admin-sales-report-' . now()->format('Y-m-d') . '.csv');
    }

    public function exportAttendancePdf(Request $request)
    {
        $marketDate = $request->input('market_date', now()->toDateString());

        $attendance = DB::table('vendors as v')
            ->whereNull('v.deleted_at')
            ->select(
                'v.business_name',
                'v.owner_name',
                'v.is_live'
            )
            ->orderBy('v.business_name', 'asc')
            ->get();

        $pdf = Pdf::loadView('admin.exports.attendance-pdf', compact(
            'attendance',
            'marketDate'
        ));

        return $pdf->download('admin-attendance-report-' . $marketDate . '.pdf');
    }

    public function exportAttendanceCsv(Request $request)
    {
        $marketDate = $request->input('market_date', now()->toDateString());

        $attendance = DB::table('vendors as v')
            ->whereNull('v.deleted_at')
            ->select(
                'v.business_name',
                'v.owner_name',
                'v.is_live'
            )
            ->orderBy('v.business_name', 'asc')
            ->get();

        return response()->streamDownload(function () use ($attendance) {
            $output = fopen('php://output', 'w');
            fputcsv($output, ['Business Name', 'Owner Name', 'Status']);

            foreach ($attendance as $record) {
                fputcsv($output, [
                    $record->business_name,
                    $record->owner_name,
                    $record->is_live ? 'Present' : 'Absent',
                ]);
            }
            fclose($output);
        }, 'admin-attendance-report-' . now()->format('Y-m-d') . '.csv');
    }
}
