<?php

namespace Database\Seeders;

use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $adminRole = Role::where('slug', 'admin')->first();
        $webDeveloperRole = Role::where('slug', 'webdev')->first();

        // Admin felhasználó létrehozása
        User::create([
            'username' => 'adminuser',
            'firstname' => 'Sándor',
            'lastname' => 'Kovács',
            'birthdate' => '1971-05-08',
            'phonenumber' => '+36209270324',
            'email' => 'apontonks@gmail.com',
            'password' => Hash::make('password'),
            'password_changed_at' => now(),
            'role_id' => $adminRole ? $adminRole->id : null,
        ]);

        // Web fejlesztő létrehozása
        User::create([
            'username' => 'webdevuser',
            'firstname' => 'Máté',
            'lastname' => 'Kovács',
            'birthdate' => '2003-04-25',
            'phonenumber' => '+36204789494',
            'email' => 'kovmate3@gmail.com',
            'password' => Hash::make('password'),
            'password_changed_at' => now(),
            'role_id' => $webDeveloperRole ? $webDeveloperRole->id : null,
        ]);

        User::factory()->count(5)->create([
            'role_id' => Role::where('slug', 'employee')->first()->id,
        ]);
    }
}
