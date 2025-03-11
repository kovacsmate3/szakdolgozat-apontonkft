<?php

namespace Database\Seeders;

use App\Models\Role;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $roles = [
            [
                'slug' => 'webdev',
                'title' => 'Weboldal fejlesztő',
                'description' => 'Weboldal fejlesztő: minden jogosultság, kivéve a szabadságkérelmek és túlórák jóváhagyása',
            ],
            [
                'slug' => 'admin',
                'title' => 'Adminisztrátor (cégvezető)',
                'description' => 'Admin: minden jogosultsággal rendelkezik',
            ],
            [
                'slug' => 'employee',
                'title' => 'Alkalmazott/Beosztott',
                'description' => 'Alkalmazott: korlátozott jogosultságok, leginkább csak saját adatainak módosítása',
            ],
        ];

        foreach ($roles as $role) {
            Role::create($role);
        }
    }
}
