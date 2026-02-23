<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

$vendors = DB::table('vendors')->get();
$categories = DB::table('product_categories')->get();

echo "VENDORS:\n";
foreach ($vendors as $v) {
    echo "id={$v->id} user_id={$v->user_id} business_name={$v->business_name}\n";
}

echo "\nCATEGORIES:\n";
foreach ($categories as $c) {
    echo "id={$c->id} name={$c->category_name}\n";
}
