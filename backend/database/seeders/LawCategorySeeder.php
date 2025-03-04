<?php

namespace Database\Seeders;

use App\Models\LawCategory;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class LawCategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $categories = [
            [
                'name' => 'Földmérés',
                'description' => 'Földmérési és térképészeti jogszabályok.'
            ],
            [
                'name' => 'Ingatlan-nyilvántartás',
                'description' => 'Ingatlan-nyilvántartási jogszabályok.'
            ],
            [
                'name' => 'Építésügy',
                'description' => 'Építésügyi jogszabályok.'
            ],
            [
                'name' => 'Földügy',
                'description' => 'Földügyi jogszabályok.'
            ],
            [
                'name' => 'Eljárási díjak',
                'description' => 'Az ingatlan-nyilvántartási, telekalakítási és földmérési eljárások díjszabását tartalmazza, biztosítva a nyilvántartások fenntarthatóságát és átláthatóságát.'
            ],
            [
                'name' => 'További jogszabályok',
                'description' => 'Egyéb általános jogszabályok.'
            ],
        ];

        foreach ($categories as $category) {
            LawCategory::create($category);
        }
    }
}
