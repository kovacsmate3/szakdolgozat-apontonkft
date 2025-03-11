<?php

namespace Database\Seeders;

use App\Models\Permission;
use App\Models\Role;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class RolePermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Szerepek és jogosultságok lekérése
        $adminRole       = Role::where('slug', 'admin')->first();
        $webDevRole      = Role::where('slug', 'webdev')->first();
        $employeeRole    = Role::where('slug', 'employee')->first();

        $allPermissions  = Permission::all();

        // 1) ADMIN: MINDEN jogosultság
        if ($adminRole) {
            $adminRole->permissions()->sync(
                $allPermissions->pluck('id')->toArray()
            );
        }

        // 2) WEB DEVELOPER: Majdnem minden, kivéve „jóváhagyás”
        if ($webDevRole) {
            $webDevPermissions = $allPermissions->filter(function ($perm) {
                return ! in_array($perm->key, [
                    'approve.leave_request',
                    'approve.overtime_request',
                ]);
            });

            $webDevRole->permissions()->sync(
                $webDevPermissions->pluck('id')->toArray()
            );
        }

        // 3) EMPLOYEE: erősen korlátozott jogosultság
        if ($employeeRole) {
            $employeePermissions = Permission::whereIn('key', [
                'edit.own.user',
                'view.project',
                'view.trip',
                'create.trip',
                'edit.trip',
                'view.fuel_expense',
                'create.fuel_expense',
                'edit.fuel_expense',
                'view.car',
                'create.travel_purpose',
                'edit.travel_purpose',
                'delete.travel_purpose',
                'view.travel_purpose',
                'view.task',
                'create.journal_entry',
                'edit.journal_entry',
                'delete.journal_entry',
                'view.journal_entry',
                'create.leave_request',
                'edit.leave_request',
                'delete.leave_request',
                'view.leave_request',
                'create.overtime_request',
                'edit.overtime_request',
                'delete.overtime_request',
                'view.overtime_request',
            ])->get();

            $employeeRole->permissions()->sync(
                $employeePermissions->pluck('id')->toArray()
            );
        }
    }
}
