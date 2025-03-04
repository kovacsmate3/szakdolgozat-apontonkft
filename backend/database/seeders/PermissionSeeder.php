<?php

namespace Database\Seeders;

use App\Models\Permission;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class PermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $permissions = [
            // 1. Felhasználók és jogosultságkezelés
            ['key' => 'create.user', 'module' => 'user-management', 'description' => 'Új felhasználó létrehozása'],
            ['key' => 'edit.user', 'module' => 'user-management', 'description' => 'Felhasználó adatainak módosítása'],
            ['key' => 'delete.user', 'module' => 'user-management', 'description' => 'Felhasználó törlése'],
            ['key' => 'view.user', 'module' => 'user-management', 'description' => 'Felhasználók listázása és megtekintése'],
            ['key' => 'edit.own.user', 'module' => 'user-management', 'description' => 'Saját profiladatok módosítása'],

            // 2. Szerepkörök és jogosultságok kezelése
            ['key' => 'create.role', 'module' => 'role-management', 'description' => 'Új szerepkör létrehozása'],
            ['key' => 'edit.role', 'module' => 'role-management', 'description' => 'Szerepkör módosítása'],
            ['key' => 'delete.role', 'module' => 'role-management', 'description' => 'Szerepkör törlése'],
            ['key' => 'view.role', 'module' => 'role-management', 'description' => 'Szerepkörök megtekintése'],
            ['key' => 'assign.role', 'module' => 'role-management', 'description' => 'Felhasználókhoz szerepkör hozzárendelése'],
            ['key' => 'create.permission', 'module' => 'permission-management', 'description' => 'Jogosultság létrehozása'],
            ['key' => 'edit.permission', 'module' => 'permission-management', 'description' => 'Jogosultság módosítása'],
            ['key' => 'delete.permission', 'module' => 'permission-management', 'description' => 'Jogosultság törlése'],
            ['key' => 'view.permission', 'module' => 'permission-management', 'description' => 'Jogosultságok listázása és megtekintése'],

            // 3. Projektek kezelése
            ['key' => 'create.project', 'module' => 'project-management', 'description' => 'Új projekt létrehozása'],
            ['key' => 'edit.project', 'module' => 'project-management', 'description' => 'Projekt módosítása'],
            ['key' => 'delete.project', 'module' => 'project-management', 'description' => 'Projekt törlése'],
            ['key' => 'view.project', 'module' => 'project-management', 'description' => 'Projektek megtekintése'],

            // 4. Utazások kezelése
            ['key' => 'create.trip', 'module' => 'trip-management', 'description' => 'Új utazás rögzítése'],
            ['key' => 'edit.trip', 'module' => 'trip-management', 'description' => 'Utazás módosítása'],
            ['key' => 'delete.trip', 'module' => 'trip-management', 'description' => 'Utazás törlése (nem engedélyezett)'],
            ['key' => 'view.trip', 'module' => 'trip-management', 'description' => 'Utazások megtekintése'],

            // 5. Tankolások kezelése
            ['key' => 'create.fuel_expense', 'module' => 'fuel-management', 'description' => 'Új tankolási adat rögzítése'],
            ['key' => 'edit.fuel_expense', 'module' => 'fuel-management', 'description' => 'Tankolási adat módosítása'],
            ['key' => 'delete.fuel_expense', 'module' => 'fuel-management', 'description' => 'Tankolási adat törlése (nem engedélyezett)'],
            ['key' => 'view.fuel_expense', 'module' => 'fuel-management', 'description' => 'Tankolási adatok megtekintése'],

            // 6. Autók kezelése
            ['key' => 'create.car', 'module' => 'car-management', 'description' => 'Új autó rögzítése'],
            ['key' => 'edit.car', 'module' => 'car-management', 'description' => 'Autó adatainak módosítása'],
            ['key' => 'delete.car', 'module' => 'car-management', 'description' => 'Autó törlése'],
            ['key' => 'view.car', 'module' => 'car-management', 'description' => 'Autók listázása és megtekintése'],

            // 7. Utazási célok kezelése
            ['key' => 'create.travel_purpose', 'module' => 'travel-purpose-management', 'description' => 'Új utazási cél felvétele'],
            ['key' => 'edit.travel_purpose', 'module' => 'travel-purpose-management', 'description' => 'Utazási cél módosítása'],
            ['key' => 'delete.travel_purpose', 'module' => 'travel-purpose-management', 'description' => 'Utazási cél törlése'],
            ['key' => 'view.travel_purpose', 'module' => 'travel-purpose-management', 'description' => 'Utazási célok megtekintése'],

            // 8. Feladatok kezelése
            ['key' => 'create.task', 'module' => 'task-management', 'description' => 'Új feladat rögzítése'],
            ['key' => 'edit.task', 'module' => 'task-management', 'description' => 'Feladat módosítása'],
            ['key' => 'delete.task', 'module' => 'task-management', 'description' => 'Feladat törlése'],
            ['key' => 'view.task', 'module' => 'task-management', 'description' => 'Feladatok megtekintése'],

            // 9. Naplóbejegyzések kezelése
            ['key' => 'create.journal_entry', 'module' => 'journal-management', 'description' => 'Naplóbejegyzés rögzítése'],
            ['key' => 'edit.journal_entry', 'module' => 'journal-management', 'description' => 'Naplóbejegyzés módosítása'],
            ['key' => 'delete.journal_entry', 'module' => 'journal-management', 'description' => 'Naplóbejegyzés törlése'],
            ['key' => 'view.journal_entry', 'module' => 'journal-management', 'description' => 'Naplóbejegyzések megtekintése'],

            // 10. Szabadságkérelmek kezelése
            ['key' => 'create.leave_request', 'module' => 'leave-management', 'description' => 'Új szabadságkérelem rögzítése'],
            ['key' => 'edit.leave_request', 'module' => 'leave-management', 'description' => 'Szabadságkérelem módosítása'],
            ['key' => 'delete.leave_request', 'module' => 'leave-management', 'description' => 'Szabadságkérelem törlése'],
            ['key' => 'view.leave_request', 'module' => 'leave-management', 'description' => 'Szabadságkérelmek megtekintése'],
            ['key' => 'approve.leave_request', 'module' => 'leave-management', 'description' => 'Szabadságkérelem jóváhagyása'],

            // 11. Túlóra bejelentések kezelése
            ['key' => 'create.overtime_request', 'module' => 'overtime-management', 'description' => 'Túlóra bejelentése'],
            ['key' => 'edit.overtime_request', 'module' => 'overtime-management', 'description' => 'Túlóra módosítása'],
            ['key' => 'delete.overtime_request', 'module' => 'overtime-management', 'description' => 'Túlóra törlése'],
            ['key' => 'view.overtime_request', 'module' => 'overtime-management', 'description' => 'Túlóra bejelentések megtekintése'],
            ['key' => 'approve.overtime_request', 'module' => 'overtime-management', 'description' => 'Túlóra jóváhagyása'],
        ];

        foreach ($permissions as $permission) {
            Permission::create($permission);
        }
    }
}
