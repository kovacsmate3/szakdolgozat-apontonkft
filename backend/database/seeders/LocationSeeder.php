<?php

namespace Database\Seeders;

use App\Models\Location;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class LocationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $locations = [
            [
                'name' => 'A-Ponton Kft. székhely',
                'location_type' => 'site',
                'is_headquarter' => true,
            ],
            [
                'name' => 'MOL Töltőállomás',
                'location_type' => 'station',
                'is_headquarter' => false,
            ],
            [
                'name' => 'A-Ponton Kft. iroda',
                'location_type' => 'site',
                'is_headquarter' => false,
            ],
            [
                'name' => 'Épkar Zrt. - biatorbágyi bölcsőde',
                'location_type' => 'partner',
                'is_headquarter' => false,
            ],
            [
                'name' => 'SZÁM-SZIL Kft.',
                'location_type' => 'partner',
                'is_headquarter' => false,
            ],
            [
                'name' => 'Praktiker',
                'location_type' => 'shop',
                'is_headquarter' => false,
            ],
            [
                'name' => 'Rákospalota 1 Posta',
                'location_type' => 'other',
                'is_headquarter' => false,
            ],
            [
                'name' => 'Piri Dávid lakóhely',
                'location_type' => 'other',
                'is_headquarter' => false,
            ]
        ];

        foreach ($locations as $location) {
            Location::create($location);
        }
    }
}
