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

        // Query orders with vendor information through order_items
        $orders = DB::table('orders as o')
            ->join('order_items as oi', 'o.id', '=', 'oi.order_id')
            ->join('vendors as v', 'oi.vendor_id', '=', 'v.id')
            ->whereBetween('o.created_at', [$startDate . ' 00:00:00', $endDate . ' 23:59:59'])
            ->select(
                'o.id',
                'o.order_reference as order_number',
                'v.business_name',
                DB::raw('SUM(oi.quantity * oi.unit_price) as subtotal'),
                DB::raw('SUM(oi.quantity * oi.unit_price) * 0.05 as market_fee'),
                DB::raw('SUM(oi.quantity * oi.unit_price) * 1.05 as total'),
                'o.order_status as status',
                'o.created_at'
            )
            ->groupBy('o.id', 'o.order_reference', 'v.business_name', 'o.order_status', 'o.created_at')
            ->orderBy('o.created_at', 'desc')
            ->get();

        // Calculate totals
        $totalRevenue = $orders->sum('total');
        $totalMarketFees = $orders->sum('market_fee');
        $totalGrossSales = $orders->sum('subtotal');

        return view('admin.reports.sales', compact(
            'orders',
            'startDate',
            'endDate',
            'totalRevenue',
            'totalMarketFees',
            'totalGrossSales',
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
        $attendance = DB::table('vendor_attendance as va')
            ->join('vendors as v', 'va.vendor_id', '=', 'v.id')
            ->whereDate('va.market_date', $marketDate)
            ->select(
                'v.business_name',
                'v.owner_name',
                'va.check_in_time',
                'va.check_out_time'
            )
            ->orderBy('va.check_in_time', 'asc')
            ->get();

        // Get all vendors for comparison
        $allVendors = DB::table('vendors')
            ->whereNull('deleted_at')
            ->select('id', 'business_name', 'owner_name')
            ->orderBy('business_name')
            ->get();

        // Mark which vendors attended
        $attendedVendors = $attendance->pluck('vendor_id')->toArray();
        $absentVendors = $allVendors->whereNotIn('id', $attendedVendors);

        return view('admin.reports.attendance', compact(
            'attendance',
            'marketDate',
            'allVendors',
            'absentVendors',
            'pendingVendorsCount'
        ));
    }

    public function exportSalesPdf(Request $request)
    {
        $startDate = $request->input('start_date', now()->subDays(30)->toDateString());
        $endDate = $request->input('end_date', now()->toDateString());

        $orders = DB::table('orders as o')
            ->join('order_items as oi', 'o.id', '=', 'oi.order_id')
            ->join('vendors as v', 'oi.vendor_id', '=', 'v.id')
            ->whereBetween('o.created_at', [$startDate . ' 00:00:00', $endDate . ' 23:59:59'])
            ->select(
                'o.id',
                'o.order_reference as order_number',
                'v.business_name',
                DB::raw('SUM(oi.quantity * oi.unit_price) as subtotal'),
                DB::raw('SUM(oi.quantity * oi.unit_price) * 0.05 as market_fee'),
                DB::raw('SUM(oi.quantity * oi.unit_price) * 1.05 as total'),
                'o.order_status as status',
                'o.created_at'
            )
            ->groupBy('o.id', 'o.order_reference', 'v.business_name', 'o.order_status', 'o.created_at')
            ->orderBy('o.created_at', 'desc')
            ->get();

        $totalRevenue = $orders->sum('total');
        $totalMarketFees = $orders->sum('market_fee');
        $totalGrossSales = $orders->sum('subtotal');

        $pdf = Pdf::loadView('admin.exports.sales-pdf', compact(
            'orders',
            'startDate',
            'endDate',
            'totalRevenue',
            'totalMarketFees',
            'totalGrossSales'
        ));

        return $pdf->download('admin-sales-report-' . now()->format('Y-m-d') . '.pdf');
    }

    public function exportSalesCsv(Request $request)
    {
        $startDate = $request->input('start_date', now()->subDays(30)->toDateString());
        $endDate = $request->input('end_date', now()->toDateString());

        $orders = DB::table('orders as o')
            ->join('order_items as oi', 'o.id', '=', 'oi.order_id')
            ->join('vendors as v', 'oi.vendor_id', '=', 'v.id')
            ->whereBetween('o.created_at', [$startDate . ' 00:00:00', $endDate . ' 23:59:59'])
            ->select(
                'o.id',
                'o.order_reference as order_number',
                'v.business_name',
                DB::raw('SUM(oi.quantity * oi.unit_price) as subtotal'),
                DB::raw('SUM(oi.quantity * oi.unit_price) * 0.05 as market_fee'),
                DB::raw('SUM(oi.quantity * oi.unit_price) * 1.05 as total'),
                'o.order_status as status',
                'o.created_at'
            )
            ->groupBy('o.id', 'o.order_reference', 'v.business_name', 'o.order_status', 'o.created_at')
            ->orderBy('o.created_at', 'desc')
            ->get();

        $totalRevenue = $orders->sum('total');
        $totalMarketFees = $orders->sum('market_fee');
        $totalGrossSales = $orders->sum('subtotal');

        return response()->streamDownload(function () use ($orders, $totalRevenue, $totalMarketFees, $totalGrossSales) {
            $output = fopen('php://output', 'w');
            fputcsv($output, ['Order Number', 'Vendor Name', 'Subtotal', 'Market Fee (5%)', 'Total', 'Status', 'Date']);

            foreach ($orders as $order) {
                fputcsv($output, [
                    $order->order_number,
                    $order->business_name,
                    number_format($order->subtotal, 2),
                    number_format($order->market_fee, 2),
                    number_format($order->total, 2),
                    $order->status,
                    $order->created_at
                ]);
            }

            fputcsv($output, []);
            fputcsv($output, ['TOTALS', '', number_format($totalGrossSales, 2), number_format($totalMarketFees, 2), number_format($totalRevenue, 2), '', '']);
            fclose($output);
        }, 'admin-sales-report-' . now()->format('Y-m-d') . '.csv');
    }

    public function exportAttendancePdf(Request $request)
    {
        $marketDate = $request->input('market_date', now()->toDateString());

        $attendance = DB::table('vendor_attendance as va')
            ->join('vendors as v', 'va.vendor_id', '=', 'v.id')
            ->whereDate('va.market_date', $marketDate)
            ->select(
                'v.business_name',
                'v.owner_name',
                'va.check_in_time',
                'va.check_out_time'
            )
            ->orderBy('va.check_in_time', 'asc')
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

        $attendance = DB::table('vendor_attendance as va')
            ->join('vendors as v', 'va.vendor_id', '=', 'v.id')
            ->whereDate('va.market_date', $marketDate)
            ->select(
                'v.business_name',
                'v.owner_name',
                'va.check_in_time',
                'va.check_out_time'
            )
            ->orderBy('va.check_in_time', 'asc')
            ->get();

        return response()->streamDownload(function () use ($attendance) {
            $output = fopen('php://output', 'w');
            fputcsv($output, ['Business Name', 'Owner Name', 'Check In', 'Check Out']);

            foreach ($attendance as $record) {
                fputcsv($output, [
                    $record->business_name,
                    $record->owner_name,
                    $record->check_in_time,
                    $record->check_out_time
                ]);
            }
            fclose($output);
        }, 'admin-attendance-report-' . now()->format('Y-m-d') . '.csv');
    }
}
