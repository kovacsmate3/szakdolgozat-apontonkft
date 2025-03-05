<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;

use App\Models\TravelPurposeDictionary;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            FuelPriceSeeder::class,
            LawCategorySeeder::class,
            LawSeeder::class,
            RoleSeeder::class,
            PermissionSeeder::class,
            RolePermissionSeeder::class,
            UserSeeder::class,
            CarSeeder::class,
            LocationSeeder::class,
            TravelPurposeDictionarySeeder::class,
            LocationPurposeSeeder::class,
            AddressSeeder::class,
            FuelExpenseSeeder::class,
            TripSeeder::class
        ]);
    }
}
