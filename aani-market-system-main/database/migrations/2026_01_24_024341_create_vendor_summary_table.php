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
        Schema::create('vendor_summary', function (Blueprint $table) {
            $table->id();
            $table->foreignId('vendor_id')->constrained()->onDelete('cascade');
            $table->date('summary_date')->index();
            $table->integer('total_pre_orders')->default(0);
            $table->integer('total_walk_in_sales')->default(0);
            $table->decimal('gross_sales', 10, 2)->default(0);
            $table->decimal('total_fees', 10, 2)->default(0);
            $table->decimal('net_sales', 10, 2)->default(0);
            $table->timestamps();
            
            $table->unique(['vendor_id', 'summary_date']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('vendor_summary');
    }
};
