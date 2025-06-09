<?php

namespace Database\Seeders;

use App\Models\Trip;
use Carbon\Carbon;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class TripSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        /*
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
        */

        $existingTrips  = [
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


        foreach ($existingTrips  as $trip) {
            Trip::create($trip);
        }

        // Get all business travel purpose IDs
        $businessPurposeIds = \App\Models\TravelPurposeDictionary::where('type', 'Üzleti')
            ->pluck('id')
            ->toArray();

        // Location mappings for realistic trips
        $locationPairs = [
            // Company offices to partners
            [1, 4], // A-Ponton Kft. székhely to Épkar Zrt.
            [1, 5], // A-Ponton Kft. székhely to SZÁM-SZIL Kft.
            [3, 4], // A-Ponton Kft. iroda to Épkar Zrt.
            [3, 5], // A-Ponton Kft. iroda to SZÁM-SZIL Kft.

            // Return trips
            [4, 1], // Épkar Zrt. to A-Ponton Kft. székhely
            [5, 1], // SZÁM-SZIL Kft. to A-Ponton Kft. székhely
            [4, 3], // Épkar Zrt. to A-Ponton Kft. iroda
            [5, 3], // SZÁM-SZIL Kft. to A-Ponton Kft. iroda

            // Between offices
            [1, 3], // A-Ponton Kft. székhely to iroda
            [3, 1], // A-Ponton Kft. iroda to székhely

            // Practical errands
            [1, 6], // A-Ponton Kft. székhely to Praktiker
            [3, 6], // A-Ponton Kft. iroda to Praktiker
            [1, 7], // A-Ponton Kft. székhely to Posta
            [3, 7], // A-Ponton Kft. iroda to Posta

            // Return from errands
            [6, 1], // Praktiker to A-Ponton Kft. székhely
            [6, 3], // Praktiker to A-Ponton Kft. iroda
            [7, 1], // Posta to A-Ponton Kft. székhely
            [7, 3], // Posta to A-Ponton Kft. iroda

            // Fuel
            [1, 2], // A-Ponton Kft. székhely to MOL
            [3, 2], // A-Ponton Kft. iroda to MOL
            [2, 1], // MOL to A-Ponton Kft. székhely
            [2, 3], // MOL to A-Ponton Kft. iroda
            [4, 2], // Épkar Zrt. to MOL
            [5, 2], // SZÁM-SZIL Kft. to MOL
            [2, 4], // MOL to Épkar Zrt.
            [2, 5], // MOL to SZÁM-SZIL Kft.
        ];

        // Location to distances (approximate in km)
        $locationDistances = [
            // Office to partners
            '1-4' => 38.1, // A-Ponton Kft. székhely to Épkar Zrt.
            '1-5' => 30.9, // A-Ponton Kft. székhely to SZÁM-SZIL Kft.
            '3-4' => 38.1, // A-Ponton Kft. iroda to Épkar Zrt.
            '3-5' => 31.2, // A-Ponton Kft. iroda to SZÁM-SZIL Kft.

            // Return trips
            '4-1' => 38.1, // Épkar Zrt. to A-Ponton Kft. székhely
            '5-1' => 30.9, // SZÁM-SZIL Kft. to A-Ponton Kft. székhely
            '4-3' => 38.1, // Épkar Zrt. to A-Ponton Kft. iroda
            '5-3' => 31.2, // SZÁM-SZIL Kft. to A-Ponton Kft. iroda

            // Between offices
            '1-3' => 6.5, // A-Ponton Kft. székhely to iroda
            '3-1' => 6.5, // A-Ponton Kft. iroda to székhely

            // Practical errands
            '1-6' => 8.0, // A-Ponton Kft. székhely to Praktiker
            '3-6' => 4.5, // A-Ponton Kft. iroda to Praktiker
            '1-7' => 3.8, // A-Ponton Kft. székhely to Posta
            '3-7' => 7.3, // A-Ponton Kft. iroda to Posta

            // Return from errands
            '6-1' => 8.0, // Praktiker to A-Ponton Kft. székhely
            '6-3' => 4.5, // Praktiker to A-Ponton Kft. iroda
            '7-1' => 3.8, // Posta to A-Ponton Kft. székhely
            '7-3' => 7.3, // Posta to A-Ponton Kft. iroda

            // Fuel
            '1-2' => 5.9, // A-Ponton Kft. székhely to MOL
            '3-2' => 4.3, // A-Ponton Kft. iroda to MOL
            '2-1' => 5.9, // MOL to A-Ponton Kft. székhely
            '2-3' => 4.3, // MOL to A-Ponton Kft. iroda
            '4-2' => 45.1, // Épkar Zrt. to MOL
            '5-2' => 35.3, // SZÁM-SZIL Kft. to MOL
            '2-4' => 45.1, // MOL to Épkar Zrt.
            '2-5' => 35.3, // MOL to SZÁM-SZIL Kft.
        ];

        // Kezdő kilométeróra állás
        $odometerReading = 80000;

        // Elérhető felhasználók és autók
        $validUsers = [1, 3, 4];
        $cars = [1, 2];

        // Utak létrehozása havi bontásban
        for ($month = 1; $month <= 11; $month++) {
            // Havi utak száma
            $tripsThisMonth = rand(10, 15);

            // Elsődleges felhasználó és autó a hónapra
            $primaryUser = $validUsers[array_rand($validUsers)];
            $primaryCar = $cars[array_rand($cars)];

            // 1. LÉPÉS: Havi utak generálása, időrendi sorrend nélkül
            $monthlyTrips = [];

            for ($i = 0; $i < $tripsThisMonth; $i++) {
                // Dátum generálása
                $day = rand(1, min(28, Carbon::create(2024, $month)->daysInMonth));
                $hour = rand(7, 17);
                $minute = rand(0, 59);
                $startTime = Carbon::create(2024, $month, $day, $hour, $minute);

                // Útvonal kiválasztása
                $locationPair = $locationPairs[array_rand($locationPairs)];
                $startLocationId = $locationPair[0];
                $destLocationId = $locationPair[1];

                // Távolság számítása
                $routeKey = "{$startLocationId}-{$destLocationId}";
                $baseDistance = $locationDistances[$routeKey] ?? 10;
                $plannedDistance = $baseDistance + (rand(-5, 15) / 10);
                $actualDistance = $plannedDistance + (rand(-10, 20) / 10);
                if ($actualDistance < 1) $actualDistance = 1;

                // Időtartam számítása
                $avgSpeed = rand(40, 60);
                $durationMinutes = round(($actualDistance / $avgSpeed) * 60);
                $durationMinutes = max(5, $durationMinutes);
                $actualDurationMinutes = $durationMinutes + rand(-5, 10);
                if ($actualDurationMinutes < 3) $actualDurationMinutes = 3;

                $plannedHours = floor($durationMinutes / 60);
                $plannedMinutes = $durationMinutes % 60;
                $plannedDuration = sprintf("%02d:%02d:00", $plannedHours, $plannedMinutes);

                $actualHours = floor($actualDurationMinutes / 60);
                $actualMinutes = $actualDurationMinutes % 60;
                $actualDuration = sprintf("%02d:%02d:00", $actualHours, $actualMinutes);

                // Végidő számítása
                $endTime = (clone $startTime)->addMinutes($actualDurationMinutes);

                // Utazási cél kiválasztása
                $dictId = null;
                if ($startLocationId == 2 || $destLocationId == 2) {
                    $dictId = 13; // Üzemanyagfeltöltés/Tankolás
                } elseif (in_array($startLocationId, [4, 5]) || in_array($destLocationId, [4, 5])) {
                    $dictId = in_array(rand(1, 10), [1, 2, 3]) ? 5 : 17; // Geodéziai felmérés or Üzleti tárgyalás
                } elseif ($startLocationId == 7 || $destLocationId == 7) {
                    $dictId = rand(0, 1) ? 15 : 16; // Letter sending or receiving
                } elseif ($startLocationId == 6 || $destLocationId == 6) {
                    $dictId = 19; // Vásárlás - magáncélú
                } elseif (($startLocationId == 1 && $destLocationId == 3) ||
                    ($startLocationId == 3 && $destLocationId == 1)
                ) {
                    $dictId = 6; // Irodai munka
                } else {
                    $businessOnlyPurposes = array_filter($businessPurposeIds, function ($id) {
                        return $id != 2 && $id != 7 && $id != 19; // Exclude non-business
                    });
                    $dictId = $businessOnlyPurposes[array_rand($businessOnlyPurposes)];
                }

                // Út hozzáadása a havi listához (kilométeróra állások nélkül)
                $monthlyTrips[] = [
                    'car_id' => $primaryCar,
                    'user_id' => $primaryUser,
                    'start_location_id' => $startLocationId,
                    'destination_location_id' => $destLocationId,
                    'start_time' => $startTime,
                    'end_time' => $endTime,
                    'planned_distance' => $plannedDistance,
                    'actual_distance' => $actualDistance,
                    'planned_duration' => $plannedDuration,
                    'actual_duration' => $actualDuration,
                    'dict_id' => $dictId,
                ];
            }

            // 2. LÉPÉS: Utak rendezése időrendi sorrendbe
            usort($monthlyTrips, function ($a, $b) {
                return $a['start_time']->timestamp - $b['start_time']->timestamp;
            });

            // 3. LÉPÉS: Kilométeróra állások beállítása időrendi sorrendben és utak létrehozása
            foreach ($monthlyTrips as $trip) {
                $trip['start_odometer'] = $odometerReading;
                $trip['end_odometer'] = $odometerReading + round($trip['actual_distance']);
                $odometerReading = $trip['end_odometer'];

                // Út létrehozása az adatbázisban
                Trip::create($trip);
            }

            // Egyéb használat szimulálása a hónap végén
            $odometerReading += rand(50, 200);
        }
    }
}
