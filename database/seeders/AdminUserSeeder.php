<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $profileImagePath = 'storage/maps/1769326582_6975c7f61ae02.jpg';

        User::updateOrCreate(
            ['email' => 'admin@aani.com'],
            [
                'email' => 'admin@aani.com',
                'password' => Hash::make('admin123'),
                'role' => 'administrator',
                'is_active' => true,
                'email_verified_at' => now(),
                'profile_picture' => $profileImagePath,
            ]
        );

        $this->command->info('Admin user created successfully!');
        $this->command->info('Email: admin@aani.com');
        $this->command->info('Password: admin123');
    }
}
