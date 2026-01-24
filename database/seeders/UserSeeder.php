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
        // Create a vendor user
        $vendorUser = User::firstOrCreate([
            'email' => 'vendor@test.com'
        ], [
            'name' => 'Test Vendor',
            'email' => 'vendor@test.com',
            'password' => Hash::make('password'),
            'role' => 'vendor',
            'email_verified_at' => now(),
        ]);

        $this->command->info('Vendor user created: vendor@test.com / password');
    }
}
