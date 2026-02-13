<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('order_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained()->onDelete('cascade');
            $table->foreignId('vendor_id')->constrained()->onDelete('cascade');
            $table->foreignId('product_id')->constrained();
            $table->decimal('quantity', 10, 2);
            $table->decimal('unit_price', 10, 2);
            $table->enum('item_status', ['pending', 'confirmed', 'ready', 'completed', 'cancelled', 'preparing', 'awaiting_rider', 'out_for_delivery', 'delivered'])->default('pending')->index();
            $table->text('vendor_notes')->nullable();
            $table->timestamps();
        });

        // Sync existing orders with their items after creating order_items table
        // This ensures coherent status between orders and their items
        if (Schema::hasTable('orders')) {
            DB::statement("
                UPDATE orders o
                INNER JOIN (
                    SELECT 
                        oi.order_id,
                        oi.item_status as latest_status,
                        oi.updated_at
                    FROM order_items oi
                    INNER JOIN (
                        SELECT 
                            order_id, 
                            MAX(updated_at) as max_updated_at
                        FROM order_items
                        GROUP BY order_id
                    ) latest ON oi.order_id = latest.order_id AND oi.updated_at = latest.max_updated_at
                ) latest_items ON o.id = latest_items.order_id
                SET o.order_status = latest_items.latest_status,
                    o.updated_at = NOW()
            ");
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('order_items');
    }
};
