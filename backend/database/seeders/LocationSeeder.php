<?php

namespace Database\Seeders;

use App\Models\Location;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class LocationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $adminUser = User::whereHas('role', function ($query) {
            $query->where('slug', 'admin');
        })->first();

        // Nem admin felhasználók az egyéb típusokhoz
        $regularUsers = User::whereHas('role', function ($query) {
            $query->where('slug', '<>', 'admin');
        })->take(3)->get()->keyBy(function () {
            return rand(0, 2);
        });

        $locations = [
            [
                'name' => 'A-Ponton Kft. székhely',
                'location_type' => 'telephely',
                'is_headquarter' => true,
                'user_id' => $adminUser->id,
            ],
            [
                'name' => 'MOL Töltőállomás',
                'location_type' => 'töltőállomás',
                'is_headquarter' => false,
            ],
            [
                'name' => 'A-Ponton Kft. iroda',
                'location_type' => 'telephely',
                'is_headquarter' => false,
                'user_id' =>  $adminUser->id,
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
                'location_type' => 'bolt',
                'is_headquarter' => false,
            ],
            [
                'name' => 'Rákospalota 1 Posta',
                'location_type' => 'egyéb',
                'is_headquarter' => false,
            ],
            [
                'name' => 'Piri Dávid lakóhely',
                'location_type' => 'egyéb',
                'is_headquarter' => false,
            ],
            [
                'name' => 'Öcsémék otthona',
                'location_type' => 'egyéb',
                'is_headquarter' => false,
            ]
        ];

        foreach ($locations as $locationData) {
            // Ha nem telephely, random felhasználóhoz rendeljük
            if ($locationData['location_type'] !== 'telephely') {
                $locationData['user_id'] = $regularUsers->random()->id;
            }

            Location::create($locationData);
        }
    }
}
