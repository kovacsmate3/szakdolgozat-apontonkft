<?php

namespace Database\Seeders;

use App\Models\FuelExpense;
use App\Models\Trip;
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
            'car_id'       => 2,
            'user_id'      => 1,
            'location_id'  => 2,
            'expense_date' => '2024-06-05 13:11:07',
            'amount'       => 28111,
            'currency'     => 'HUF',
            'fuel_quantity' => 44.48,
            'odometer'     => 81913,
            'trip_id'       => null,
        ]);

        FuelExpense::create([
            'car_id'       => 1,
            'user_id'      => 2,
            'location_id'  => 2,
            'expense_date' => '2024-03-03 08:39:01',
            'amount'       => 22233,
            'currency'     => 'HUF',
            'fuel_quantity' => 38.11,
            'odometer'     => 80608,
            'trip_id'       => null,
        ]);

        $trip1 = Trip::where('start_time', '>', '2024-12-03 17:00:00')
            ->where('start_time', '<', '2024-12-03 19:00:00')
            ->first();

        FuelExpense::create([
            'car_id'       => 1,
            'user_id'      => 1,
            'location_id'  => 2,
            'expense_date' => '2024-12-03 18:09:00',
            'amount'       => 28111,
            'currency'     => 'HUF',
            'fuel_quantity' => 44.48,
            'odometer'     => 87928,
            'trip_id'       => $trip1 ? $trip1->id : null,
        ]);

        $trip2 = Trip::where('start_time', '>', '2024-12-13 16:00:00')
            ->where('start_time', '<', '2024-12-13 18:00:00')
            ->first();

        FuelExpense::create([
            'car_id'       => 1,
            'user_id'      => 1,
            'location_id'  => 2,
            'expense_date' => '2024-12-13 16:55:00',
            'amount'       => 30347,
            'currency'     => 'HUF',
            'fuel_quantity' => 49.12,
            'odometer'     => 88725,
            'trip_id'       => $trip2 ? $trip2->id : null,
        ]);

        FuelExpense::create([
            'car_id'       => 2,
            'user_id'      => 2,
            'location_id'  => 2,
            'expense_date' => '2025-04-23 10:48:31',
            'amount'       => 20544,
            'currency'     => 'HUF',
            'fuel_quantity' => 32.2,
            'odometer'     => 87944,
            'trip_id'      => 10,
        ]);

        FuelExpense::create([
            'car_id'       => 2,
            'user_id'      => 1,
            'location_id'  => 2,
            'expense_date' => '2025-05-01 10:41:03',
            'amount'       => 21735,
            'currency'     => 'HUF',
            'fuel_quantity' => 35.0,
            'odometer'     => 87976,
            'trip_id'      => 11,
        ]);

        $gasStationTrips = Trip::whereHas('startLocation', function ($query) {
            $query->where('location_type', 'töltőállomás');
        })
            ->orWhereHas('destinationLocation', function ($query) {
                $query->where('location_type', 'töltőállomás');
            })
            ->get();

        foreach ($gasStationTrips as $gasStationTrip) {
            if (($trip1 && $gasStationTrip->id == $trip1->id) || ($trip2 && $gasStationTrip->id == $trip2->id)) {
                continue;
            }

            FuelExpense::factory()
                ->forTrip($gasStationTrip)
                ->create();
        }
    }
}
