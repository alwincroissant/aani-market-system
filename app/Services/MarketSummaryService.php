<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

/**
 * Market Summary Service
 * 
 * Replaces the deprecated market_summary table with computed queries.
 * All metrics are calculated dynamically from vendor, order, walk_in_sales, and stall_assignment data.
 * Note: Vendors pay NO fees on sales. Market revenue comes from stall rental fees (see stall_payments table).
 */
class MarketSummaryService
{
    /**
     * Get market summary for a specific date.
     * 
     * @param Carbon|string $date
     * @return array
     */
    public static function getSummaryForDate($date)
    {
        $date = Carbon::parse($date)->toDateString();

        $grossRevenue = self::calculateGrossMarketRevenue($date);

        return [
            'summary_date' => $date,
            'total_vendors_present' => self::countVendorsPresent($date),
            'total_orders' => self::countTotalOrders($date),
            'total_walk_in_sales' => self::countTotalWalkInSales($date),
            'gross_market_revenue' => $grossRevenue,
            'net_vendor_payout' => $grossRevenue, // Vendors keep 100% of sales; stall rent is separate
        ];
    }

    /**
     * Count vendors with active stall assignments on a specific date.
     * A vendor is "present" if they have a stall assignment active on that date.
     * 
     * @param string $date (YYYY-MM-DD)
     * @return int
     */
    public static function countVendorsPresent($date)
    {
        // Find vendors with active stall assignments (not yet ended)
        // OR vendors who recorded walk-in sales that day
        $vendorsWithAssignments = DB::table('stall_assignments')
            ->where('assigned_date', '<=', $date)
            ->where(function ($query) use ($date) {
                $query->whereNull('end_date')
                    ->orWhere('end_date', '>=', $date);
            })
            ->distinct()
            ->count('vendor_id');

        // Also count vendors with sales activity that day (who might not have stall but did walk-in)
        $vendorsWithSales = DB::table('walk_in_sales')
            ->whereDate('sale_date', $date)
            ->distinct()
            ->count('vendor_id');

        // Combine and get unique count
        return DB::table(function ($query) use ($date) {
            $query->select('vendor_id')
                ->from('stall_assignments')
                ->where('assigned_date', '<=', $date)
                ->where(function ($q) use ($date) {
                    $q->whereNull('end_date')
                        ->orWhere('end_date', '>=', $date);
                })
                ->union(
                    DB::table('walk_in_sales')
                        ->whereDate('sale_date', $date)
                        ->select('vendor_id')
                );
        }, 'union_vendors')
            ->distinct()
            ->count('vendor_id');
    }

    /**
     * Count total orders placed on a specific date.
     * Excludes cancelled orders.
     * 
     * @param string $date (YYYY-MM-DD)
     * @return int
     */
    public static function countTotalOrders($date)
    {
        return DB::table('orders')
            ->whereDate('order_date', $date)
            ->whereNot('order_status', 'cancelled')
            ->count();
    }

    /**
     * Count total walk-in sales records on a specific date.
     * 
     * @param string $date (YYYY-MM-DD)
     * @return int
     */
    public static function countTotalWalkInSales($date)
    {
        return DB::table('walk_in_sales')
            ->whereDate('sale_date', $date)
            ->count();
    }

    /**
     * Calculate total gross market revenue (all sales before fees).
     * Includes: (order_items.qty * price) + (walk_in_sales.qty * price)
     * 
     * @param string $date (YYYY-MM-DD)
     * @return float
     */
    public static function calculateGrossMarketRevenue($date)
    {
        // Pre-order revenue
        $preOrderRevenue = DB::table('order_items')
            ->join('orders', 'order_items.order_id', '=', 'orders.id')
            ->whereDate('orders.order_date', $date)
            ->whereIn('order_items.item_status', ['confirmed', 'ready', 'completed', 'out_for_delivery', 'delivered'])
            ->selectRaw('SUM(order_items.quantity * order_items.unit_price) as total')
            ->value('total') ?? 0;

        // Walk-in sales revenue
        $walkInRevenue = DB::table('walk_in_sales')
            ->whereDate('sale_date', $date)
            ->selectRaw('SUM(quantity * unit_price) as total')
            ->value('total') ?? 0;

        return (float) ($preOrderRevenue + $walkInRevenue);
    }

    /**
     * Calculate net vendor payout (same as gross_market_revenue since there are no transaction fees).
     * Vendors keep 100% of sales revenue. Market revenue comes from stall rental fees.
     * 
     * @param string $date (YYYY-MM-DD)
     * @return float
     */
    public static function calculateNetVendorPayout($date)
    {
        return self::calculateGrossMarketRevenue($date);
    }

    /**
     * Get summary for a date range (useful for reports).
     * Returns aggregated metrics across multiple days.
     * 
     * @param string $startDate (YYYY-MM-DD)
     * @param string $endDate (YYYY-MM-DD)
     * @return array
     */
    public static function getSummaryForDateRange($startDate, $endDate)
    {
        $startDate = Carbon::parse($startDate)->toDateString();
        $endDate = Carbon::parse($endDate)->toDateString();

        $totalOrders = DB::table('orders')
            ->whereBetween(DB::raw('DATE(order_date)'), [$startDate, $endDate])
            ->whereNot('order_status', 'cancelled')
            ->count();

        $totalWalkInSales = DB::table('walk_in_sales')
            ->whereBetween(DB::raw('DATE(sale_date)'), [$startDate, $endDate])
            ->count();

        $preOrderRevenue = DB::table('order_items')
            ->join('orders', 'order_items.order_id', '=', 'orders.id')
            ->whereBetween(DB::raw('DATE(orders.order_date)'), [$startDate, $endDate])
            ->whereIn('order_items.item_status', ['confirmed', 'ready', 'completed', 'out_for_delivery', 'delivered'])
            ->selectRaw('SUM(order_items.quantity * order_items.unit_price) as total')
            ->value('total') ?? 0;

        $walkInRevenue = DB::table('walk_in_sales')
            ->whereBetween(DB::raw('DATE(sale_date)'), [$startDate, $endDate])
            ->selectRaw('SUM(quantity * unit_price) as total')
            ->value('total') ?? 0;

        $grossRevenue = (float) ($preOrderRevenue + $walkInRevenue);

        // Get unique vendors present in this range
        $totalVendorsPresent = DB::table(function ($query) use ($startDate, $endDate) {
            $query->select('vendor_id')
                ->from('stall_assignments')
                ->where('assigned_date', '<=', $endDate)
                ->where(function ($q) use ($endDate) {
                    $q->whereNull('end_date')
                        ->orWhere('end_date', '>=', $endDate);
                })
                ->union(
                    DB::table('walk_in_sales')
                        ->whereBetween(DB::raw('DATE(sale_date)'), [$startDate, $endDate])
                        ->select('vendor_id')
                );
        }, 'union_vendors')
            ->distinct()
            ->count('vendor_id');

        return [
            'start_date' => $startDate,
            'end_date' => $endDate,
            'total_vendors_present' => $totalVendorsPresent,
            'total_orders' => $totalOrders,
            'total_walk_in_sales' => $totalWalkInSales,
            'gross_market_revenue' => $grossRevenue,
            'net_vendor_payout' => $grossRevenue, // No transaction fees
        ];
    }
}
