<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

// Create stalls if none exist
$stallCount = DB::table('stalls')->count();
if ($stallCount === 0) {
    $sectionId = DB::table('market_sections')->value('id');
    if (!$sectionId) {
        $sectionId = DB::table('market_sections')->insertGetId([
            'section_name' => 'General',
            'section_code' => 'GEN',
            'description' => 'General market area',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        echo "Created default market section (id={$sectionId})\n";
    }

    for ($i = 1; $i <= 4; $i++) {
        DB::table('stalls')->insert([
            'section_id' => $sectionId,
            'stall_number' => 'S-' . str_pad($i, 3, '0', STR_PAD_LEFT),
            'x1' => 100 + ($i * 50),
            'y1' => 100,
            'x2' => 140 + ($i * 50),
            'y2' => 140,
            'status' => 'available',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        echo "Created stall S-" . str_pad($i, 3, '0', STR_PAD_LEFT) . "\n";
    }
}

// Assign stalls to vendors
$vendors = DB::table('vendors')->get();
$stalls = DB::table('stalls')->where('status', 'available')->get();

$idx = 0;
foreach ($vendors as $vendor) {
    $existing = DB::table('stall_assignments')
        ->where('vendor_id', $vendor->id)
        ->whereNull('end_date')
        ->first();

    if (!$existing && isset($stalls[$idx])) {
        $stall = $stalls[$idx];
        DB::table('stall_assignments')->insert([
            'stall_id' => $stall->id,
            'vendor_id' => $vendor->id,
            'assigned_date' => now(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        DB::table('stalls')->where('id', $stall->id)->update(['status' => 'occupied']);
        echo "Assigned {$vendor->business_name} to stall {$stall->stall_number}\n";
        $idx++;
    }
}

// Create rent bills
$assignments = DB::table('vendors')
    ->join('stall_assignments', 'vendors.id', '=', 'stall_assignments.vendor_id')
    ->join('stalls', 'stall_assignments.stall_id', '=', 'stalls.id')
    ->whereNull('stall_assignments.end_date')
    ->select('vendors.id as vendor_id', 'vendors.business_name', 'stalls.id as stall_id', 'stalls.stall_number')
    ->get();

$periods = [
    'January 2026' => '2026-01-31',
    'February 2026' => '2026-02-28',
    'March 2026' => '2026-03-31',
];

foreach ($assignments as $a) {
    foreach ($periods as $period => $dueDate) {
        $exists = DB::table('stall_payments')
            ->where('vendor_id', $a->vendor_id)
            ->where('billing_period', $period)
            ->exists();

        if (!$exists) {
            DB::table('stall_payments')->insert([
                'vendor_id' => $a->vendor_id,
                'stall_id' => $a->stall_id,
                'amount_due' => 1500.00,
                'amount_paid' => 0,
                'due_date' => $dueDate,
                'status' => $dueDate < '2026-03-01' ? 'overdue' : 'unpaid',
                'billing_period' => $period,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
            echo "  Created bill for {$a->business_name}: {$period}\n";
        }
    }
}

echo "\nDone!\n";
