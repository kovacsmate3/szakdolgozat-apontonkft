<?php

namespace Database\Seeders;

use App\Models\Trip;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class TripSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {

        $locationToPurposeMap = [
            1 => 6,
            2 => 13,
            3 => 6,
            4 => 5,
            5 => 18,
            6 => 19,
            7 => 15,
            8 => 7,
        ];

        $trips = [
            [
                'car_id' => 1,
                'user_id' => 1,
                'start_location_id' => 1,
                'destination_location_id' => 4,
                'start_time' => '2024-12-02 07:36:00',
                'end_time' => '2024-12-02 08:30:00',
                'planned_distance' => 38.1,
                'actual_distance' => 38.1,
                'start_odometer' => 87700,
                'end_odometer' => 87738,
                'planned_duration' => '01:00:00',
                'actual_duration' => '00:54:00',
                'dict_id' => 5,
            ],
            [
                'car_id' => 1,
                'user_id' => 1,
                'start_location_id' => 4,
                'destination_location_id' => 5,
                'start_time' => '2024-12-02 16:30:00',
                'end_time' => '2024-12-02 17:15:00',
                'planned_distance' => 30.9,
                'actual_distance' => 30.9,
                'start_odometer' => 87738,
                'end_odometer' => 87769,
                'planned_duration' => '00:45:00',
                'actual_duration' => '00:45:00',
                'dict_id' => 18,
            ],
            [
                'car_id' => 1,
                'user_id' => 1,
                'start_location_id' => 5,
                'destination_location_id' => 3,
                'start_time' => '2024-12-02 18:09:00',
                'end_time' => '2024-12-02 18:49:00',
                'planned_distance' => 31.2,
                'actual_distance' => 31.2,
                'start_odometer' => 87769,
                'end_odometer' => 87800,
                'planned_duration' => '00:40:00',
                'actual_duration' => '00:40:00',
                'dict_id' => 6,
            ],
            [
                'car_id' => 1,
                'user_id' => 1,
                'start_location_id' => 3,
                'destination_location_id' => 4,
                'start_time' => '2024-12-06 07:32:00',
                'end_time' => '2024-12-06 08:30:00',
                'planned_distance' => 38.1,
                'actual_distance' => 38.1,
                'start_odometer' => 87800,
                'end_odometer' => 87838,
                'planned_duration' => '00:58:00',
                'actual_duration' => '00:58:00',
                'dict_id' => 5,
            ],
            [
                'car_id' => 1,
                'user_id' => 1,
                'start_location_id' => 4,
                'destination_location_id' => 7,
                'start_time' => '2024-12-06 16:05:12',
                'end_time' => '2024-12-06 17:15:41',
                'planned_distance' => 45.3,
                'actual_distance' => 45.1,
                'start_odometer' => 87838,
                'end_odometer' => 87883,
                'planned_duration' => '01:10:00',
                'actual_duration' => '01:10:29',
                'dict_id' => 15,
            ],
            [
                'car_id' => 1,
                'user_id' => 1,
                'start_location_id' => 7,
                'destination_location_id' => 1,
                'start_time' => '2024-12-06 17:32:03',
                'end_time' => '2024-12-06 17:42:01',
                'planned_distance' => 3.8,
                'actual_distance' => 3.8,
                'start_odometer' => 87883,
                'end_odometer' => 87887,
                'planned_duration' => '00:10:00',
                'actual_duration' => '00:09:58',
                'dict_id' => 6,
            ],
            [
                'car_id' => 1,
                'user_id' => 1,
                'start_location_id' => 1,
                'destination_location_id' => 6,
                'start_time' => '2024-12-13 07:32:00',
                'end_time' => '2024-12-13 07:52:12',
                'planned_distance' => 8,
                'actual_distance' => 9.2,
                'start_odometer' => 87887,
                'end_odometer' => 87896,
                'planned_duration' => '00:14:50',
                'actual_duration' => '00:20:12',
                'dict_id' => 19,
            ],
            [
                'car_id' => 1,
                'user_id' => 1,
                'start_location_id' => 6, // Praktiker
                'destination_location_id' => 2, // MOL
                'start_time' => '2024-12-13 9:33:07',
                'end_time' => '2024-12-13 9:38:47',
                'planned_distance' => 3.9,
                'actual_distance' => 3.9,
                'start_odometer' => 87896,
                'end_odometer' => 87900,
                'planned_duration' => '00:4:12',
                'actual_duration' => '00:05:40',
                'dict_id' => 13,
            ],
            [
                'car_id' => 1,
                'user_id' => 1,
                'start_location_id' => 2,
                'destination_location_id' => 3,
                'start_time' => '2024-12-13 9:55:13',
                'end_time' => '2024-12-13 10:10:07',
                'planned_distance' => 6.5,
                'actual_distance' => 6.3,
                'start_odometer' => 87900,
                'end_odometer' => 87906,
                'planned_duration' => '00:15:00',
                'actual_duration' => '00:14:54',
                'dict_id' => 6,
            ],
        ];

        // Alternatív megoldás, ha a fenti manuális hozzárendelés helyett
        // automatikusan szeretnénk hozzárendelni a purpose ID-ket
        /*
        $trips = array_map(function ($trip) use ($locationToPurposeMap) {
            $destinationLocationId = $trip['destination_location_id'];
            $trip['travel_purpose_id'] = $locationToPurposeMap[$destinationLocationId] ?? null;
            return $trip;
        }, $trips);
        */


        foreach ($trips as $trip) {
            Trip::create($trip);
        }
    }
}
