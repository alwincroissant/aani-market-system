<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * Creates simplified database views for vendor and market summaries.
     * These views compute aggregates dynamically instead of storing persisted totals.
     * Note: Vendors pay NO transaction fees. Market revenue comes from stall rental (stall_payments).
     * 
     * OPTIONAL: Use these if you prefer direct SQL/database access over PHP service classes.
     * Note: View syntax may need adjustment based on your database engine (MySQL/PostgreSQL).
     */
    public function up(): void
    {
        // Note: These views are simplified examples.
        // For production use, consider using the VendorSummaryService and MarketSummaryService
        // PHP classes instead, as they handle edge cases and date filtering better.
        
        // Uncomment below if you want to create actual database views:
        
        /*
        // View: vendor_daily_sales_view
        // Shows daily sales metrics for each vendor (no fees - vendors keep 100% of sales)
        DB::statement(
            "CREATE VIEW vendor_daily_sales_view AS
            SELECT 
                v.id as vendor_id,
                v.business_name,
                DATE(COALESCE(o.order_date, w.sale_date)) as summary_date,
                COUNT(DISTINCT oi.order_id) as total_pre_orders,
                COUNT(DISTINCT w.id) as total_walk_in_sales,
                COALESCE(SUM(oi.quantity * oi.unit_price), 0) + 
                COALESCE(SUM(w.quantity * w.unit_price), 0) as gross_sales
            FROM vendors v
            LEFT JOIN order_items oi ON v.id = oi.vendor_id
            LEFT JOIN orders o ON oi.order_id = o.id AND o.order_status != 'cancelled'
            LEFT JOIN walk_in_sales w ON v.id = w.vendor_id
            WHERE (o.order_date IS NOT NULL OR w.sale_date IS NOT NULL)
            GROUP BY v.id, v.business_name, DATE(COALESCE(o.order_date, w.sale_date))"
        );

        // View: market_daily_summary_view
        // Shows market-wide daily metrics
        DB::statement(
            "CREATE VIEW market_daily_summary_view AS
            SELECT 
                DATE(COALESCE(o.order_date, w.sale_date)) as summary_date,
                COUNT(DISTINCT o.id) as total_orders,
                COUNT(DISTINCT w.id) as total_walk_in_sales,
                COALESCE(SUM(oi.quantity * oi.unit_price), 0) + 
                COALESCE(SUM(w.quantity * w.unit_price), 0) as gross_market_revenue
            FROM orders o
            LEFT JOIN order_items oi ON o.id = oi.order_id
            LEFT JOIN walk_in_sales w ON DATE(o.order_date) = DATE(w.sale_date)
            WHERE o.order_status != 'cancelled'
            GROUP BY DATE(COALESCE(o.order_date, w.sale_date))"
        );
        */
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // DB::statement('DROP VIEW IF EXISTS vendor_daily_sales_view');
        // DB::statement('DROP VIEW IF EXISTS market_daily_summary_view');
    }
};
