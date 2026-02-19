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
        Schema::create('vendors', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->unique()->constrained()->onDelete('cascade');
            $table->string('business_name')->index();
            $table->text('vendor_bio')->nullable();
            $table->string('owner_name');
            $table->string('contact_phone', 20)->nullable();
            $table->string('contact_email')->nullable();
            $table->string('regional_sourcing_origin')->nullable();
            $table->text('business_description')->nullable();
            $table->string('business_hours')->default('8:00 AM - 6:00 PM');
            $table->string('logo_url', 500)->nullable();
            $table->string('banner_url', 500)->nullable();
            $table->string('banner_image')->nullable();
            $table->boolean('weekend_pickup_enabled')->default(true);
            $table->boolean('weekday_delivery_enabled')->default(false);
            $table->boolean('weekend_delivery_enabled')->default(false);
            $table->boolean('delivery_available')->default(false);
            $table->boolean('is_live')->default(false);
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('vendors');
    }
};
