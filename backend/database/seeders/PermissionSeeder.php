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
            // 1. Felhasználók kezelése
            ['key' => 'create.user', 'module' => 'user-management', 'description' => 'Új felhasználó létrehozása'],
            ['key' => 'edit.user', 'module' => 'user-management', 'description' => 'Felhasználó adatainak módosítása'],
            ['key' => 'delete.user', 'module' => 'user-management', 'description' => 'Felhasználó törlése'],
            ['key' => 'view.user', 'module' => 'user-management', 'description' => 'Felhasználók listázása és megtekintése'],

            // 2. Szerepkörök kezelése
            ['key' => 'create.role', 'module' => 'role-management', 'description' => 'Új szerepkör létrehozása'],
            ['key' => 'edit.role', 'module' => 'role-management', 'description' => 'Szerepkör módosítása'],
            ['key' => 'delete.role', 'module' => 'role-management', 'description' => 'Szerepkör törlése'],
            ['key' => 'view.role', 'module' => 'role-management', 'description' => 'Szerepkörök megtekintése'],
            ['key' => 'assign.role', 'module' => 'role-management', 'description' => 'Felhasználókhoz szerepkör hozzárendelése'],
            ['key' => 'assign.permission_to_role', 'module' => 'role-management', 'description' => 'Jogosultságok hozzárendelése szerepkörökhöz'],
            ['key' => 'revoke.permission_from_role', 'module' => 'role-management', 'description' => 'Jogosultságok visszavonása szerepkörtől'],

            // 3. Jogosultságok kezelése
            ['key' => 'create.permission', 'module' => 'permission-management', 'description' => 'Jogosultság létrehozása'],
            ['key' => 'edit.permission', 'module' => 'permission-management', 'description' => 'Jogosultság módosítása'],
            ['key' => 'delete.permission', 'module' => 'permission-management', 'description' => 'Jogosultság törlése'],
            ['key' => 'view.permission', 'module' => 'permission-management', 'description' => 'Jogosultságok listázása és megtekintése'],

            // 4. Projektek kezelése
            ['key' => 'create.project', 'module' => 'project-management', 'description' => 'Új projekt létrehozása'],
            ['key' => 'edit.project', 'module' => 'project-management', 'description' => 'Projekt módosítása'],
            ['key' => 'delete.project', 'module' => 'project-management', 'description' => 'Projekt törlése'],
            ['key' => 'view.project', 'module' => 'project-management', 'description' => 'Projektek megtekintése'],
            ['key' => 'assign.task_to_project', 'module' => 'project-management', 'description' => 'Feladatok hozzárendelése projektekhez'],

            // 5. Utazások kezelése
            ['key' => 'create.trip', 'module' => 'trip-management', 'description' => 'Új utazás rögzítése'],
            ['key' => 'edit.trip', 'module' => 'trip-management', 'description' => 'Utazás módosítása'],
            ['key' => 'delete.trip', 'module' => 'trip-management', 'description' => 'Utazás törlése'],
            ['key' => 'view.trip', 'module' => 'trip-management', 'description' => 'Utazások megtekintése'],
            ['key' => 'calculate.trip_cost', 'module' => 'trip-management', 'description' => 'Utazási költség kiszámítása'],
            ['key' => 'view.trip_map', 'module' => 'trip-management', 'description' => 'Utazási útvonal térképen való megtekintése'],
            ['key' => 'export.trip.gps_data', 'module' => 'trip-management', 'description' => 'GPS-útvonaladatok exportálása'],
            ['key' => 'analyze.trip.gps_data', 'module' => 'trip-management', 'description' => 'GPS-útvonalak elemzése és útvonalpontosság értékelése'],
            ['key' => 'generate.trip_report', 'module' => 'trip-management', 'description' => 'Útnyilvántartási jelentés generálása'],

            // 6. Tankolások kezelése
            ['key' => 'create.fuel_expense', 'module' => 'fuel-management', 'description' => 'Új tankolási adat rögzítése'],
            ['key' => 'edit.fuel_expense', 'module' => 'fuel-management', 'description' => 'Tankolási adat módosítása'],
            ['key' => 'delete.fuel_expense', 'module' => 'fuel-management', 'description' => 'Tankolási adat törlése (nem engedélyezett)'],
            ['key' => 'view.fuel_expense', 'module' => 'fuel-management', 'description' => 'Tankolási adatok megtekintése'],
            ['key' => 'calculate.fuel_consumption', 'module' => 'fuel-management', 'description' => 'Üzemanyag-fogyasztás kiszámítása'],

            // 7. Autók kezelése
            ['key' => 'create.car', 'module' => 'car-management', 'description' => 'Új autó rögzítése'],
            ['key' => 'edit.car', 'module' => 'car-management', 'description' => 'Autó adatainak módosítása'],
            ['key' => 'delete.car', 'module' => 'car-management', 'description' => 'Autó törlése'],
            ['key' => 'view.car', 'module' => 'car-management', 'description' => 'Autók listázása és megtekintése'],
            ['key' => 'assign.car', 'module' => 'car-management', 'description' => 'Autók hozzárendelése felhasználókhoz'],

            // 8. Utazási célok kezelése
            ['key' => 'create.travel_purpose_dictionary', 'module' => 'travel-purpose-management', 'description' => 'Új utazási cél felvétele'],
            ['key' => 'edit.travel_purpose_dictionary', 'module' => 'travel-purpose-management', 'description' => 'Utazási cél módosítása'],
            ['key' => 'delete.travel_purpose_dictionary', 'module' => 'travel-purpose-management', 'description' => 'Utazási cél törlése'],
            ['key' => 'view.travel_purpose_dictionary', 'module' => 'travel-purpose-management', 'description' => 'Utazási célok megtekintése'],

            // 9. Feladatok kezelése
            ['key' => 'create.task', 'module' => 'task-management', 'description' => 'Új feladat rögzítése'],
            ['key' => 'edit.task', 'module' => 'task-management', 'description' => 'Feladat módosítása'],
            ['key' => 'delete.task', 'module' => 'task-management', 'description' => 'Feladat törlése'],
            ['key' => 'view.task', 'module' => 'task-management', 'description' => 'Feladatok megtekintése'],
            ['key' => 'assign.task', 'module' => 'task-management', 'description' => 'Felhasználók hozzárendelése feladatokhoz'],
            ['key' => 'unassign.task', 'module' => 'task-management', 'description' => 'Felhasználók eltávolítása feladatokról'],
            ['key' => 'change.role_on_task', 'module' => 'task-management', 'description' => 'Felhasználó feladaton betöltött szerepének módosítása'],
            ['key' => 'change_status.task', 'module' => 'task-management', 'description' => 'Feladat állapotának módosítása'],
            ['key' => 'manage.task_hierarchy', 'module' => 'task-management', 'description' => 'Feladatok hierarchikus struktúrájának kezelése'],

            // 10. Naplóbejegyzések kezelése
            ['key' => 'create.journal_entry', 'module' => 'journal-management', 'description' => 'Naplóbejegyzés rögzítése'],
            ['key' => 'edit.journal_entry', 'module' => 'journal-management', 'description' => 'Naplóbejegyzés módosítása'],
            ['key' => 'delete.journal_entry', 'module' => 'journal-management', 'description' => 'Naplóbejegyzés törlése'],
            ['key' => 'view.journal_entry', 'module' => 'journal-management', 'description' => 'Naplóbejegyzések megtekintése'],
            ['key' => 'export.journal_entry', 'module' => 'journal-management', 'description' => 'Naplóbejegyzések exportálása'],
            ['key' => 'comment.journal_entry', 'module' => 'journal-management', 'description' => 'Megjegyzés hozzáadása egy naplóbejegyzéshez'],

            // 11. Szabadságkérelmek kezelése
            ['key' => 'create.leave_request', 'module' => 'leave-management', 'description' => 'Új szabadságkérelem rögzítése'],
            ['key' => 'edit.leave_request', 'module' => 'leave-management', 'description' => 'Szabadságkérelem módosítása'],
            ['key' => 'delete.leave_request', 'module' => 'leave-management', 'description' => 'Szabadságkérelem törlése'],
            ['key' => 'view.leave_request', 'module' => 'leave-management', 'description' => 'Szabadságkérelmek megtekintése'],
            ['key' => 'approve.leave_request', 'module' => 'leave-management', 'description' => 'Szabadságkérelem jóváhagyása'],
            ['key' => 'reject.leave_request', 'module' => 'leave-management', 'description' => 'Szabadságkérelem elutasítása'],

            // 12. Túlóra bejelentések kezelése
            ['key' => 'create.overtime_request', 'module' => 'overtime-management', 'description' => 'Túlóra bejelentése'],
            ['key' => 'edit.overtime_request', 'module' => 'overtime-management', 'description' => 'Túlóra módosítása'],
            ['key' => 'delete.overtime_request', 'module' => 'overtime-management', 'description' => 'Túlóra törlése'],
            ['key' => 'view.overtime_request', 'module' => 'overtime-management', 'description' => 'Túlóra bejelentések megtekintése'],
            ['key' => 'approve.overtime_request', 'module' => 'overtime-management', 'description' => 'Túlóra jóváhagyása'],
            ['key' => 'reject.overtime_request', 'module' => 'overtime-management', 'description' => 'Túlóra kérelem elutasítása'],

            // 13. Helyszínek kezelése
            ['key' => 'create.location', 'module' => 'location-management', 'description' => 'Új helyszín létrehozása'],
            ['key' => 'edit.location', 'module' => 'location-management', 'description' => 'Helyszín módosítása'],
            ['key' => 'delete.location', 'module' => 'location-management', 'description' => 'Helyszín törlése'],
            ['key' => 'view.location', 'module' => 'location-management', 'description' => 'Helyszínek megtekintése'],
            ['key' => 'assign.travel_purpose_to_location', 'module' => 'location-management', 'description' => 'Utazási célok hozzárendelése helyszínekhez'],
            ['key' => 'view.gps_data', 'module' => 'location-management', 'description' => 'GPS adatok megtekintése'],

            // 14. Címek kezelése
            ['key' => 'create.address', 'module' => 'address-management', 'description' => 'Új címadat létrehozása'],
            ['key' => 'edit.address', 'module' => 'address-management', 'description' => 'Címadat módosítása'],
            ['key' => 'delete.address', 'module' => 'address-management', 'description' => 'Címadat törlése'],
            ['key' => 'view.address', 'module' => 'address-management', 'description' => 'Címadatok megtekintése'],
            ['key' => 'assign.location_to_address', 'module' => 'address-management', 'description' => 'Foglalkoztatási helyszín hozzárendelési címhez'],

            // 15. Jogszabály kategóriák kezelése
            ['key' => 'create.law_category', 'module' => 'law-category-management', 'description' => 'Új jogszabály kategória létrehozása'],
            ['key' => 'edit.law_category', 'module' => 'law-category-management', 'description' => 'Jogszabály kategória módosítása'],
            ['key' => 'delete.law_category', 'module' => 'law-category-management', 'description' => 'Jogszabály kategória törlése'],
            ['key' => 'view.law_category', 'module' => 'law-category-management', 'description' => 'Jogszabály kategóriák megtekintése'],

            // 16. Jogszabályok kezelése
            ['key' => 'create.law', 'module' => 'law-management', 'description' => 'Új jogszabály létrehozása'],
            ['key' => 'edit.law', 'module' => 'law-management', 'description' => 'Jogszabály módosítása'],
            ['key' => 'delete.law', 'module' => 'law-management', 'description' => 'Jogszabály törlése'],
            ['key' => 'view.law', 'module' => 'law-management', 'description' => 'Jogszabályok megtekintése'],

            // 17. Üzemanyagárak kezelése
            ['key' => 'create.fuel_price', 'module' => 'fuel-price-management', 'description' => 'Új üzemanyagár rögzítése'],
            ['key' => 'edit.fuel_price', 'module' => 'fuel-price-management', 'description' => 'Üzemanyagár módosítása'],
            ['key' => 'delete.fuel_price', 'module' => 'fuel-price-management', 'description' => 'Üzemanyagár törlése'],
            ['key' => 'view.fuel_price', 'module' => 'fuel-price-management', 'description' => 'Üzemanyagárak megtekintése'],

            // 18. Exportálás
            ['key' => 'export.trip_report', 'module' => 'export-management', 'description' => 'Útnyilvántartási adatok exportálása különböző formátumokban (XLS, DOC)'],
            ['key' => 'export.trip.doc', 'module' => 'export-management', 'description' => 'Útnyilvántartás exportálása DOC formátumban'],
            ['key' => 'export.trip.xls', 'module' => 'export-management', 'description' => 'Útnyilvántartás exportálása XLS formátumban'],
        ];

        foreach ($permissions as $permission) {
            Permission::create($permission);
        }
    }
}
