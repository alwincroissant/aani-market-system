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

        // Note: Order status sync will be handled in a separate migration after order_items table is populated
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
