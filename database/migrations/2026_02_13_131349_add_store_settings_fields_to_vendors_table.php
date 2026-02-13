<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('vendors', function (Blueprint $table) {
            // Store settings fields
            $table->string('business_hours')->default('8:00 AM - 6:00 PM')->after('business_description');
            $table->boolean('delivery_available')->default(false)->after('weekend_delivery_enabled');
            
            // Farm details fields
            $table->string('farm_name')->nullable()->after('business_name');
            $table->string('region')->nullable()->after('regional_sourcing_origin');
            $table->text('complete_address')->nullable()->after('contact_email');
            $table->decimal('farm_size', 8, 2)->nullable()->after('complete_address');
            $table->integer('years_in_operation')->nullable()->after('farm_size');
        });
    }

    public function down(): void
    {
        Schema::table('vendors', function (Blueprint $table) {
            $table->dropColumn([
                'business_hours',
                'delivery_available',
                'farm_name',
                'region',
                'complete_address',
                'farm_size',
                'years_in_operation'
            ]);
        });
    }
};