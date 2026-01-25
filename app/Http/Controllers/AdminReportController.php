<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AdminReportController extends Controller
{
    public function sales(Request $request)
    {
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
            'totalGrossSales'
        ));
    }

    public function attendance(Request $request)
    {
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
            'absentVendors'
        ));
    }
}
