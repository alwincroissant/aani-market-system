<?php
require_once 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\Product;

// Get product with image
$productWithImage = Product::where('product_image_url', '!=', null)->first(['id', 'product_name', 'product_image_url', 'vendor_id']);

if ($productWithImage) {
    echo "Product with image found:\n";
    echo json_encode($productWithImage, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
    echo "\n\nTesting asset() path:\n";
    echo "asset('{$productWithImage->product_image_url}')\n";
    echo "asset() would generate: " . asset($productWithImage->product_image_url) . "\n";
    
    // Check if file exists
    $filePath = 'public/' . $productWithImage->product_image_url;
    echo "\nChecking file: $filePath\n";
    echo "File exists: " . (file_exists($filePath) ? "YES" : "NO") . "\n";
    
    // Also check the actual storage path
    echo "\nChecking storage path:\n";
    $storagePath = storage_path('app/public/' . str_replace('storage/', '', $productWithImage->product_image_url));
    echo "Storage path: $storagePath\n";
    echo "File exists at storage: " . (file_exists($storagePath) ? "YES" : "NO") . "\n";
} else {
    echo "No product with image found in database\n";
    echo "All products:\n";
    $all = Product::select(['id', 'product_name', 'product_image_url'])->limit(10)->get();
    echo json_encode($all, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
}
