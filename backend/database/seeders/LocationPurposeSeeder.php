<?php

namespace Database\Seeders;

use App\Models\Location;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class LocationPurposeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $records = [
            ['location_id' => 1, 'travel_purpose_id' => 6],
            ['location_id' => 2, 'travel_purpose_id' => 13],
            ['location_id' => 3, 'travel_purpose_id' => 6],
            ['location_id' => 4, 'travel_purpose_id' => 5],
            ['location_id' => 5, 'travel_purpose_id' => 18],
            ['location_id' => 6, 'travel_purpose_id' => 19],
            ['location_id' => 7, 'travel_purpose_id' => 15],
            ['location_id' => 8, 'travel_purpose_id' => 7],
        ];

        foreach ($records as $record) {
            $location = Location::find($record['location_id']);
            if ($location) {
                $location->travelPurposes()->syncWithoutDetaching([$record['travel_purpose_id']]);
            }
        }
    }
}
