<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class MarketSectionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $sections = [
            [
                'section_name' => 'Vegetables',
                'section_code' => 'VEG',
                'description' => 'Fresh vegetables and leafy greens',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'section_name' => 'Plant Market',
                'section_code' => 'PLT',
                'description' => 'Plants, flowers and gardening supplies',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'section_name' => 'Meat and Fish',
                'section_code' => 'MF',
                'description' => 'Fresh meat, poultry and seafood',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'section_name' => 'Food Section',
                'section_code' => 'FD',
                'description' => 'Prepared foods and beverages',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];

        foreach ($sections as $section) {
            $existing = DB::table('market_sections')->where('section_code', $section['section_code'])->first();
            
            if (!$existing) {
                DB::table('market_sections')->insert($section);
                $this->command->info('Section created: ' . $section['section_name']);
            } else {
                $this->command->info('Section already exists: ' . $section['section_name']);
            }
        }
    }
}
