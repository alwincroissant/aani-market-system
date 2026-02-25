<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\User;
use App\Models\Product;
use App\Models\StockLog;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;

$profileImagePath = 'storage/maps/1769326582_6975c7f61ae02.jpg';
$productImagePath = 'storage/maps/1769326582_6975c7f61ae02.jpg';

$vendorEmail = 'vendor2@test.com';
$vendorPassword = 'password';

// Create or reuse vendor user
$user = User::where('email', $vendorEmail)->first();
if (! $user) {
    $user = User::create([
        'email' => $vendorEmail,
        'password' => Hash::make($vendorPassword),
        'role' => 'vendor',
        'email_verified_at' => now(),
        'profile_picture' => $profileImagePath,
    ]);
    echo "Created user: {$vendorEmail} (id={$user->id})\n";
} else {
    echo "User already exists: {$vendorEmail} (id={$user->id})\n";
}

// Create vendor profile if missing
$existingVendor = DB::table('vendors')->where('user_id', $user->id)->first();
if (! $existingVendor) {
    $vendorId = DB::table('vendors')->insertGetId([
        'user_id' => $user->id,
        'business_name' => 'Green Valley Goods',
        'owner_name' => 'Alice Greene',
        'contact_phone' => '+19876543210',
        'contact_email' => $vendorEmail,
        'regional_sourcing_origin' => 'Green Valley Region',
        'business_description' => 'Delivering wholesome local produce and pantry items',
        'logo_url' => null,
        'banner_url' => null,
        'weekend_pickup_enabled' => true,
        'weekday_delivery_enabled' => false,
        'weekend_delivery_enabled' => true,
        'created_at' => now(),
        'updated_at' => now(),
    ]);
    echo "Created vendor profile: Green Valley Goods (id={$vendorId})\n";
} else {
    $vendorId = $existingVendor->id;
    echo "Vendor profile already exists (id={$vendorId})\n";
}

// Ensure a product category exists
$category = DB::table('product_categories')->first();
if (! $category) {
    $categoryId = DB::table('product_categories')->insertGetId([
        'category_name' => 'General',
        'color_code' => '#99CC66',
        'created_at' => now(),
        'updated_at' => now(),
    ]);
    echo "Created default category id={$categoryId}\n";
} else {
    $categoryId = $category->id;
}

$products = [
    [
        'product_name' => 'Ridge Lettuce (each)',
        'description' => 'Crisp ridge-grown lettuce',
        'price_per_unit' => 80.00,
        'unit_type' => 'each',
        'stock_quantity' => 40,
    ],
    [
        'product_name' => 'Raw Almonds 250g',
        'description' => 'Locally roasted raw almonds, 250g pack',
        'price_per_unit' => 300.00,
        'unit_type' => 'pack',
        'stock_quantity' => 25,
    ],
    [
        'product_name' => 'Herbal Tea Mix 100g',
        'description' => 'Soothing herbal tea blend, 100g',
        'price_per_unit' => 150.00,
        'unit_type' => 'pack',
        'stock_quantity' => 20,
    ],
];

foreach ($products as $p) {
    $exists = DB::table('products')->where('vendor_id', $vendorId)->where('product_name', $p['product_name'])->first();
    if ($exists) {
        echo "Product already exists: {$p['product_name']}\n";
        continue;
    }

    $product = Product::create([
        'vendor_id' => $vendorId,
        'category_id' => $categoryId,
        'product_name' => $p['product_name'],
        'description' => $p['description'],
        'price_per_unit' => $p['price_per_unit'],
        'unit_type' => $p['unit_type'],
        'product_image_url' => $productImagePath,
        'is_available' => true,
        'stock_quantity' => $p['stock_quantity'],
        'minimum_stock' => 5,
        'track_stock' => true,
        'allow_backorder' => false,
        'stock_notes' => null,
    ]);

    if ($p['stock_quantity'] > 0) {
        StockLog::create([
            'product_id' => $product->id,
            'vendor_id' => $vendorId,
            'previous_stock' => 0,
            'new_stock' => $p['stock_quantity'],
            'quantity_changed' => $p['stock_quantity'],
            'change_type' => 'restock',
            'notes' => 'Initial seed for vendor2',
        ]);
    }

    echo "Created product: {$p['product_name']} (stock: {$p['stock_quantity']})\n";
}

echo "Done. Vendor login: {$vendorEmail} / {$vendorPassword}\n";


