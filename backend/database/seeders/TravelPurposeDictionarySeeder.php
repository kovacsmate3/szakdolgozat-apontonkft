<?php

namespace Database\Seeders;

use App\Models\TravelPurposeDictionary;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class TravelPurposeDictionarySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Keresünk egy admin felhasználót, vagy ha nincs, akkor bármelyiket
        $adminUser = User::whereHas('role', function ($query) {
            $query->where('slug', 'admin');
        })->first();

        // Ha nincs admin, akkor vesszük az első felhasználót
        $userId = $adminUser ? $adminUser->id : (User::first() ? User::first()->id : null);

        $purposes = [
            [
                'travel_purpose' => 'Banki ügyintézés',
                'type'          => 'Üzleti',
                'note'          => '',
                'is_system'     => false,
                'user_id'       => $userId,
            ],
            [
                'travel_purpose' => 'Családi program',
                'type'          => 'Magán',
                'note'          => '',
                'is_system'     => false,
                'user_id'       => $userId,
            ],
            [
                'travel_purpose' => 'Együttműködési megbeszélés',
                'type'          => 'Üzleti',
                'note'          => '',
                'is_system'     => false,
                'user_id'       => $userId,
            ],
            [
                'travel_purpose' => 'Értékesítési tárgyalás',
                'type'          => 'Üzleti',
                'note'          => '',
                'is_system'     => false,
                'user_id'       => $userId,
            ],
            [
                'travel_purpose' => 'Geodéziai felmérés',
                'type'          => 'Üzleti',
                'note'          => '',
                'is_system'     => false,
                'user_id'       => $userId,
            ],
            [
                'travel_purpose' => 'Irodai munka',
                'type'          => 'Üzleti',
                'note'          => 'Rendszerszintű, nem törölhető, nem módosítható',
                'is_system'     => true,
                'user_id'       => $userId,
            ],
            [
                'travel_purpose' => 'Lakóhelyről történő bejárás munkahelyre/telephelyre',
                'type'          => 'Magán',
                'note'          => '',
                'is_system'     => false,
                'user_id'       => $userId,
            ],
            [
                'travel_purpose' => 'Mozgásvizsgálat',
                'type'          => 'Üzleti',
                'note'          => '',
                'is_system'     => false,
                'user_id'       => $userId,
            ],
            [
                'travel_purpose' => 'Szakmai konferencia',
                'type'          => 'Üzleti',
                'note'          => '',
                'is_system'     => false,
                'user_id'       => $userId,
            ],
            [
                'travel_purpose' => 'Szakmai továbbképzés',
                'type'          => 'Üzleti',
                'note'          => '',
                'is_system'     => false,
                'user_id'       => $userId,
            ],
            [
                'travel_purpose' => 'Szaktanácsadás',
                'type'          => 'Üzleti',
                'note'          => '',
                'is_system'     => false,
                'user_id'       => $userId,
            ],
            [
                'travel_purpose' => 'Szállás',
                'type'          => 'Üzleti',
                'note'          => '',
                'is_system'     => false,
                'user_id'       => $userId,
            ],
            [
                'travel_purpose' => 'Üzemanyagfeltöltés/Tankolás',
                'type'          => 'Üzleti',
                'note'          => 'Rendszerszintű, nem törölhető, nem módosítható',
                'is_system'     => true,
                'user_id'       => $userId,
            ],
            [
                'travel_purpose' => 'Üzemorvosi vizsgálat',
                'type'          => 'Üzleti',
                'note'          => '',
                'is_system'     => false,
                'user_id'       => $userId,
            ],
            [
                'travel_purpose' => 'Üzleti levél feladás',
                'type'          => 'Üzleti',
                'note'          => '',
                'is_system'     => false,
                'user_id'       => $userId,
            ],
            [
                'travel_purpose' => 'Üzleti levél átvétel',
                'type'          => 'Üzleti',
                'note'          => '',
                'is_system'     => false,
                'user_id'       => $userId,
            ],
            [
                'travel_purpose' => 'Üzleti tárgyalás',
                'type'          => 'Üzleti',
                'note'          => 'Alapértelmezett',
                'is_system'     => false,
                'user_id'       => $userId,
            ],
            [
                'travel_purpose' => 'Üzleti ügyintézés',
                'type'          => 'Üzleti',
                'note'          => '',
                'is_system'     => false,
                'user_id'       => $userId,
            ],
            [
                'travel_purpose' => 'Vásárlás',
                'type'          => 'Magán',
                'note'          => '',
                'is_system'     => false,
                'user_id'       => $userId,
            ],
        ];

        foreach ($purposes as $purpose) {
            TravelPurposeDictionary::create($purpose);
        }
    }
}
