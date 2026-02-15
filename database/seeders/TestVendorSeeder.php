<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Vendor;
use App\Models\User;
use App\Models\Product;
use App\Models\ProductCategory;

class TestVendorSeeder extends Seeder
{
    public function run(): void
    {
        // Create test users first
        $user1 = User::firstOrCreate([
            'email' => 'vendor1@example.com'
        ], [
            'password' => bcrypt('password'),
            'role' => 'vendor'
        ]);

        $user2 = User::firstOrCreate([
            'email' => 'vendor2@example.com'
        ], [
            'password' => bcrypt('password'),
            'role' => 'vendor'
        ]);

        // Create test vendors linked to users
        $vendor1 = Vendor::firstOrCreate([
            'user_id' => $user1->id
        ], [
            'business_name' => 'Fresh Produce Stand',
            'owner_name' => 'John Farmer',
            'contact_phone' => '09123456789',
            'contact_email' => 'vendor1@example.com',
            'weekend_pickup_enabled' => true,
            'weekday_delivery_enabled' => true
        ]);

        $vendor2 = Vendor::firstOrCreate([
            'user_id' => $user2->id
        ], [
            'business_name' => 'Organic Goods Co.',
            'owner_name' => 'Sarah Green',
            'contact_phone' => '09987654321',
            'contact_email' => 'vendor2@example.com',
            'weekend_pickup_enabled' => true,
            'weekend_delivery_enabled' => true
        ]);

        // Get or create categories
        $vegetableCategory = ProductCategory::firstOrCreate(['category_name' => 'Vegetables']);
        $fruitCategory = ProductCategory::firstOrCreate(['category_name' => 'Fruits']);
        $dairyCategory = ProductCategory::firstOrCreate(['category_name' => 'Dairy']);

        // Create test products for vendor 1 (Fresh Produce Stand)
        Product::create([
            'vendor_id' => $vendor1->id,
            'category_id' => $vegetableCategory->id,
            'product_name' => 'Fresh Tomatoes',
            'description' => 'Locally grown ripe tomatoes, perfect for salads and cooking',
            'price_per_unit' => 45.50,
            'unit_type' => 'kg',
            'is_available' => true,
            'stock_quantity' => 25,
            'minimum_stock' => 10,
            'track_stock' => true,
            'allow_backorder' => false,
            'stock_notes' => 'Harvested daily, best quality'
        ]);

        Product::create([
            'vendor_id' => $vendor1->id,
            'category_id' => $vegetableCategory->id,
            'product_name' => 'Organic Lettuce',
            'description' => 'Crisp organic lettuce, grown without pesticides',
            'price_per_unit' => 35.00,
            'unit_type' => 'piece',
            'is_available' => true,
            'stock_quantity' => 8,
            'minimum_stock' => 15,
            'track_stock' => true,
            'allow_backorder' => true,
            'stock_notes' => 'Low stock alert - restock needed'
        ]);

        Product::create([
            'vendor_id' => $vendor1->id,
            'category_id' => $fruitCategory->id,
            'product_name' => 'Sweet Mangoes',
            'description' => 'Sweet and juicy mangoes, perfect for desserts',
            'price_per_unit' => 120.00,
            'unit_type' => 'kg',
            'is_available' => true,
            'stock_quantity' => 0,
            'minimum_stock' => 5,
            'track_stock' => true,
            'allow_backorder' => false,
            'stock_notes' => 'Out of season, back in summer'
        ]);

        // Create test products for vendor 2 (Organic Goods Co.)
        Product::create([
            'vendor_id' => $vendor2->id,
            'category_id' => $dairyCategory->id,
            'product_name' => 'Fresh Milk',
            'description' => 'Organic whole milk from grass-fed cows',
            'price_per_unit' => 65.00,
            'unit_type' => 'liter',
            'is_available' => true,
            'stock_quantity' => 30,
            'minimum_stock' => 10,
            'track_stock' => true,
            'allow_backorder' => true,
            'stock_notes' => 'Daily delivery available'
        ]);

        Product::create([
            'vendor_id' => $vendor2->id,
            'category_id' => $dairyCategory->id,
            'product_name' => 'Artisan Cheese',
            'description' => 'Handcrafted cheese aged for 6 months',
            'price_per_unit' => 450.00,
            'unit_type' => 'kg',
            'is_available' => true,
            'stock_quantity' => 5,
            'minimum_stock' => 3,
            'track_stock' => true,
            'allow_backorder' => false,
            'stock_notes' => 'Limited stock, premium quality'
        ]);

        Product::create([
            'vendor_id' => $vendor2->id,
            'category_id' => $fruitCategory->id,
            'product_name' => 'Organic Apples',
            'description' => 'Crisp organic apples, perfect for snacking',
            'price_per_unit' => 85.00,
            'unit_type' => 'kg',
            'is_available' => true,
            'stock_quantity' => 50,
            'minimum_stock' => 20,
            'track_stock' => true,
            'allow_backorder' => false,
            'stock_notes' => 'Multiple varieties available'
        ]);

        Product::create([
            'vendor_id' => $vendor2->id,
            'category_id' => $vegetableCategory->id,
            'product_name' => 'Fresh Carrots',
            'description' => 'Sweet and crunchy carrots, great for juicing',
            'price_per_unit' => 28.00,
            'unit_type' => 'kg',
            'is_available' => true,
            'stock_quantity' => 2,
            'minimum_stock' => 10,
            'track_stock' => true,
            'allow_backorder' => true,
            'stock_notes' => 'Very low stock - urgent restock needed'
        ]);

        $this->command->info('Test vendors and products created successfully!');
    }
}
