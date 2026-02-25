<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class MockDataSeeder extends Seeder
{
    public function run(): void
    {
        $profileImagePath = 'storage/maps/1769326582_6975c7f61ae02.jpg';
        $vendorBannerPath = 'vendor-banners/LxiIpAHjlcIjdMWpIon7Q29XpeXLAJisu30C27Cq.jpg';
        $vendorLogoPath = 'vendor-logos/wUwTyLfWjQ1BjUnC8JE0fV4CQZUUK1iPPpfcfyfi.png';
        $productImagePath = 'storage/maps/1769326582_6975c7f61ae02.jpg';

        $vendors = DB::table('vendors')->get();
        $products = DB::table('products')->get();

        if ($vendors->isEmpty() || $products->isEmpty()) {
            $this->command->warn('MockDataSeeder skipped: vendors or products missing.');
            return;
        }

        $customers = [];
        for ($i = 1; $i <= 10; $i++) {
            $email = "customer{$i}@demo.test";
            $userId = DB::table('users')->where('email', $email)->value('id');

            if (! $userId) {
                $userId = DB::table('users')->insertGetId([
                    'email' => $email,
                    'password' => Hash::make('password'),
                    'role' => 'customer',
                    'email_verified_at' => now(),
                    'profile_picture' => $profileImagePath,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            } else {
                DB::table('users')->where('id', $userId)->update([
                    'profile_picture' => $profileImagePath,
                    'updated_at' => now(),
                ]);
            }

            $firstName = 'Demo';
            $lastName = 'Customer ' . $i;
            $phone = '09' . str_pad((string) (100000000 + $i), 9, '0', STR_PAD_LEFT);

            $customer = DB::table('customers')->where('user_id', $userId)->first();
            if (! $customer) {
                $customerId = DB::table('customers')->insertGetId([
                    'user_id' => $userId,
                    'first_name' => $firstName,
                    'last_name' => $lastName,
                    'phone' => $phone,
                    'delivery_address' => "123 Test St, City {$i}",
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            } else {
                $customerId = $customer->id;
            }

            $customers[] = [
                'id' => $customerId,
                'name' => $firstName . ' ' . $lastName,
                'phone' => $phone,
                'address' => "123 Test St, City {$i}",
            ];

            $addressCount = DB::table('customer_addresses')->where('customer_id', $customerId)->count();
            if ($addressCount === 0) {
                DB::table('customer_addresses')->insert([
                    [
                        'customer_id' => $customerId,
                        'address_line' => "123 Test St, City {$i}",
                        'city' => "City {$i}",
                        'province' => 'Metro Test',
                        'postal_code' => '1000',
                        'recipient_name' => $firstName . ' ' . $lastName,
                        'recipient_phone' => $phone,
                        'is_default' => true,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ],
                    [
                        'customer_id' => $customerId,
                        'address_line' => "Unit {$i}B, Sample Ave",
                        'city' => "City {$i}",
                        'province' => 'Metro Test',
                        'postal_code' => '1000',
                        'recipient_name' => $firstName . ' ' . $lastName,
                        'recipient_phone' => $phone,
                        'is_default' => false,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ],
                ]);
            }
        }

        if (DB::table('market_fees')->count() === 0) {
            DB::table('market_fees')->insert([
                [
                    'fee_name' => 'Market Service Fee',
                    'fee_type' => 'percentage',
                    'fee_value' => 5.00,
                    'is_active' => true,
                    'effective_date' => now()->subMonths(1)->toDateString(),
                    'end_date' => null,
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
                [
                    'fee_name' => 'Daily Stall Fee',
                    'fee_type' => 'fixed',
                    'fee_value' => 50.00,
                    'is_active' => false,
                    'effective_date' => now()->subMonths(2)->toDateString(),
                    'end_date' => now()->subMonths(1)->toDateString(),
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
            ]);
        }

        foreach ($vendors as $vendor) {
            for ($d = 0; $d < 7; $d++) {
                $date = now()->subDays($d);
                DB::table('vendor_attendance')->insertOrIgnore([
                    'vendor_id' => $vendor->id,
                    'market_date' => $date->toDateString(),
                    'check_in_time' => $date->copy()->setTime(8, 0),
                    'check_out_time' => $date->copy()->setTime(17, 0),
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }

        $productsByVendor = $products->groupBy('vendor_id');
        $walkInSaleIds = [];

        foreach ($vendors as $vendor) {
            $vendorProducts = $productsByVendor->get($vendor->id, collect());
            if ($vendorProducts->isEmpty()) {
                continue;
            }

            for ($i = 0; $i < 2; $i++) {
                $product = $vendorProducts->random();
                $quantity = rand(1, 5);
                $unitPrice = (float) $product->price_per_unit;
                $saleDate = now()->subDays(rand(0, 6));

                $saleId = DB::table('walk_in_sales')->insertGetId([
                    'vendor_id' => $vendor->id,
                    'sale_date' => $saleDate->toDateString(),
                    'sale_time' => $saleDate->copy()->setTime(rand(9, 17), rand(0, 59)),
                    'product_id' => $product->id,
                    'product_name' => $product->product_name,
                    'quantity' => $quantity,
                    'unit_price' => $unitPrice,
                    'notes' => 'Walk-in sale for testing',
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

                $walkInSaleIds[] = [
                    'id' => $saleId,
                    'vendor_id' => $vendor->id,
                    'date' => $saleDate->toDateString(),
                    'gross' => $quantity * $unitPrice,
                ];
            }
        }

        $feePercent = (float) DB::table('market_fees')
            ->where('fee_type', 'percentage')
            ->where('is_active', true)
            ->value('fee_value') ?? 5.0;

        $orderStatuses = [
            'pending',
            'confirmed',
            'ready',
            'completed',
            'preparing',
            'awaiting_rider',
            'out_for_delivery',
            'delivered',
        ];
        $fulfillmentTypes = ['weekend_pickup', 'weekday_delivery', 'weekend_delivery'];

        $orderCount = 20;
        for ($i = 1; $i <= $orderCount; $i++) {
            $customer = $customers[array_rand($customers)];
            $orderDate = now()->subDays(rand(0, 14));
            $status = $orderStatuses[array_rand($orderStatuses)];
            $fulfillment = $fulfillmentTypes[array_rand($fulfillmentTypes)];
            $orderReference = null;
            for ($attempt = 0; $attempt < 5; $attempt++) {
                $candidate = 'ORD-' . now()->format('Ymd') . '-' . strtoupper(Str::random(6));
                if (! DB::table('orders')->where('order_reference', $candidate)->exists()) {
                    $orderReference = $candidate;
                    break;
                }
            }

            if (! $orderReference) {
                $this->command->warn('Skipping order: unable to generate unique reference.');
                continue;
            }

            $deliveryAddress = null;
            $pickupCode = null;

            if (in_array($fulfillment, ['weekday_delivery', 'weekend_delivery'], true)) {
                $deliveryAddress = $customer['address'];
            } else {
                $pickupCode = strtoupper(Str::random(6));
            }

            $orderId = DB::table('orders')->insertGetId([
                'order_reference' => $orderReference,
                'customer_id' => $customer['id'],
                'order_date' => $orderDate,
                'fulfillment_type' => $fulfillment,
                'order_status' => $status,
                'delivery_address' => $deliveryAddress,
                'pickup_code' => $pickupCode,
                'notes' => 'Mock order for testing video',
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            $itemsPerOrder = rand(1, 3);
            $vendorTotals = [];

            for ($j = 0; $j < $itemsPerOrder; $j++) {
                $product = $products->random();
                $quantity = rand(1, 4);
                $unitPrice = (float) $product->price_per_unit;
                $gross = $quantity * $unitPrice;

                DB::table('order_items')->insert([
                    'order_id' => $orderId,
                    'vendor_id' => $product->vendor_id,
                    'product_id' => $product->id,
                    'quantity' => $quantity,
                    'unit_price' => $unitPrice,
                    'item_status' => $status,
                    'vendor_notes' => 'Mock item',
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

                $previousStock = (int) $product->stock_quantity;
                $newStock = max(0, $previousStock - $quantity);

                DB::table('stock_logs')->insert([
                    'product_id' => $product->id,
                    'vendor_id' => $product->vendor_id,
                    'previous_stock' => $previousStock,
                    'new_stock' => $newStock,
                    'quantity_changed' => $quantity,
                    'change_type' => 'sale',
                    'notes' => 'Mock order sale',
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

                if (! isset($vendorTotals[$product->vendor_id])) {
                    $vendorTotals[$product->vendor_id] = 0.0;
                }
                $vendorTotals[$product->vendor_id] += $gross;
            }

            foreach ($vendorTotals as $vendorId => $grossAmount) {
                $feeAmount = round($grossAmount * ($feePercent / 100), 2);
                $netAmount = round($grossAmount - $feeAmount, 2);

                DB::table('vendor_transactions')->insert([
                    'vendor_id' => $vendorId,
                    'transaction_date' => $orderDate->toDateString(),
                    'transaction_type' => 'pre_order',
                    'order_id' => $orderId,
                    'sale_id' => null,
                    'gross_amount' => $grossAmount,
                    'fee_amount' => $feeAmount,
                    'net_amount' => $netAmount,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }

        foreach ($walkInSaleIds as $sale) {
            $feeAmount = round($sale['gross'] * ($feePercent / 100), 2);
            $netAmount = round($sale['gross'] - $feeAmount, 2);

            DB::table('vendor_transactions')->insert([
                'vendor_id' => $sale['vendor_id'],
                'transaction_date' => $sale['date'],
                'transaction_type' => 'walk_in',
                'order_id' => null,
                'sale_id' => $sale['id'],
                'gross_amount' => $sale['gross'],
                'fee_amount' => $feeAmount,
                'net_amount' => $netAmount,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        foreach ($products as $product) {
            $hasLog = DB::table('stock_logs')->where('product_id', $product->id)->exists();
            if ($hasLog) {
                continue;
            }

            DB::table('stock_logs')->insert([
                'product_id' => $product->id,
                'vendor_id' => $product->vendor_id,
                'previous_stock' => 0,
                'new_stock' => (int) $product->stock_quantity,
                'quantity_changed' => (int) $product->stock_quantity,
                'change_type' => 'restock',
                'notes' => 'Initial mock restock',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        // Vendors will use fallback placeholders when images are not uploaded

        if (DB::table('products')->whereNull('product_image_url')->count() > 0) {
            DB::table('products')->whereNull('product_image_url')->update(['product_image_url' => $productImagePath]);
        }

        $this->command->info('Mock customers and related data seeded successfully!');
    }
}
