<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Removes the vendor_transactions table which stored transaction-level records
     * where net_amount was a derived field (gross_amount - fee_amount).
     * 
     * This data can be reconstructed from:
     * - order_items (for pre-orders)
     * - walk_in_sales (for walk-in sales)
     * - market_fees configuration
     * 
     * This was an intermediate audit trail that duplicates data already present
     * in orders/walk_in_sales with computed fee amounts.
     */
    public function up(): void
    {
        Schema::dropIfExists('vendor_transactions');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::create('vendor_transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('vendor_id')->constrained()->onDelete('cascade');
            $table->date('transaction_date')->index();
            $table->enum('transaction_type', ['pre_order', 'walk_in']);
            $table->foreignId('order_id')->nullable()->constrained()->onDelete('set null');
            $table->foreignId('sale_id')->nullable()->constrained('walk_in_sales')->onDelete('set null');
            $table->decimal('gross_amount', 10, 2);
            $table->decimal('fee_amount', 10, 2);
            $table->decimal('net_amount', 10, 2);
            $table->timestamps();

            $table->index(['vendor_id', 'transaction_date']);
        });
    }
};
