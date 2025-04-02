<?php

namespace Database\Seeders;

use App\Models\FuelPrice;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class FuelPriceSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $prices = [
            [
                'period'  => '2025-04-01',
                'petrol'  => 624,
                'mixture' => 675,
                'diesel'  => 638,
                'lp_gas'  => 384,
            ],
            [
                'period'  => '2025-03-01',
                'petrol'  => 638,
                'mixture' => 669,
                'diesel'  => 649,
                'lp_gas'  => 389,
            ],
            [
                'period'  => '2025-02-01',
                'petrol'  => 628,
                'mixture' => 678,
                'diesel'  => 639,
                'lp_gas'  => 379,
            ],
            [
                'period'  => '2025-01-01',
                'petrol'  => 629,
                'mixture' => 679,
                'diesel'  => 640,
                'lp_gas'  => 380,
            ],
        ];

        foreach ($prices as $price) {
            FuelPrice::create($price);
        }
    }
}
