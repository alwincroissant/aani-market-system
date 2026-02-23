<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Product;
use App\Models\StockLog;
use Illuminate\Support\Facades\DB;

// Find vendor (by email) or use first vendor
$vendor = DB::table('vendors')->first();
if (! $vendor) {
    echo "No vendors found. Please create a vendor first.\n";
    exit(1);
}

// Ensure at least one product category exists
$category = DB::table('product_categories')->first();
if (! $category) {
    $categoryId = DB::table('product_categories')->insertGetId([
        'category_name' => 'General',
        'color_code' => '#CCCCCC',
        'created_at' => now(),
        'updated_at' => now(),
    ]);
    echo "Created default category id={$categoryId}\n";
} else {
    $categoryId = $category->id;
}

$products = [
    [
        'product_name' => 'Organic Tomatoes',
        'description' => 'Fresh organic tomatoes - per kg',
        'price_per_unit' => 120.00,
        'unit_type' => 'kg',
        'stock_quantity' => 50,
    ],
    [
        'product_name' => 'Free-range Eggs (dozen)',
        'description' => 'A dozen free-range eggs',
        'price_per_unit' => 180.00,
        'unit_type' => 'dozen',
        'stock_quantity' => 30,
    ],
    [
        'product_name' => 'Local Honey 500g',
        'description' => 'Pure local honey 500 grams',
        'price_per_unit' => 250.00,
        'unit_type' => 'pack',
        'stock_quantity' => 15,
    ],
];

foreach ($products as $p) {
    $exists = DB::table('products')->where('vendor_id', $vendor->id)->where('product_name', $p['product_name'])->first();
    if ($exists) {
        echo "Product already exists: {$p['product_name']}\n";
        continue;
    }

    $product = Product::create([
        'vendor_id' => $vendor->id,
        'category_id' => $categoryId,
        'product_name' => $p['product_name'],
        'description' => $p['description'],
        'price_per_unit' => $p['price_per_unit'],
        'unit_type' => $p['unit_type'],
        'product_image_url' => null,
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
            'vendor_id' => $vendor->id,
            'previous_stock' => 0,
            'new_stock' => $p['stock_quantity'],
            'quantity_changed' => $p['stock_quantity'],
            'change_type' => 'restock',
            'notes' => 'Initial seed',
        ]);
    }

    echo "Created product: {$p['product_name']} (stock: {$p['stock_quantity']})\n";
}
