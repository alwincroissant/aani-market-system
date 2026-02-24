<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            AdminUserSeeder::class,
            UserSeeder::class,
            MarketSectionSeeder::class,
            SampleDataSeeder::class,
            MockDataSeeder::class,
        ]);
    }
}
