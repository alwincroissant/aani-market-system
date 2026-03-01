<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

echo "Stalls: " . DB::table('stalls')->count() . "\n";
echo "Vendors: " . DB::table('vendors')->count() . "\n";
echo "Assignments: " . DB::table('stall_assignments')->count() . "\n";

$vendors = DB::table('vendors')->get();
foreach ($vendors as $v) {
    echo "Vendor #{$v->id}: {$v->business_name}\n";
}

$stalls = DB::table('stalls')->get();
foreach ($stalls as $s) {
    echo "Stall #{$s->id}: {$s->stall_number} (status: {$s->status})\n";
}
