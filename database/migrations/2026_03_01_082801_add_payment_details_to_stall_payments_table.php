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
        Schema::table('stall_payments', function (Blueprint $table) {
            $table->string('payment_method')->nullable()->after('status'); // cash, gcash, bank_transfer
            $table->string('payment_reference')->nullable()->after('payment_method');
            $table->string('billing_period')->nullable()->after('payment_reference'); // e.g. "March 2026"
        });
    }

    public function down(): void
    {
        Schema::table('stall_payments', function (Blueprint $table) {
            $table->dropColumn(['payment_method', 'payment_reference', 'billing_period']);
        });
    }
};
