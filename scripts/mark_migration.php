<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$db = $app->make('db');
$exists = $db->table('migrations')->where('migration', '2026_02_21_215210_add_stock_columns_to_products_table')->exists();
if ($exists) {
    echo "migration already recorded\n";
    exit;
}
$db->table('migrations')->insert([
    'migration' => '2026_02_21_215210_add_stock_columns_to_products_table',
    'batch' => 1,
]);
echo "inserted migration record\n";
