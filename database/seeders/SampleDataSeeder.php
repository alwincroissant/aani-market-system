<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class SampleDataSeeder extends Seeder
{
    public function run()
    {
        $profileImagePath = 'storage/maps/1769326582_6975c7f61ae02.jpg';
        $productImagePath = 'storage/products/product-placeholder.svg';

        // Create sample users for vendors
        $vendorUsers = [
            [
                'email' => 'freshfarm@example.com',
                'password' => Hash::make('password'),
                'role' => 'vendor',
                'profile_picture' => $profileImagePath,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'email' => 'greenhouse@example.com',
                'password' => Hash::make('password'),
                'role' => 'vendor',
                'profile_picture' => $profileImagePath,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'email' => 'meatmaster@example.com',
                'password' => Hash::make('password'),
                'role' => 'vendor',
                'profile_picture' => $profileImagePath,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'email' => 'foodhaven@example.com',
                'password' => Hash::make('password'),
                'role' => 'vendor',
                'profile_picture' => $profileImagePath,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'email' => 'sunrisedairy@example.com',
                'password' => Hash::make('password'),
                'role' => 'vendor',
                'profile_picture' => $profileImagePath,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'email' => 'islabakery@example.com',
                'password' => Hash::make('password'),
                'role' => 'vendor',
                'profile_picture' => $profileImagePath,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'email' => 'tropicalfruits@example.com',
                'password' => Hash::make('password'),
                'role' => 'vendor',
                'profile_picture' => $profileImagePath,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'email' => 'spicegarden@example.com',
                'password' => Hash::make('password'),
                'role' => 'vendor',
                'profile_picture' => $profileImagePath,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];

        foreach ($vendorUsers as $user) {
            DB::table('users')->insertOrIgnore($user);
        }

        // Get user IDs for vendors
        $userIds = DB::table('users')
            ->whereIn('email', [
                'freshfarm@example.com', 'greenhouse@example.com', 'meatmaster@example.com', 'foodhaven@example.com',
                'sunrisedairy@example.com', 'islabakery@example.com', 'tropicalfruits@example.com', 'spicegarden@example.com',
            ])
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
            [
                'user_id' => $userIds['sunrisedairy@example.com'],
                'business_name' => 'Sunrise Dairy',
                'owner_name' => 'Rosa Villanueva',
                'contact_phone' => '09123456793',
                'contact_email' => 'sunrisedairy@example.com',
                'regional_sourcing_origin' => 'Nueva Ecija',
                'business_description' => 'Fresh farm eggs, dairy products, and native chicken straight from our family farm in Nueva Ecija.',
                'logo_url' => null,
                'banner_url' => null,
                'weekend_pickup_enabled' => true,
                'weekday_delivery_enabled' => true,
                'weekend_delivery_enabled' => false,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'user_id' => $userIds['islabakery@example.com'],
                'business_name' => 'Isla Bakery',
                'owner_name' => 'Pedro Navarro',
                'contact_phone' => '09123456794',
                'contact_email' => 'islabakery@example.com',
                'regional_sourcing_origin' => 'Manila',
                'business_description' => 'Freshly baked Filipino breads, kakanin, and pastries baked every morning. A taste of home in every bite.',
                'logo_url' => null,
                'banner_url' => null,
                'weekend_pickup_enabled' => true,
                'weekday_delivery_enabled' => true,
                'weekend_delivery_enabled' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'user_id' => $userIds['tropicalfruits@example.com'],
                'business_name' => 'Tropical Fruits PH',
                'owner_name' => 'Liza Bautista',
                'contact_phone' => '09123456795',
                'contact_email' => 'tropicalfruits@example.com',
                'regional_sourcing_origin' => 'Davao',
                'business_description' => 'Exotic and seasonal tropical fruits sourced directly from Mindanao farms. Fresh, sweet, and affordable.',
                'logo_url' => null,
                'banner_url' => null,
                'weekend_pickup_enabled' => true,
                'weekday_delivery_enabled' => false,
                'weekend_delivery_enabled' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'user_id' => $userIds['spicegarden@example.com'],
                'business_name' => 'Spice Garden',
                'owner_name' => 'Ramon Dela Cruz',
                'contact_phone' => '09123456796',
                'contact_email' => 'spicegarden@example.com',
                'regional_sourcing_origin' => 'Bicol',
                'business_description' => 'Dried spices, condiments, native vinegar, and bagoong sourced from Bicol and surrounding regions.',
                'logo_url' => null,
                'banner_url' => null,
                'weekend_pickup_enabled' => true,
                'weekday_delivery_enabled' => true,
                'weekend_delivery_enabled' => false,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];

        foreach ($vendors as $vendor) {
            DB::table('vendors')->insertOrIgnore($vendor);
        }

        // Get vendor IDs
        $vendorIds = DB::table('vendors')
            ->whereIn('business_name', [
                'Fresh Farm Produce', 'Greenhouse Gardens', 'Meat Master', 'Food Haven',
                'Sunrise Dairy', 'Isla Bakery', 'Tropical Fruits PH', 'Spice Garden',
            ])
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
                'product_image_url' => $productImagePath,
                'is_available' => true,
                'stock_quantity' => 150,
                'minimum_stock' => 20,
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
                'product_image_url' => $productImagePath,
                'is_available' => true,
                'stock_quantity' => 80,
                'minimum_stock' => 15,
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
                'product_image_url' => $productImagePath,
                'is_available' => true,
                'stock_quantity' => 45,
                'minimum_stock' => 10,
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
                'product_image_url' => $productImagePath,
                'is_available' => true,
                'stock_quantity' => 25,
                'minimum_stock' => 5,
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
                'product_image_url' => $productImagePath,
                'is_available' => true,
                'stock_quantity' => 60,
                'minimum_stock' => 8,
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
                'product_image_url' => $productImagePath,
                'is_available' => true,
                'stock_quantity' => 15,
                'minimum_stock' => 3,
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
                'product_image_url' => $productImagePath,
                'is_available' => true,
                'stock_quantity' => 40,
                'minimum_stock' => 10,
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
                'product_image_url' => $productImagePath,
                'is_available' => true,
                'stock_quantity' => 75,
                'minimum_stock' => 15,
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
                'product_image_url' => $productImagePath,
                'is_available' => true,
                'stock_quantity' => 30,
                'minimum_stock' => 8,
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
                'product_image_url' => $productImagePath,
                'is_available' => true,
                'stock_quantity' => 20,
                'minimum_stock' => 5,
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
                'product_image_url' => $productImagePath,
                'is_available' => true,
                'stock_quantity' => 25,
                'minimum_stock' => 5,
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
                'product_image_url' => $productImagePath,
                'is_available' => true,
                'stock_quantity' => 50,
                'minimum_stock' => 10,
                'created_at' => now(),
                'updated_at' => now(),
            ],

            // Sunrise Dairy products
            [
                'vendor_id' => $vendorIds['Sunrise Dairy'],
                'category_id' => $categoryIds['Vegetables'], // closest available; ideally a Dairy category
                'product_name' => 'Native Farm Eggs',
                'description' => 'Free-range native eggs from grass-fed hens, sold per tray',
                'price_per_unit' => 220.00,
                'unit_type' => 'tray',
                'product_image_url' => $productImagePath,
                'is_available' => true,
                'stock_quantity' => 60,
                'minimum_stock' => 10,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'vendor_id' => $vendorIds['Sunrise Dairy'],
                'category_id' => $categoryIds['Vegetables'],
                'product_name' => 'Fresh Carabao Milk',
                'description' => 'Creamy carabao milk, bottled fresh daily, 500ml per bottle',
                'price_per_unit' => 85.00,
                'unit_type' => 'bottle',
                'product_image_url' => $productImagePath,
                'is_available' => true,
                'stock_quantity' => 40,
                'minimum_stock' => 8,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'vendor_id' => $vendorIds['Sunrise Dairy'],
                'category_id' => $categoryIds['Vegetables'],
                'product_name' => 'Kesong Puti',
                'description' => 'Soft white cheese made from fresh carabao milk, wrapped in banana leaf',
                'price_per_unit' => 120.00,
                'unit_type' => 'piece',
                'product_image_url' => $productImagePath,
                'is_available' => true,
                'stock_quantity' => 25,
                'minimum_stock' => 5,
                'created_at' => now(),
                'updated_at' => now(),
            ],

            // Isla Bakery products
            [
                'vendor_id' => $vendorIds['Isla Bakery'],
                'category_id' => $categoryIds['Snacks'],
                'product_name' => 'Pandesal',
                'description' => 'Classic soft Filipino bread rolls, freshly baked every morning, 10 pieces per pack',
                'price_per_unit' => 50.00,
                'unit_type' => 'pack',
                'product_image_url' => $productImagePath,
                'is_available' => true,
                'stock_quantity' => 80,
                'minimum_stock' => 15,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'vendor_id' => $vendorIds['Isla Bakery'],
                'category_id' => $categoryIds['Snacks'],
                'product_name' => 'Bibingka',
                'description' => 'Traditional rice cake baked in clay pots with salted egg and kesong puti',
                'price_per_unit' => 80.00,
                'unit_type' => 'piece',
                'product_image_url' => $productImagePath,
                'is_available' => true,
                'stock_quantity' => 30,
                'minimum_stock' => 5,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'vendor_id' => $vendorIds['Isla Bakery'],
                'category_id' => $categoryIds['Prepared Foods'],
                'product_name' => 'Ensaymada',
                'description' => 'Buttery brioche topped with sugar and cheese, perfect for merienda',
                'price_per_unit' => 65.00,
                'unit_type' => 'piece',
                'product_image_url' => $productImagePath,
                'is_available' => true,
                'stock_quantity' => 40,
                'minimum_stock' => 8,
                'created_at' => now(),
                'updated_at' => now(),
            ],

            // Tropical Fruits PH products
            [
                'vendor_id' => $vendorIds['Tropical Fruits PH'],
                'category_id' => $categoryIds['Fruits'],
                'product_name' => 'Durian',
                'description' => 'Premium Davao durian, creamy and sweet, sold per kilo',
                'price_per_unit' => 200.00,
                'unit_type' => 'kg',
                'product_image_url' => $productImagePath,
                'is_available' => true,
                'stock_quantity' => 30,
                'minimum_stock' => 5,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'vendor_id' => $vendorIds['Tropical Fruits PH'],
                'category_id' => $categoryIds['Fruits'],
                'product_name' => 'Lanzones',
                'description' => 'Sweet and juicy lanzones from Camiguin, in season',
                'price_per_unit' => 90.00,
                'unit_type' => 'kg',
                'product_image_url' => $productImagePath,
                'is_available' => true,
                'stock_quantity' => 50,
                'minimum_stock' => 10,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'vendor_id' => $vendorIds['Tropical Fruits PH'],
                'category_id' => $categoryIds['Fruits'],
                'product_name' => 'Rambutan',
                'description' => 'Fresh red rambutan, sweet and juicy, sold per kilo',
                'price_per_unit' => 70.00,
                'unit_type' => 'kg',
                'product_image_url' => $productImagePath,
                'is_available' => true,
                'stock_quantity' => 45,
                'minimum_stock' => 8,
                'created_at' => now(),
                'updated_at' => now(),
            ],

            // Spice Garden products
            [
                'vendor_id' => $vendorIds['Spice Garden'],
                'category_id' => $categoryIds['Vegetables'],
                'product_name' => 'Bicol Bagoong',
                'description' => 'Authentic fermented shrimp paste from Bicol, medium spicy, 250g jar',
                'price_per_unit' => 95.00,
                'unit_type' => 'jar',
                'product_image_url' => $productImagePath,
                'is_available' => true,
                'stock_quantity' => 60,
                'minimum_stock' => 10,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'vendor_id' => $vendorIds['Spice Garden'],
                'category_id' => $categoryIds['Vegetables'],
                'product_name' => 'Native Cane Vinegar',
                'description' => 'Traditionally fermented sugarcane vinegar, aged 6 months, 750ml bottle',
                'price_per_unit' => 75.00,
                'unit_type' => 'bottle',
                'product_image_url' => $productImagePath,
                'is_available' => true,
                'stock_quantity' => 40,
                'minimum_stock' => 8,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'vendor_id' => $vendorIds['Spice Garden'],
                'category_id' => $categoryIds['Vegetables'],
                'product_name' => 'Dried Chili Pack',
                'description' => 'Sun-dried Bicol siling labuyo, packed in 100g resealable bag',
                'price_per_unit' => 55.00,
                'unit_type' => 'pack',
                'product_image_url' => $productImagePath,
                'is_available' => true,
                'stock_quantity' => 70,
                'minimum_stock' => 12,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];

        foreach ($products as $product) {
            DB::table('products')->insertOrIgnore($product);
        }

        // Create stalls for the market map (A1–F8)
        // Section IDs: 1 = Vegetables, 2 = Plant Market, 3 = Meat & Fish, 4 = Food Section
        $stalls = [
            // ── Row A (section 4 – Food Section) ──────────────────────────────
            [
                'stall_number' => 'A1',
                'section_id'   => 4,
                'position_x'   => 112.97,
                'position_y'   => 1274.24,
                'x1' => 48.95,   'y1' => 1254.49,  'x2' => 176.98,  'y2' => 1293.99,
                'map_coordinates_json' => json_encode(['x1' => 48.95,  'y1' => 1254.49, 'x2' => 176.98, 'y2' => 1293.99]),
                'status' => 'available', 'created_at' => now(), 'updated_at' => now(),
            ],
            [
                'stall_number' => 'A2',
                'section_id'   => 4,
                'position_x'   => 241.87,
                'position_y'   => 1274.63,
                'x1' => 176.98,  'y1' => 1255.00,  'x2' => 306.76,  'y2' => 1294.25,
                'map_coordinates_json' => json_encode(['x1' => 176.98, 'y1' => 1255.00, 'x2' => 306.76, 'y2' => 1294.25]),
                'status' => 'available', 'created_at' => now(), 'updated_at' => now(),
            ],
            [
                'stall_number' => 'A3',
                'section_id'   => 4,
                'position_x'   => 371.39,
                'position_y'   => 1274.50,
                'x1' => 307.00,  'y1' => 1254.25,  'x2' => 435.77,  'y2' => 1294.75,
                'map_coordinates_json' => json_encode(['x1' => 307.00, 'y1' => 1254.25, 'x2' => 435.77, 'y2' => 1294.75]),
                'status' => 'available', 'created_at' => now(), 'updated_at' => now(),
            ],
            [
                'stall_number' => 'A4',
                'section_id'   => 4,
                'position_x'   => 500.51,
                'position_y'   => 1274.25,
                'x1' => 436.50,  'y1' => 1254.75,  'x2' => 564.52,  'y2' => 1293.75,
                'map_coordinates_json' => json_encode(['x1' => 436.50, 'y1' => 1254.75, 'x2' => 564.52, 'y2' => 1293.75]),
                'status' => 'available', 'created_at' => now(), 'updated_at' => now(),
            ],
            [
                'stall_number' => 'A5',
                'section_id'   => 4,
                'position_x'   => 538.51,
                'position_y'   => 1203.75,
                'x1' => 512.50,  'y1' => 1154.00,  'x2' => 564.51,  'y2' => 1253.50,
                'map_coordinates_json' => json_encode(['x1' => 512.50, 'y1' => 1154.00, 'x2' => 564.51, 'y2' => 1253.50]),
                'status' => 'available', 'created_at' => now(), 'updated_at' => now(),
            ],
            [
                'stall_number' => 'A6',
                'section_id'   => 4,
                'position_x'   => 73.85,
                'position_y'   => 1204.00,
                'x1' => 48.22,   'y1' => 1154.25,  'x2' => 99.48,   'y2' => 1253.75,
                'map_coordinates_json' => json_encode(['x1' => 48.22,  'y1' => 1154.25, 'x2' => 99.48,  'y2' => 1253.75]),
                'status' => 'available', 'created_at' => now(), 'updated_at' => now(),
            ],
            [
                'stall_number' => 'A7',
                'section_id'   => 4,
                'position_x'   => 73.99,
                'position_y'   => 1123.50,
                'x1' => 48.48,   'y1' => 1093.50,  'x2' => 99.49,   'y2' => 1153.50,
                'map_coordinates_json' => json_encode(['x1' => 48.48,  'y1' => 1093.50, 'x2' => 99.49,  'y2' => 1153.50]),
                'status' => 'available', 'created_at' => now(), 'updated_at' => now(),
            ],
            [
                'stall_number' => 'A8',
                'section_id'   => 4,
                'position_x'   => 538.38,
                'position_y'   => 1094.63,
                'x1' => 512.75,  'y1' => 1035.75,  'x2' => 564.01,  'y2' => 1153.50,
                'map_coordinates_json' => json_encode(['x1' => 512.75, 'y1' => 1035.75, 'x2' => 564.01, 'y2' => 1153.50]),
                'status' => 'available', 'created_at' => now(), 'updated_at' => now(),
            ],

            // ── Row B (section 4 – Food Section) ──────────────────────────────
            [
                'stall_number' => 'B1',
                'section_id'   => 4,
                'position_x'   => 73.36,
                'position_y'   => 1004.63,
                'x1' => 47.48,   'y1' => 974.75,   'x2' => 99.24,   'y2' => 1034.50,
                'map_coordinates_json' => json_encode(['x1' => 47.48,  'y1' => 974.75,  'x2' => 99.24,  'y2' => 1034.50]),
                'status' => 'available', 'created_at' => now(), 'updated_at' => now(),
            ],
            [
                'stall_number' => 'B2',
                'section_id'   => 4,
                'position_x'   => 74.11,
                'position_y'   => 904.63,
                'x1' => 48.73,   'y1' => 835.25,   'x2' => 99.49,   'y2' => 974.00,
                'map_coordinates_json' => json_encode(['x1' => 48.73,  'y1' => 835.25,  'x2' => 99.49,  'y2' => 974.00]),
                'status' => 'available', 'created_at' => now(), 'updated_at' => now(),
            ],

            // ── Row C (section 2 – Plant Market) ──────────────────────────────
            [
                'stall_number' => 'C1',
                'section_id'   => 2,
                'position_x'   => 628.76,
                'position_y'   => 1014.50,
                'x1' => 564.74,  'y1' => 994.50,   'x2' => 692.77,  'y2' => 1034.50,
                'map_coordinates_json' => json_encode(['x1' => 564.74, 'y1' => 994.50,  'x2' => 692.77, 'y2' => 1034.50]),
                'status' => 'available', 'created_at' => now(), 'updated_at' => now(),
            ],
            [
                'stall_number' => 'C2',
                'section_id'   => 2,
                'position_x'   => 499.76,
                'position_y'   => 1014.13,
                'x1' => 435.49,  'y1' => 994.50,   'x2' => 564.02,  'y2' => 1033.75,
                'map_coordinates_json' => json_encode(['x1' => 435.49, 'y1' => 994.50,  'x2' => 564.02, 'y2' => 1033.75]),
                'status' => 'available', 'created_at' => now(), 'updated_at' => now(),
            ],
            [
                'stall_number' => 'C3',
                'section_id'   => 2,
                'position_x'   => 629.11,
                'position_y'   => 934.13,
                'x1' => 564.47,  'y1' => 914.25,   'x2' => 693.75,  'y2' => 954.00,
                'map_coordinates_json' => json_encode(['x1' => 564.47, 'y1' => 914.25,  'x2' => 693.75, 'y2' => 954.00]),
                'status' => 'available', 'created_at' => now(), 'updated_at' => now(),
            ],
            [
                'stall_number' => 'C4',
                'section_id'   => 2,
                'position_x'   => 499.51,
                'position_y'   => 934.88,
                'x1' => 435.00,  'y1' => 915.25,   'x2' => 564.02,  'y2' => 954.50,
                'map_coordinates_json' => json_encode(['x1' => 435.00, 'y1' => 915.25,  'x2' => 564.02, 'y2' => 954.50]),
                'status' => 'available', 'created_at' => now(), 'updated_at' => now(),
            ],

            // ── Row D (section 1 – Vegetables) ────────────────────────────────
            [
                'stall_number' => 'D1',
                'section_id'   => 1,
                'position_x'   => 435.15,
                'position_y'   => 854.63,
                'x1' => 332.00,  'y1' => 834.75,   'x2' => 538.29,  'y2' => 874.50,
                'map_coordinates_json' => json_encode(['x1' => 332.00, 'y1' => 834.75,  'x2' => 538.29, 'y2' => 874.50]),
                'status' => 'available', 'created_at' => now(), 'updated_at' => now(),
            ],
            [
                'stall_number' => 'D2',
                'section_id'   => 1,
                'position_x'   => 641.76,
                'position_y'   => 855.25,
                'x1' => 590.00,  'y1' => 835.50,   'x2' => 693.52,  'y2' => 875.00,
                'map_coordinates_json' => json_encode(['x1' => 590.00, 'y1' => 835.50,  'x2' => 693.52, 'y2' => 875.00]),
                'status' => 'available', 'created_at' => now(), 'updated_at' => now(),
            ],

            // ── Row E (sections 1 & 3 – Vegetables / Meat & Fish) ─────────────
            [
                'stall_number' => 'E1',
                'section_id'   => 1,
                'position_x'   => 461.25,
                'position_y'   => 731.99,
                'x1' => 434.99,  'y1' => 647.99,   'x2' => 487.50,  'y2' => 815.99,
                'map_coordinates_json' => json_encode(['x1' => 434.99, 'y1' => 647.99,  'x2' => 487.50, 'y2' => 815.99]),
                'status' => 'available', 'created_at' => now(), 'updated_at' => now(),
            ],
            [
                'stall_number' => 'E2',
                'section_id'   => 3,
                'position_x'   => 513.50,
                'position_y'   => 731.37,
                'x1' => 488.00,  'y1' => 646.99,   'x2' => 539.00,  'y2' => 815.75,
                'map_coordinates_json' => json_encode(['x1' => 488.00, 'y1' => 646.99,  'x2' => 539.00, 'y2' => 815.75]),
                'status' => 'available', 'created_at' => now(), 'updated_at' => now(),
            ],
            [
                'stall_number' => 'E3',
                'section_id'   => 3,
                'position_x'   => 615.63,
                'position_y'   => 731.38,
                'x1' => 590.25,  'y1' => 647.25,   'x2' => 641.01,  'y2' => 815.50,
                'map_coordinates_json' => json_encode(['x1' => 590.25, 'y1' => 647.25,  'x2' => 641.01, 'y2' => 815.50]),
                'status' => 'available', 'created_at' => now(), 'updated_at' => now(),
            ],
            [
                'stall_number' => 'E4',
                'section_id'   => 1,
                'position_x'   => 667.76,
                'position_y'   => 731.25,
                'x1' => 642.00,  'y1' => 647.49,   'x2' => 693.52,  'y2' => 815.00,
                'map_coordinates_json' => json_encode(['x1' => 642.00, 'y1' => 647.49,  'x2' => 693.52, 'y2' => 815.00]),
                'status' => 'available', 'created_at' => now(), 'updated_at' => now(),
            ],
            [
                'stall_number' => 'E5',
                'section_id'   => 1,
                'position_x'   => 771.01,
                'position_y'   => 730.88,
                'x1' => 745.51,  'y1' => 647.00,   'x2' => 796.51,  'y2' => 814.75,
                'map_coordinates_json' => json_encode(['x1' => 745.51, 'y1' => 647.00,  'x2' => 796.51, 'y2' => 814.75]),
                'status' => 'available', 'created_at' => now(), 'updated_at' => now(),
            ],
            [
                'stall_number' => 'E6',
                'section_id'   => 3,
                'position_x'   => 822.76,
                'position_y'   => 730.63,
                'x1' => 797.00,  'y1' => 646.75,   'x2' => 848.51,  'y2' => 814.50,
                'map_coordinates_json' => json_encode(['x1' => 797.00, 'y1' => 646.75,  'x2' => 848.51, 'y2' => 814.50]),
                'status' => 'available', 'created_at' => now(), 'updated_at' => now(),
            ],
            [
                'stall_number' => 'E7',
                'section_id'   => 3,
                'position_x'   => 925.52,
                'position_y'   => 730.88,
                'x1' => 900.01,  'y1' => 647.50,   'x2' => 951.02,  'y2' => 814.25,
                'map_coordinates_json' => json_encode(['x1' => 900.01, 'y1' => 647.50,  'x2' => 951.02, 'y2' => 814.25]),
                'status' => 'available', 'created_at' => now(), 'updated_at' => now(),
            ],
            [
                'stall_number' => 'E8',
                'section_id'   => 1,
                'position_x'   => 977.27,
                'position_y'   => 730.50,
                'x1' => 951.52,  'y1' => 647.00,   'x2' => 1003.02, 'y2' => 814.00,
                'map_coordinates_json' => json_encode(['x1' => 951.52, 'y1' => 647.00,  'x2' => 1003.02, 'y2' => 814.00]),
                'status' => 'available', 'created_at' => now(), 'updated_at' => now(),
            ],

            // ── Row F (sections 1, 2 & 3) ─────────────────────────────────────
            [
                'stall_number' => 'F1',
                'section_id'   => 2,
                'position_x'   => 460.74,
                'position_y'   => 537.24,
                'x1' => 434.98,  'y1' => 447.99,   'x2' => 486.49,  'y2' => 626.49,
                'map_coordinates_json' => json_encode(['x1' => 434.98, 'y1' => 447.99,  'x2' => 486.49, 'y2' => 626.49]),
                'status' => 'available', 'created_at' => now(), 'updated_at' => now(),
            ],
            [
                'stall_number' => 'F2',
                'section_id'   => 1,
                'position_x'   => 513.00,
                'position_y'   => 537.24,
                'x1' => 486.99,  'y1' => 447.49,   'x2' => 539.00,  'y2' => 626.99,
                'map_coordinates_json' => json_encode(['x1' => 486.99, 'y1' => 447.49,  'x2' => 539.00, 'y2' => 626.99]),
                'status' => 'available', 'created_at' => now(), 'updated_at' => now(),
            ],
            [
                'stall_number' => 'F3',
                'section_id'   => 1,
                'position_x'   => 616.01,
                'position_y'   => 537.99,
                'x1' => 590.00,  'y1' => 448.49,   'x2' => 642.01,  'y2' => 627.49,
                'map_coordinates_json' => json_encode(['x1' => 590.00, 'y1' => 448.49,  'x2' => 642.01, 'y2' => 627.49]),
                'status' => 'available', 'created_at' => now(), 'updated_at' => now(),
            ],
            [
                'stall_number' => 'F4',
                'section_id'   => 3,
                'position_x'   => 668.26,
                'position_y'   => 536.74,
                'x1' => 643.50,  'y1' => 447.99,   'x2' => 693.01,  'y2' => 625.49,
                'map_coordinates_json' => json_encode(['x1' => 643.50, 'y1' => 447.99,  'x2' => 693.01, 'y2' => 625.49]),
                'status' => 'available', 'created_at' => now(), 'updated_at' => now(),
            ],
            [
                'stall_number' => 'F5',
                'section_id'   => 3,
                'position_x'   => 771.02,
                'position_y'   => 536.74,
                'x1' => 745.51,  'y1' => 446.49,   'x2' => 796.52,  'y2' => 626.99,
                'map_coordinates_json' => json_encode(['x1' => 745.51, 'y1' => 446.49,  'x2' => 796.52, 'y2' => 626.99]),
                'status' => 'available', 'created_at' => now(), 'updated_at' => now(),
            ],
            [
                'stall_number' => 'F6',
                'section_id'   => 1,
                'position_x'   => 823.02,
                'position_y'   => 537.99,
                'x1' => 797.01,  'y1' => 448.49,   'x2' => 849.02,  'y2' => 627.49,
                'map_coordinates_json' => json_encode(['x1' => 797.01, 'y1' => 448.49,  'x2' => 849.02, 'y2' => 627.49]),
                'status' => 'available', 'created_at' => now(), 'updated_at' => now(),
            ],
            [
                'stall_number' => 'F7',
                'section_id'   => 1,
                'position_x'   => 926.04,
                'position_y'   => 537.49,
                'x1' => 900.03,  'y1' => 447.49,   'x2' => 952.04,  'y2' => 627.49,
                'map_coordinates_json' => json_encode(['x1' => 900.03, 'y1' => 447.49,  'x2' => 952.04, 'y2' => 627.49]),
                'status' => 'available', 'created_at' => now(), 'updated_at' => now(),
            ],
            [
                'stall_number' => 'F8',
                'section_id'   => 3,
                'position_x'   => 977.65,
                'position_y'   => 536.88,
                'x1' => 952.52,  'y1' => 446.25,   'x2' => 1002.77, 'y2' => 627.50,
                'map_coordinates_json' => json_encode(['x1' => 952.52, 'y1' => 446.25,  'x2' => 1002.77, 'y2' => 627.50]),
                'status' => 'available', 'created_at' => now(), 'updated_at' => now(),
            ],
        ];

        foreach ($stalls as $stall) {
            DB::table('stalls')->insertOrIgnore($stall);
        }

        // Fetch stall IDs by stall_number for reliable assignment
        $stallIds = DB::table('stalls')
            ->whereIn('stall_number', ['D1', 'C1', 'E2', 'A1', 'D2', 'B1', 'E1', 'F2'])
            ->pluck('id', 'stall_number');

        // Assign vendors to their corresponding stalls
        // D1 (Vegetables/section 1) → Fresh Farm Produce
        // C1 (Plant Market/section 2) → Greenhouse Gardens
        // E2 (Meat & Fish/section 3) → Meat Master
        // A1 (Food Section/section 4) → Food Haven
        // D2 (Vegetables/section 1) → Sunrise Dairy
        // B1 (Food Section/section 4) → Isla Bakery
        // E1 (Vegetables/section 1) → Tropical Fruits PH
        // F2 (Vegetables/section 1) → Spice Garden
        $stallAssignments = [
            [
                'stall_id'      => $stallIds['D1'],
                'vendor_id'     => $vendorIds['Fresh Farm Produce'],
                'assigned_date' => now()->toDateString(),
                'end_date'      => null,
                'created_at'    => now(),
                'updated_at'    => now(),
            ],
            [
                'stall_id'      => $stallIds['C1'],
                'vendor_id'     => $vendorIds['Greenhouse Gardens'],
                'assigned_date' => now()->toDateString(),
                'end_date'      => null,
                'created_at'    => now(),
                'updated_at'    => now(),
            ],
            [
                'stall_id'      => $stallIds['E2'],
                'vendor_id'     => $vendorIds['Meat Master'],
                'assigned_date' => now()->toDateString(),
                'end_date'      => null,
                'created_at'    => now(),
                'updated_at'    => now(),
            ],
            [
                'stall_id'      => $stallIds['A1'],
                'vendor_id'     => $vendorIds['Food Haven'],
                'assigned_date' => now()->toDateString(),
                'end_date'      => null,
                'created_at'    => now(),
                'updated_at'    => now(),
            ],
            [
                'stall_id'      => $stallIds['D2'],
                'vendor_id'     => $vendorIds['Sunrise Dairy'],
                'assigned_date' => now()->toDateString(),
                'end_date'      => null,
                'created_at'    => now(),
                'updated_at'    => now(),
            ],
            [
                'stall_id'      => $stallIds['B1'],
                'vendor_id'     => $vendorIds['Isla Bakery'],
                'assigned_date' => now()->toDateString(),
                'end_date'      => null,
                'created_at'    => now(),
                'updated_at'    => now(),
            ],
            [
                'stall_id'      => $stallIds['E1'],
                'vendor_id'     => $vendorIds['Tropical Fruits PH'],
                'assigned_date' => now()->toDateString(),
                'end_date'      => null,
                'created_at'    => now(),
                'updated_at'    => now(),
            ],
            [
                'stall_id'      => $stallIds['F2'],
                'vendor_id'     => $vendorIds['Spice Garden'],
                'assigned_date' => now()->toDateString(),
                'end_date'      => null,
                'created_at'    => now(),
                'updated_at'    => now(),
            ],
        ];

        foreach ($stallAssignments as $assignment) {
            DB::table('stall_assignments')->insertOrIgnore($assignment);
        }

        // Mark assigned stalls as occupied
        DB::table('stalls')
            ->whereIn('stall_number', ['D1', 'C1', 'E2', 'A1', 'D2', 'B1', 'E1', 'F2'])
            ->update(['status' => 'occupied']);

        $this->command->info('Sample data seeded successfully!');
        $this->command->info('Vendor Login Credentials:');
        $this->command->info('Fresh Farm Produce: freshfarm@example.com / password');
        $this->command->info('Greenhouse Gardens: greenhouse@example.com / password');
        $this->command->info('Meat Master: meatmaster@example.com / password');
        $this->command->info('Food Haven: foodhaven@example.com / password');
        $this->command->info('Sunrise Dairy: sunrisedairy@example.com / password');
        $this->command->info('Isla Bakery: islabakery@example.com / password');
        $this->command->info('Tropical Fruits PH: tropicalfruits@example.com / password');
        $this->command->info('Spice Garden: spicegarden@example.com / password');
    }
}