<?php
require_once 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\Product;

$products = Product::orderBy('id', 'DESC')->limit(10)->get(['id', 'product_name', 'product_image_url']);

echo "Products in DB:\n";
echo json_encode($products, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
