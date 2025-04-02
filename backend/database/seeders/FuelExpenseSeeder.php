<?php

namespace Database\Seeders;

use App\Models\FuelExpense;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class FuelExpenseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        FuelExpense::create([
            'car_id'       => 1,
            'user_id'      => 1,
            'location_id'  => 2,
            'expense_date' => '2024-12-03 18:09:00',
            'amount'       => 28111,
            'currency'     => 'HUF',
            'fuel_quantity'=> 44.48,
            'odometer'     => 87928,
        ]);

        FuelExpense::create([
            'car_id'       => 1,
            'user_id'      => 1,
            'location_id'  => 2,
            'expense_date' => '2024-12-13 16:55:00',
            'amount'       => 30347,
            'currency'     => 'HUF',
            'fuel_quantity'=> 49.12,
            'odometer'     => 88725,
        ]);
    }
}
