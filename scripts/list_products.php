<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

$rows = DB::table('products')->orderBy('id')->get();

if (! count($rows)) {
    echo "No products found.\n";
    exit;
}

foreach ($rows as $r) {
    echo "ID: {$r->id} | Vendor: {$r->vendor_id} | Name: {$r->product_name} | Stock: {$r->stock_quantity}\n";
}
