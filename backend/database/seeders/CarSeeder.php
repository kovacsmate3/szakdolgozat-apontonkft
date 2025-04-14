<?php

namespace Database\Seeders;

use App\Models\Car;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class CarSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Car::create([
            'user_id' => 1,
            'car_type' => 'furgon',
            'license_plate' => 'SVC-645',
            'manufacturer' => 'Renault',
            'model' => 'Kangoo',
            'fuel_type' => 'dízel',
            'standard_consumption' => 5.3,
            'capacity' => 1461,
            'fuel_tank_capacity' => 54,
        ]);


        Car::create([
            'user_id' => 1,
            'car_type' => 'hatchback',
            'license_plate' => 'LVY-802',
            'manufacturer' => 'Renault',
            'model' => 'Clio',
            'fuel_type' => 'dízel',
            'standard_consumption' => 4.3,
            'capacity' => 1461,
            'fuel_tank_capacity' => 39,
        ]);
    }
}
