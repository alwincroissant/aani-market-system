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
        Schema::create('market_fees', function (Blueprint $table) {
            $table->id();
            $table->string('fee_name', 100);
            $table->enum('fee_type', ['percentage', 'fixed']);
            $table->decimal('fee_value', 10, 2);
            $table->boolean('is_active')->default(true);
            $table->date('effective_date')->index();
            $table->date('end_date')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('market_fees');
    }
};
