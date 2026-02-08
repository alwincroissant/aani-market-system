<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class SampleDataSeeder extends Seeder
{
    public function run()
    {
        // Create sample users for vendors
        $vendorUsers = [
            [
                'email' => 'freshfarm@example.com',
                'password' => Hash::make('password'),
                'role' => 'vendor',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'email' => 'greenhouse@example.com',
                'password' => Hash::make('password'),
                'role' => 'vendor',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'email' => 'meatmaster@example.com',
                'password' => Hash::make('password'),
                'role' => 'vendor',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'email' => 'foodhaven@example.com',
                'password' => Hash::make('password'),
                'role' => 'vendor',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];

        foreach ($vendorUsers as $user) {
            DB::table('users')->insertOrIgnore($user);
        }

        // Get user IDs for vendors
        $userIds = DB::table('users')
            ->whereIn('email', ['freshfarm@example.com', 'greenhouse@example.com', 'meatmaster@example.com', 'foodhaven@example.com'])
            ->pluck('id', 'email');

        // Create sample vendors
        $vendors = [
            [
                'user_id' => $userIds['freshfarm@example.com'],
                'business_name' => 'Fresh Farm Produce',
                'owner_name' => 'Juan Santos',
                'contact_phone' => '09123456789',
                'contact_email' => 'freshfarm@example.com',
                'regional_sourcing_origin' => 'Laguna',
                'business_description' => 'Fresh organic vegetables and fruits directly from our farm. No pesticides, all natural goodness.',
                'logo_url' => null,
                'banner_url' => null,
                'weekend_pickup_enabled' => true,
                'weekday_delivery_enabled' => true,
                'weekend_delivery_enabled' => false,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'user_id' => $userIds['greenhouse@example.com'],
                'business_name' => 'Greenhouse Gardens',
                'owner_name' => 'Maria Reyes',
                'contact_phone' => '09123456790',
                'contact_email' => 'greenhouse@example.com',
                'regional_sourcing_origin' => 'Batangas',
                'business_description' => 'Beautiful plants, flowers, and gardening supplies. Transform your space with our curated collection.',
                'logo_url' => null,
                'banner_url' => null,
                'weekend_pickup_enabled' => true,
                'weekday_delivery_enabled' => false,
                'weekend_delivery_enabled' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'user_id' => $userIds['meatmaster@example.com'],
                'business_name' => 'Meat Master',
                'owner_name' => 'Carlos Cruz',
                'contact_phone' => '09123456791',
                'contact_email' => 'meatmaster@example.com',
                'regional_sourcing_origin' => 'Cavite',
                'business_description' => 'Premium quality meats and fresh seafood. All products are locally sourced and inspected daily.',
                'logo_url' => null,
                'banner_url' => null,
                'weekend_pickup_enabled' => true,
                'weekday_delivery_enabled' => true,
                'weekend_delivery_enabled' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'user_id' => $userIds['foodhaven@example.com'],
                'business_name' => 'Food Haven',
                'owner_name' => 'Ana Martinez',
                'contact_phone' => '09123456792',
                'contact_email' => 'foodhaven@example.com',
                'regional_sourcing_origin' => 'Quezon City',
                'business_description' => 'Delicious prepared foods, snacks, and ready-to-eat meals. Made with love and fresh ingredients.',
                'logo_url' => null,
                'banner_url' => null,
                'weekend_pickup_enabled' => true,
                'weekday_delivery_enabled' => true,
                'weekend_delivery_enabled' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];

        foreach ($vendors as $vendor) {
            DB::table('vendors')->insertOrIgnore($vendor);
        }

        // Get vendor IDs
        $vendorIds = DB::table('vendors')
            ->whereIn('business_name', ['Fresh Farm Produce', 'Greenhouse Gardens', 'Meat Master', 'Food Haven'])
            ->pluck('id', 'business_name');

        // Create sample product categories
        $categories = [
            ['category_name' => 'Vegetables', 'color_code' => '#28a745'],
            ['category_name' => 'Fruits', 'color_code' => '#fd7e14'],
            ['category_name' => 'Plants & Flowers', 'color_code' => '#6f42c1'],
            ['category_name' => 'Meat', 'color_code' => '#6c757d'],
            ['category_name' => 'Seafood', 'color_code' => '#17a2b8'],
            ['category_name' => 'Prepared Foods', 'color_code' => '#dc3545'],
            ['category_name' => 'Snacks', 'color_code' => '#ffc107'],
        ];

        foreach ($categories as $category) {
            DB::table('product_categories')->insertOrIgnore($category);
        }

        // Get category IDs
        $categoryIds = DB::table('product_categories')
            ->whereIn('category_name', ['Vegetables', 'Fruits', 'Plants & Flowers', 'Meat', 'Seafood', 'Prepared Foods', 'Snacks'])
            ->pluck('id', 'category_name');

        // Create sample products
        $products = [
            // Fresh Farm Produce products
            [
                'vendor_id' => $vendorIds['Fresh Farm Produce'],
                'category_id' => $categoryIds['Vegetables'],
                'product_name' => 'Fresh Tomatoes',
                'description' => 'Ripe, juicy tomatoes perfect for salads and cooking',
                'price_per_unit' => 80.00,
                'unit_type' => 'kg',
                'product_image_url' => null,
                'is_available' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'vendor_id' => $vendorIds['Fresh Farm Produce'],
                'category_id' => $categoryIds['Vegetables'],
                'product_name' => 'Organic Lettuce',
                'description' => 'Crisp organic lettuce, pesticide-free',
                'price_per_unit' => 120.00,
                'unit_type' => 'kg',
                'product_image_url' => null,
                'is_available' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'vendor_id' => $vendorIds['Fresh Farm Produce'],
                'category_id' => $categoryIds['Fruits'],
                'product_name' => 'Sweet Mangoes',
                'description' => 'Sweet, ripe mangoes from our orchard',
                'price_per_unit' => 150.00,
                'unit_type' => 'kg',
                'product_image_url' => null,
                'is_available' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],

            // Greenhouse Gardens products
            [
                'vendor_id' => $vendorIds['Greenhouse Gardens'],
                'category_id' => $categoryIds['Plants & Flowers'],
                'product_name' => 'Potted Orchids',
                'description' => 'Beautiful blooming orchids in decorative pots',
                'price_per_unit' => 450.00,
                'unit_type' => 'piece',
                'product_image_url' => null,
                'is_available' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'vendor_id' => $vendorIds['Greenhouse Gardens'],
                'category_id' => $categoryIds['Plants & Flowers'],
                'product_name' => 'Indoor Succulents',
                'description' => 'Low-maintenance succulents perfect for indoor spaces',
                'price_per_unit' => 180.00,
                'unit_type' => 'piece',
                'product_image_url' => null,
                'is_available' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'vendor_id' => $vendorIds['Greenhouse Gardens'],
                'category_id' => $categoryIds['Plants & Flowers'],
                'product_name' => 'Rose Bouquet',
                'description' => 'Fresh cut roses, perfect for special occasions',
                'price_per_unit' => 350.00,
                'unit_type' => 'bouquet',
                'product_image_url' => null,
                'is_available' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],

            // Meat Master products
            [
                'vendor_id' => $vendorIds['Meat Master'],
                'category_id' => $categoryIds['Meat'],
                'product_name' => 'Premium Pork Belly',
                'description' => 'Fresh, high-quality pork belly perfect for grilling',
                'price_per_unit' => 280.00,
                'unit_type' => 'kg',
                'product_image_url' => null,
                'is_available' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'vendor_id' => $vendorIds['Meat Master'],
                'category_id' => $categoryIds['Meat'],
                'product_name' => 'Chicken Thighs',
                'description' => 'Tender chicken thighs, boneless and skinless',
                'price_per_unit' => 160.00,
                'unit_type' => 'kg',
                'product_image_url' => null,
                'is_available' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'vendor_id' => $vendorIds['Meat Master'],
                'category_id' => $categoryIds['Seafood'],
                'product_name' => 'Fresh Bangus',
                'description' => 'Fresh milkfish, locally sourced',
                'price_per_unit' => 120.00,
                'unit_type' => 'piece',
                'product_image_url' => null,
                'is_available' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],

            // Food Haven products
            [
                'vendor_id' => $vendorIds['Food Haven'],
                'category_id' => $categoryIds['Prepared Foods'],
                'product_name' => 'Adobo Combo',
                'description' => 'Classic Filipino adobo with rice, serves 2',
                'price_per_unit' => 180.00,
                'unit_type' => 'meal',
                'product_image_url' => null,
                'is_available' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'vendor_id' => $vendorIds['Food Haven'],
                'category_id' => $categoryIds['Prepared Foods'],
                'product_name' => 'Pancit Canton',
                'description' => 'Stir-fried noodles with vegetables and meat',
                'price_per_unit' => 150.00,
                'unit_type' => 'plate',
                'product_image_url' => null,
                'is_available' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'vendor_id' => $vendorIds['Food Haven'],
                'category_id' => $categoryIds['Snacks'],
                'product_name' => 'Banana Cue',
                'description' => 'Sweet caramelized bananas, 3 pieces per pack',
                'price_per_unit' => 45.00,
                'unit_type' => 'pack',
                'product_image_url' => null,
                'is_available' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];

        foreach ($products as $product) {
            DB::table('products')->insertOrIgnore($product);
        }

        // Create sample stalls for the market map
        $stalls = [
            [
                'stall_number' => '1A',
                'section_id' => 1, // Vegetables section
                'position_x' => 200,
                'position_y' => 400,
                'x1' => 150,
                'y1' => 350,
                'x2' => 250,
                'y2' => 450,
                'map_coordinates_json' => json_encode(['x1' => 150, 'y1' => 350, 'x2' => 250, 'y2' => 450]),
                'status' => 'available',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'stall_number' => '1B',
                'section_id' => 1, // Vegetables section
                'position_x' => 300,
                'position_y' => 400,
                'x1' => 250,
                'y1' => 350,
                'x2' => 350,
                'y2' => 450,
                'map_coordinates_json' => json_encode(['x1' => 250, 'y1' => 350, 'x2' => 350, 'y2' => 450]),
                'status' => 'available',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'stall_number' => '2A',
                'section_id' => 2, // Plant Market section
                'position_x' => 400,
                'position_y' => 400,
                'x1' => 350,
                'y1' => 350,
                'x2' => 450,
                'y2' => 450,
                'map_coordinates_json' => json_encode(['x1' => 350, 'y1' => 350, 'x2' => 450, 'y2' => 450]),
                'status' => 'available',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'stall_number' => '2B',
                'section_id' => 2, // Plant Market section
                'position_x' => 500,
                'position_y' => 400,
                'x1' => 450,
                'y1' => 350,
                'x2' => 550,
                'y2' => 450,
                'map_coordinates_json' => json_encode(['x1' => 450, 'y1' => 350, 'x2' => 550, 'y2' => 450]),
                'status' => 'available',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'stall_number' => '3A',
                'section_id' => 3, // Meat and Fish section
                'position_x' => 600,
                'position_y' => 400,
                'x1' => 550,
                'y1' => 350,
                'x2' => 650,
                'y2' => 450,
                'map_coordinates_json' => json_encode(['x1' => 550, 'y1' => 350, 'x2' => 650, 'y2' => 450]),
                'status' => 'available',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'stall_number' => '3B',
                'section_id' => 3, // Meat and Fish section
                'position_x' => 700,
                'position_y' => 400,
                'x1' => 650,
                'y1' => 350,
                'x2' => 750,
                'y2' => 450,
                'map_coordinates_json' => json_encode(['x1' => 650, 'y1' => 350, 'x2' => 750, 'y2' => 450]),
                'status' => 'available',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'stall_number' => '4A',
                'section_id' => 4, // Food Section
                'position_x' => 800,
                'position_y' => 400,
                'x1' => 750,
                'y1' => 350,
                'x2' => 850,
                'y2' => 450,
                'map_coordinates_json' => json_encode(['x1' => 750, 'y1' => 350, 'x2' => 850, 'y2' => 450]),
                'status' => 'available',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'stall_number' => '4B',
                'section_id' => 4, // Food Section
                'position_x' => 900,
                'position_y' => 400,
                'x1' => 850,
                'y1' => 350,
                'x2' => 950,
                'y2' => 450,
                'map_coordinates_json' => json_encode(['x1' => 850, 'y1' => 350, 'x2' => 950, 'y2' => 450]),
                'status' => 'available',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];

        foreach ($stalls as $stall) {
            DB::table('stalls')->insertOrIgnore($stall);
        }

        // Assign some vendors to stalls for demonstration
        $stallAssignments = [
            [
                'stall_id' => 1, // 1A
                'vendor_id' => $vendorIds['Meat Master'],
                'assigned_date' => now()->toDateString(),
                'end_date' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'stall_id' => 7, // 4A
                'vendor_id' => $vendorIds['Food Haven'],
                'assigned_date' => now()->toDateString(),
                'end_date' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];

        foreach ($stallAssignments as $assignment) {
            DB::table('stall_assignments')->insertOrIgnore($assignment);
        }

        // Update stall statuses for assigned stalls
        DB::table('stalls')->whereIn('id', [1, 7])->update(['status' => 'occupied']);

        $this->command->info('Sample data seeded successfully!');
        $this->command->info('Vendor Login Credentials:');
        $this->command->info('Fresh Farm Produce: freshfarm@example.com / password');
        $this->command->info('Greenhouse Gardens: greenhouse@example.com / password');
        $this->command->info('Meat Master: meatmaster@example.com / password');
        $this->command->info('Food Haven: foodhaven@example.com / password');
    }
}
