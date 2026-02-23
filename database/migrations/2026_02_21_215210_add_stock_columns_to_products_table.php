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
        // Add each column only if it doesn't already exist (safer for sqlite and redeploys)
        if (! Schema::hasColumn('products', 'stock_quantity')) {
            Schema::table('products', function (Blueprint $table) {
                $table->integer('stock_quantity')->default(0)->after('product_image_url');
            });
        }

        if (! Schema::hasColumn('products', 'minimum_stock')) {
            Schema::table('products', function (Blueprint $table) {
                $table->integer('minimum_stock')->default(5)->after('stock_quantity');
            });
        }

        if (! Schema::hasColumn('products', 'track_stock')) {
            Schema::table('products', function (Blueprint $table) {
                $table->boolean('track_stock')->default(true)->after('minimum_stock');
            });
        }

        if (! Schema::hasColumn('products', 'allow_backorder')) {
            Schema::table('products', function (Blueprint $table) {
                $table->boolean('allow_backorder')->default(false)->after('track_stock');
            });
        }

        if (! Schema::hasColumn('products', 'stock_notes')) {
            Schema::table('products', function (Blueprint $table) {
                $table->text('stock_notes')->nullable()->after('allow_backorder');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Drop each column only if it exists
        if (Schema::hasColumn('products', 'stock_quantity')) {
            Schema::table('products', function (Blueprint $table) {
                $table->dropColumn('stock_quantity');
            });
        }

        if (Schema::hasColumn('products', 'minimum_stock')) {
            Schema::table('products', function (Blueprint $table) {
                $table->dropColumn('minimum_stock');
            });
        }

        if (Schema::hasColumn('products', 'track_stock')) {
            Schema::table('products', function (Blueprint $table) {
                $table->dropColumn('track_stock');
            });
        }

        if (Schema::hasColumn('products', 'allow_backorder')) {
            Schema::table('products', function (Blueprint $table) {
                $table->dropColumn('allow_backorder');
            });
        }

        if (Schema::hasColumn('products', 'stock_notes')) {
            Schema::table('products', function (Blueprint $table) {
                $table->dropColumn('stock_notes');
            });
        }
    }
};
