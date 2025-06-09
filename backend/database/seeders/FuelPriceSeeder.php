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
            // 2024 prices (complete year based on NAV data)
            [
                'period'  => '2024-12-01',
                'petrol'  => 608,
                'mixture' => 659,
                'diesel'  => 612,
                'lp_gas'  => 368,
            ],
            [
                'period'  => '2024-11-01',
                'petrol'  => 588,
                'mixture' => 639,
                'diesel'  => 593,
                'lp_gas'  => 363,
            ],
            [
                'period'  => '2024-10-01',
                'petrol'  => 603,
                'mixture' => 653,
                'diesel'  => 609,
                'lp_gas'  => 363,
            ],
            [
                'period'  => '2024-09-01',
                'petrol'  => 617,
                'mixture' => 668,
                'diesel'  => 625,
                'lp_gas'  => 352,
            ],
            [
                'period'  => '2024-08-01',
                'petrol'  => 620,
                'mixture' => 669,
                'diesel'  => 634,
                'lp_gas'  => 352,
            ],
            [
                'period'  => '2024-07-01',
                'petrol'  => 604,
                'mixture' => 653,
                'diesel'  => 610,
                'lp_gas'  => 351,
            ],
            [
                'period'  => '2024-06-01',
                'petrol'  => 640,
                'mixture' => 689,
                'diesel'  => 629,
                'lp_gas'  => 352,
            ],
            [
                'period'  => '2024-05-01',
                'petrol'  => 646,
                'mixture' => 695,
                'diesel'  => 653,
                'lp_gas'  => 345,
            ],
            [
                'period'  => '2024-04-01',
                'petrol'  => 617,
                'mixture' => 666,
                'diesel'  => 652,
                'lp_gas'  => 342,
            ],
            [
                'period'  => '2024-03-01',
                'petrol'  => 601,
                'mixture' => 650,
                'diesel'  => 638,
                'lp_gas'  => 332,
            ],
            [
                'period'  => '2024-02-01',
                'petrol'  => 566,
                'mixture' => 616,
                'diesel'  => 606,
                'lp_gas'  => 320,
            ],
            [
                'period'  => '2024-01-01',
                'petrol'  => 583,
                'mixture' => 632,
                'diesel'  => 614,
                'lp_gas'  => 317,
            ],
        ];

        foreach ($prices as $price) {
            FuelPrice::create($price);
        }
    }
}
