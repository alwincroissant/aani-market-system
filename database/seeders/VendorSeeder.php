<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class VendorSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $profileImagePath = 'storage/maps/1769326582_6975c7f61ae02.jpg';
        $vendorBannerPath = 'vendor-banners/LxiIpAHjlcIjdMWpIon7Q29XpeXLAJisu30C27Cq.jpg';
        $vendorLogoPath = 'vendor-logos/wUwTyLfWjQ1BjUnC8JE0fV4CQZUUK1iPPpfcfyfi.png';

        // Get or create the vendor user
        $vendorUser = User::firstOrCreate([
            'email' => 'vendor@test.com'
        ], [
            'email' => 'vendor@test.com',
            'password' => Hash::make('password'),
            'role' => 'vendor',
            'email_verified_at' => now(),
            'profile_picture' => $profileImagePath,
        ]);

        // Create vendor profile
        $existingVendor = DB::table('vendors')->where('user_id', $vendorUser->id)->first();
        
        if (!$existingVendor) {
            $vendorId = DB::table('vendors')->insertGetId([
                'user_id' => $vendorUser->id,
                'business_name' => 'Fresh Farm Produce',
                'owner_name' => 'John Farmer',
                'contact_phone' => '+1234567890',
                'contact_email' => 'vendor@test.com',
                'regional_sourcing_origin' => 'Local Region',
                'business_description' => 'Providing fresh organic produce from local farms',
                'logo_url' => null,
                'banner_url' => null,
                'weekend_pickup_enabled' => true,
                'weekday_delivery_enabled' => false,
                'weekend_delivery_enabled' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
            
            $this->command->info('Vendor profile created: Fresh Farm Produce');
        } else {
            $this->command->info('Vendor profile already exists');
        }
        
        $this->command->info('Login credentials: vendor@test.com / password');
    }
}
