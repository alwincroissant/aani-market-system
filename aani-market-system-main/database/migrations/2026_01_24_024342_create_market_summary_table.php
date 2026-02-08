<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('market_summary', function (Blueprint $table) {
            $table->id();
            $table->date('summary_date')->unique()->index();
            $table->integer('total_vendors_present')->default(0);
            $table->integer('total_orders')->default(0);
            $table->integer('total_walk_in_sales')->default(0);
            $table->decimal('gross_market_revenue', 10, 2)->default(0);
            $table->decimal('total_market_fees', 10, 2)->default(0);
            $table->decimal('net_vendor_payout', 10, 2)->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('market_summary');
    }
};
