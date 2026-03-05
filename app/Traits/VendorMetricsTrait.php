<?php

namespace App\Traits;

use App\Services\VendorSummaryService;
use Carbon\Carbon;

/**
 * VendorMetricsTrait
 * 
 * Adds convenient methods to Vendor model for accessing computed metrics.
 * Usage: Add `use VendorMetricsTrait;` to the Vendor model.
 */
trait VendorMetricsTrait
{
    /**
     * Get summary metrics for today.
     * 
     * @return array
     */
    public function getSummaryForToday()
    {
        return VendorSummaryService::getSummaryForDate($this->id, Carbon::today());
    }

    /**
     * Get summary metrics for a specific date.
     * 
     * @param Carbon|string $date
     * @return array
     */
    public function getSummaryForDate($date)
    {
        return VendorSummaryService::getSummaryForDate($this->id, $date);
    }

    /**
     * Get summary metrics for a date range.
     * 
     * @param Carbon|string $startDate
     * @param Carbon|string $endDate
     * @return array
     */
    public function getSummaryForDateRange($startDate, $endDate)
    {
        return VendorSummaryService::getSummaryForDateRange($this->id, $startDate, $endDate);
    }

    /**
     * Get total gross sales for a date.
     * 
     * @param Carbon|string|null $date
     * @return float
     */
    public function getGrossSalesForDate($date = null)
    {
        $date = $date ? Carbon::parse($date) : Carbon::today();
        return VendorSummaryService::calculateGrossSales($this->id, $date->toDateString());
    }

    /**
     * Get net sales for a date (same as gross sales since there are no transaction fees).
     * 
     * @param Carbon|string|null $date
     * @return float
     */
    public function getNetSalesForDate($date = null)
    {
        $date = $date ? Carbon::parse($date) : Carbon::today();
        return VendorSummaryService::calculateNetSales($this->id, $date->toDateString());
    }

    /**
     * Get count of pre-orders for a date.
     * 
     * @param Carbon|string|null $date
     * @return int
     */
    public function getPreOrderCountForDate($date = null)
    {
        $date = $date ? Carbon::parse($date) : Carbon::today();
        return VendorSummaryService::countPreOrders($this->id, $date->toDateString());
    }

    /**
     * Get count of walk-in sales for a date.
     * 
     * @param Carbon|string|null $date
     * @return int
     */
    public function getWalkInSalesCountForDate($date = null)
    {
        $date = $date ? Carbon::parse($date) : Carbon::today();
        return VendorSummaryService::countWalkInSales($this->id, $date->toDateString());
    }
}
