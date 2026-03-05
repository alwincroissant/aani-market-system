<?php

namespace App\Services;

use App\Models\Vendor;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

/**
 * Vendor Summary Service
 * 
 * Replaces the deprecated vendor_summary table with computed queries.
 * All metrics are calculated dynamically from order_items and walk_in_sales.
 * Note: Vendors pay NO fees on sales. Their only expenses are stall rental fees (see stall_payments table).
 */
class VendorSummaryService
{
    /**
     * Get vendor summary for a specific date.
     * 
     * @param Vendor|int $vendor
     * @param Carbon|string $date
     * @return array
     */
    public static function getSummaryForDate($vendor, $date)
    {
        $vendorId = $vendor instanceof Vendor ? $vendor->id : $vendor;
        $date = Carbon::parse($date)->toDateString();

        $grossSales = self::calculateGrossSales($vendorId, $date);

        return [
            'vendor_id' => $vendorId,
            'summary_date' => $date,
            'total_pre_orders' => self::countPreOrders($vendorId, $date),
            'total_walk_in_sales' => self::countWalkInSales($vendorId, $date),
            'gross_sales' => $grossSales,
            'net_sales' => $grossSales, // No transaction fees; vendors keep 100% of sales revenue
        ];
    }

    /**
     * Count confirmed/completed pre-orders for a vendor on a specific date.
     * 
     * @param int $vendorId
     * @param string $date (YYYY-MM-DD)
     * @return int
     */
    public static function countPreOrders($vendorId, $date)
    {
        return DB::table('order_items')
            ->join('orders', 'order_items.order_id', '=', 'orders.id')
            ->where('order_items.vendor_id', $vendorId)
            ->whereDate('orders.order_date', $date)
            ->whereIn('order_items.item_status', ['confirmed', 'ready', 'completed', 'out_for_delivery', 'delivered'])
            ->count();
    }

    /**
     * Count walk-in sales for a vendor on a specific date.
     * 
     * @param int $vendorId
     * @param string $date (YYYY-MM-DD)
     * @return int
     */
    public static function countWalkInSales($vendorId, $date)
    {
        return DB::table('walk_in_sales')
            ->where('vendor_id', $vendorId)
            ->whereDate('sale_date', $date)
            ->count();
    }

    /**
     * Calculate gross sales (sum of all revenue before fees).
     * Includes: (order_items.qty * price) + (walk_in_sales.qty * price)
     * 
     * @param int $vendorId
     * @param string $date (YYYY-MM-DD)
     * @return float
     */
    public static function calculateGrossSales($vendorId, $date)
    {
        // Pre-order revenue
        $preOrderRevenue = DB::table('order_items')
            ->join('orders', 'order_items.order_id', '=', 'orders.id')
            ->where('order_items.vendor_id', $vendorId)
            ->whereDate('orders.order_date', $date)
            ->whereIn('order_items.item_status', ['confirmed', 'ready', 'completed', 'out_for_delivery', 'delivered'])
            ->selectRaw('SUM(order_items.quantity * order_items.unit_price) as total')
            ->value('total') ?? 0;

        // Walk-in sales revenue
        $walkInRevenue = DB::table('walk_in_sales')
            ->where('vendor_id', $vendorId)
            ->whereDate('sale_date', $date)
            ->selectRaw('SUM(quantity * unit_price) as total')
            ->value('total') ?? 0;

        return (float) ($preOrderRevenue + $walkInRevenue);
    }

    /**
     * Calculate net sales (same as gross_sales since there are no transaction fees).
     * Vendors pay stall rent separately (see stall_payments table), not per-sale fees.
     * 
     * @param int $vendorId
     * @param string $date (YYYY-MM-DD)
     * @return float
     */
    public static function calculateNetSales($vendorId, $date)
    {
        return self::calculateGrossSales($vendorId, $date);
    }

    /**
     * Get summary for a date range (useful for reports).
     * Returns aggregated metrics across multiple days.
     * 
     * @param int $vendorId
     * @param string $startDate (YYYY-MM-DD)
     * @param string $endDate (YYYY-MM-DD)
     * @return array
     */
    public static function getSummaryForDateRange($vendorId, $startDate, $endDate)
    {
        $startDate = Carbon::parse($startDate)->toDateString();
        $endDate = Carbon::parse($endDate)->toDateString();

        $totalPreOrders = DB::table('order_items')
            ->join('orders', 'order_items.order_id', '=', 'orders.id')
            ->where('order_items.vendor_id', $vendorId)
            ->whereBetween(DB::raw('DATE(orders.order_date)'), [$startDate, $endDate])
            ->whereIn('order_items.item_status', ['confirmed', 'ready', 'completed', 'out_for_delivery', 'delivered'])
            ->count();

        $totalWalkInSales = DB::table('walk_in_sales')
            ->where('vendor_id', $vendorId)
            ->whereBetween(DB::raw('DATE(sale_date)'), [$startDate, $endDate])
            ->count();

        $preOrderRevenue = DB::table('order_items')
            ->join('orders', 'order_items.order_id', '=', 'orders.id')
            ->where('order_items.vendor_id', $vendorId)
            ->whereBetween(DB::raw('DATE(orders.order_date)'), [$startDate, $endDate])
            ->whereIn('order_items.item_status', ['confirmed', 'ready', 'completed', 'out_for_delivery', 'delivered'])
            ->selectRaw('SUM(order_items.quantity * order_items.unit_price) as total')
            ->value('total') ?? 0;

        $walkInRevenue = DB::table('walk_in_sales')
            ->where('vendor_id', $vendorId)
            ->whereBetween(DB::raw('DATE(sale_date)'), [$startDate, $endDate])
            ->selectRaw('SUM(quantity * unit_price) as total')
            ->value('total') ?? 0;

        $grossSales = (float) ($preOrderRevenue + $walkInRevenue);

        return [
            'vendor_id' => $vendorId,
            'start_date' => $startDate,
            'end_date' => $endDate,
            'total_pre_orders' => $totalPreOrders,
            'total_walk_in_sales' => $totalWalkInSales,
            'gross_sales' => $grossSales,
            'net_sales' => $grossSales, // No transaction fees
        ];
    }
}
