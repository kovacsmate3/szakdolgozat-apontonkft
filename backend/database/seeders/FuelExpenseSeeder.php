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
        // Korábbi két tankolás, az egyik egy utazáshoz kapcsolva

        // Megkeressük a megfelelő utazást az időpont alapján (2024-12-03 18:09:00 körüli utazás)
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

        // Megkeressük a második utazást is (2024-12-13 körüli utazás)
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

        // Generáljunk további töltési adatokat, véletlenszerűen kapcsoljuk utazásokhoz

        // Megkeressük azokat az utazásokat, ahol töltőállomás a cél- vagy kiindulási pont
        $gasStationTrips = Trip::whereHas('startLocation', function ($query) {
            $query->where('location_type', 'töltőállomás');
        })
            ->orWhereHas('destinationLocation', function ($query) {
                $query->where('location_type', 'töltőállomás');
            })
            ->get();

        // Ha találtunk töltőállomásos utazásokat, mindegyikhez rendelünk egy töltést
        foreach ($gasStationTrips as $gasStationTrip) {
            // Csak akkor, ha ezek az utazások nem az eddig már létrehozott két töltéshez kapcsolódnak
            if (($trip1 && $gasStationTrip->id == $trip1->id) || ($trip2 && $gasStationTrip->id == $trip2->id)) {
                continue;
            }

            FuelExpense::factory()
                ->forTrip($gasStationTrip)
                ->create();
        }
    }
}
