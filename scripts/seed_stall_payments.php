<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

$vendors = DB::table('vendors')
    ->join('stall_assignments', 'vendors.id', '=', 'stall_assignments.vendor_id')
    ->join('stalls', 'stall_assignments.stall_id', '=', 'stalls.id')
    ->whereNull('stall_assignments.end_date')
    ->select('vendors.id as vendor_id', 'vendors.business_name', 'stalls.id as stall_id', 'stalls.stall_number')
    ->get();

echo 'Vendors with stalls: ' . count($vendors) . PHP_EOL;

foreach ($vendors as $v) {
    echo "  Vendor #{$v->vendor_id}: {$v->business_name} -> Stall {$v->stall_number}" . PHP_EOL;

    $periods = [
        'January 2026' => '2026-01-31',
        'February 2026' => '2026-02-28',
        'March 2026' => '2026-03-31',
    ];

    foreach ($periods as $period => $dueDate) {
        $exists = DB::table('stall_payments')
            ->where('vendor_id', $v->vendor_id)
            ->where('billing_period', $period)
            ->exists();

        if (!$exists) {
            DB::table('stall_payments')->insert([
                'vendor_id' => $v->vendor_id,
                'stall_id' => $v->stall_id,
                'amount_due' => 1500.00,
                'amount_paid' => 0,
                'due_date' => $dueDate,
                'status' => $dueDate < '2026-03-01' ? 'overdue' : 'unpaid',
                'billing_period' => $period,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
            echo "    Created bill: {$period} (due: {$dueDate})" . PHP_EOL;
        } else {
            echo "    Bill already exists: {$period}" . PHP_EOL;
        }
    }
}

echo 'Done.' . PHP_EOL;
