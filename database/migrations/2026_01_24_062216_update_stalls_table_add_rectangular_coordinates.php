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
        Schema::table('stalls', function (Blueprint $table) {
            // Add rectangular coordinate fields
            $table->decimal('x1', 10, 2)->nullable()->after('position_y'); // Top-left X
            $table->decimal('y1', 10, 2)->nullable()->after('x1'); // Top-left Y
            $table->decimal('x2', 10, 2)->nullable()->after('y1'); // Bottom-right X
            $table->decimal('y2', 10, 2)->nullable()->after('x2'); // Bottom-right Y
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('stalls', function (Blueprint $table) {
            $table->dropColumn(['x1', 'y1', 'x2', 'y2']);
        });
    }
};
