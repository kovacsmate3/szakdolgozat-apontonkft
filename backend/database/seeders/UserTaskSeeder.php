<?php

namespace Database\Seeders;

use App\Models\Task;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class UserTaskSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $adminUser = User::where('username', 'adminuser')->first();
        $employeeUsers = User::whereHas('role', function ($query) {
            $query->where('slug', 'employee');
        })->get();

        $tasks = Task::all();

        $date = fake()->dateTimeBetween('-1 month', 'now');

        foreach ($tasks as $task) {
            if ($task->surveying_instrument === 'Laptop') {
                $task->users()->attach($adminUser->id, [
                    'assigned_at' => $date,
                    'role_on_task' => 'Koordinátor',
                ]);
            } else {
                $randomEmployee = $employeeUsers->random();
                $task->users()->attach($randomEmployee->id, [
                    'assigned_at' => $date,
                    'role_on_task' => "Terepi földmérnök",
                ]);
            }
        }
    }
}
