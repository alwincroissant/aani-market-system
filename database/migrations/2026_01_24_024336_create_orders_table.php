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
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->string('order_reference', 50)->unique()->index();
            $table->foreignId('customer_id')->constrained()->onDelete('cascade');
            $table->timestamp('order_date')->useCurrent()->index();
            $table->enum('fulfillment_type', ['weekend_pickup', 'weekday_delivery', 'weekend_delivery']);
            $table->enum('order_status', ['pending', 'confirmed', 'ready', 'completed', 'cancelled', 'preparing', 'awaiting_rider', 'out_for_delivery', 'delivered'])->default('pending')->index();
            $table->text('delivery_address')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        // Sync orders.order_status with the latest order_items.item_status
        // This ensures coherent status between orders and their items
        if (Schema::hasTable('order_items')) {
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
        Schema::dropIfExists('orders');
    }
};
