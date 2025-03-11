<?php

namespace Database\Seeders;

use App\Models\TravelPurposeDictionary;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class TravelPurposeDictionarySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $purposes = [
            [
                'travel_purpose' => 'Banki ügyintézés',
                'type'          => 'Üzleti',
                'note'          => '',
                'is_system'     => false,
            ],
            [
                'travel_purpose' => 'Családi program',
                'type'          => 'Magán',
                'note'          => '',
                'is_system'     => false,
            ],
            [
                'travel_purpose' => 'Együttműködési megbeszélés',
                'type'          => 'Üzleti',
                'note'          => '',
                'is_system'     => false,
            ],
            [
                'travel_purpose' => 'Értékesítési tárgyalás',
                'type'          => 'Üzleti',
                'note'          => '',
                'is_system'     => false,
            ],
            [
                'travel_purpose' => 'Geodéziai felmérés',
                'type'          => 'Üzleti',
                'note'          => '',
                'is_system'     => false,
            ],
            [
                'travel_purpose' => 'Irodai munka',
                'type'          => 'Üzleti',
                'note'          => 'Rendszerszintű, nem törölhető, nem módosítható',
                'is_system'     => true,
            ],
            [
                'travel_purpose' => 'Lakóhelyről történő bejárás munkahelyre/telephelyre',
                'type'          => 'Magán',
                'note'          => '',
                'is_system'     => false,
            ],
            [
                'travel_purpose' => 'Mozgásvizsgálat',
                'type'          => 'Üzleti',
                'note'          => '',
                'is_system'     => false,
            ],
            [
                'travel_purpose' => 'Szakmai konferencia',
                'type'          => 'Üzleti',
                'note'          => '',
                'is_system'     => false,
            ],
            [
                'travel_purpose' => 'Szakmai továbbképzés',
                'type'          => 'Üzleti',
                'note'          => '',
                'is_system'     => false,
            ],
            [
                'travel_purpose' => 'Szaktanácsadás',
                'type'          => 'Üzleti',
                'note'          => '',
                'is_system'     => false,
            ],
            [
                'travel_purpose' => 'Szállás',
                'type'          => 'Üzleti',
                'note'          => '',
                'is_system'     => false,
            ],
            [
                'travel_purpose' => 'Üzemanyagfeltöltés/Tankolás',
                'type'          => 'Üzleti',
                'note'          => 'Rendszerszintű, nem törölhető, nem módosítható',
                'is_system'     => true,
            ],
            [
                'travel_purpose' => 'Üzemorvosi vizsgálat',
                'type'          => 'Üzleti',
                'note'          => '',
                'is_system'     => false,
            ],
            [
                'travel_purpose' => 'Üzleti levél feladás',
                'type'          => 'Üzleti',
                'note'          => '',
                'is_system'     => false,
            ],
            [
                'travel_purpose' => 'Üzleti levél átvétel',
                'type'          => 'Üzleti',
                'note'          => '',
                'is_system'     => false,
            ],
            [
                'travel_purpose' => 'Üzleti tárgyalás',
                'type'          => 'Üzleti',
                'note'          => 'Alapértelmezett',
                'is_system'     => false,
            ],
            [
                'travel_purpose' => 'Üzleti ügyintézés',
                'type'          => 'Üzleti',
                'note'          => '',
                'is_system'     => false,
            ],
            [
                'travel_purpose' => 'Vásárlás',
                'type'          => 'Magán',
                'note'          => '',
                'is_system'     => false,
            ],
        ];

        foreach ($purposes as $purpose) {
            TravelPurposeDictionary::create($purpose);
        }
    }
}
