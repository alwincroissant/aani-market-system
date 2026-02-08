<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create administrator user
        $adminUser = User::firstOrCreate([
            'email' => 'admin@aani.com'
        ], [
            'email' => 'admin@aani.com',
            'password' => Hash::make('admin123'),
            'role' => 'administrator',
            'email_verified_at' => now(),
        ]);

        // Create pickup manager user
        $pickupManagerUser = User::firstOrCreate([
            'email' => 'pickup@aani.com'
        ], [
            'email' => 'pickup@aani.com',
            'password' => Hash::make('pickup123'),
            'role' => 'pickup_manager',
            'email_verified_at' => now(),
        ]);

        // Create vendor user
        $vendorUser = User::firstOrCreate([
            'email' => 'vendor@test.com'
        ], [
            'email' => 'vendor@test.com',
            'password' => Hash::make('password'),
            'role' => 'vendor',
            'email_verified_at' => now(),
        ]);

        // Create customer user
        $customerUser = User::firstOrCreate([
            'email' => 'customer@test.com'
        ], [
            'email' => 'customer@test.com',
            'password' => Hash::make('password'),
            'role' => 'customer',
            'email_verified_at' => now(),
        ]);

        $this->command->info('Sample users created:');
        $this->command->info('Admin: admin@aani.com / admin123');
        $this->command->info('Pickup Manager: pickup@aani.com / pickup123');
        $this->command->info('Vendor: vendor@test.com / password');
        $this->command->info('Customer: customer@test.com / password');
    }
}
