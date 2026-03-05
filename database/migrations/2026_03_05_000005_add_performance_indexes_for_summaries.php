<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * Adds performance indexes to support the new computed summary queries.
     * These indexes accelerate the aggregate calculations in VendorSummaryService
     * and MarketSummaryService.
     */
    public function up(): void
    {
        // Index for order_items aggregate queries
        // Used by: VendorSummaryService::calculateGrossSales(), countPreOrders()
        Schema::table('order_items', function (Blueprint $table) {
            $table->index(['vendor_id', 'item_status'], 'idx_order_items_vendor_status');
            $table->index(['order_id', 'vendor_id'], 'idx_order_items_order_vendor');
        });

        // Index for orders date-based filtering
        // Used by: VendorSummaryService, MarketSummaryService
        Schema::table('orders', function (Blueprint $table) {
            $table->index(['order_date', 'order_status'], 'idx_orders_date_status');
        });

        // Index for walk_in_sales aggregations
        // Used by: Both services for date and vendor filtering
        Schema::table('walk_in_sales', function (Blueprint $table) {
            $table->index(['vendor_id', 'sale_date'], 'idx_walk_in_sales_vendor_date');
            $table->index(['sale_date'], 'idx_walk_in_sales_date');
        });

        // Index for stall_assignments vendor presence check
        // Used by: MarketSummaryService::countVendorsPresent()
        Schema::table('stall_assignments', function (Blueprint $table) {
            $table->index(['vendor_id', 'assigned_date'], 'idx_stall_assignments_vendor_date');
            $table->index(['assigned_date', 'end_date'], 'idx_stall_assignments_date_range');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('order_items', function (Blueprint $table) {
            $table->dropIndex('idx_order_items_vendor_status');
            $table->dropIndex('idx_order_items_order_vendor');
        });

        Schema::table('orders', function (Blueprint $table) {
            $table->dropIndex('idx_orders_date_status');
        });

        Schema::table('walk_in_sales', function (Blueprint $table) {
            $table->dropIndex('idx_walk_in_sales_vendor_date');
            $table->dropIndex('idx_walk_in_sales_date');
        });

        Schema::table('stall_assignments', function (Blueprint $table) {
            $table->dropIndex('idx_stall_assignments_vendor_date');
            $table->dropIndex('idx_stall_assignments_date_range');
        });
    }
};
