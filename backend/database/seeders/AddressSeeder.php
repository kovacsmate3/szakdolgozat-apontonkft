<?php

namespace Database\Seeders;

use App\Models\Address;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class AddressSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $addresses = [
            [
                'location_id' => 1,
                'country' => 'Magyarország',
                'postalcode' => 1151,
                'city' => 'Budapest',
                'road_name' => 'Esthajnal',
                'public_space_type' => 'utca',
                'building_number' => '3.',
            ],
            [
                'location_id' => 2,
                'country' => 'Magyarország',
                'postalcode' => 1151,
                'city' => 'Budapest',
                'road_name' => 'Bogáncs',
                'public_space_type' => 'utca',
                'building_number' => '1.',
            ],
            [
                'location_id' => 3,
                'country' => 'Magyarország',
                'postalcode' => 1151,
                'city' => 'Budapest',
                'road_name' => 'Székely Elek',
                'public_space_type' => 'út',
                'building_number' => '11.',
            ],
            [
                'location_id' => 4,
                'country' => 'Magyarország',
                'postalcode' => 2051,
                'city' => 'Biatorbágy',
                'road_name' => 'Nimród',
                'public_space_type' => 'utca',
                'building_number' => '17.',
            ],
            [
                'location_id' => 5,
                'country' => 'Magyarország',
                'postalcode' => 1042,
                'city' => 'Budapest',
                'road_name' => 'Árpád',
                'public_space_type' => 'út',
                'building_number' => '56.',
            ],
            [
                'location_id' => 6,
                'country' => 'Magyarország',
                'postalcode' => 1152,
                'city' => 'Budapest',
                'road_name' => 'Városkapu',
                'public_space_type' => 'utca',
                'building_number' => '5.',
            ],
            [
                'location_id' => 7,
                'country' => 'Magyarország',
                'postalcode' => 1151,
                'city' => 'Budapest',
                'road_name' => 'Fő',
                'public_space_type' => 'út',
                'building_number' => '68.',
            ],
            [
                'location_id' => 8,
                'country' => 'Magyarország',
                'postalcode' => 1028,
                'city' => 'Budapest',
                'road_name' => 'Gazda',
                'public_space_type' => 'utca',
                'building_number' => '82.',
            ],
            [
                'location_id' => null,
                'country'     => 'Magyarország',
                'postalcode'  => 1065,
                'city'        => 'Budapest',
                'road_name'   => 'Podmaniczky',
                'public_space_type' => 'utca',
                'building_number'   => '14.',
            ],
            [
                'location_id' => null,
                'country'     => 'Magyarország',
                'postalcode'  => 2011,
                'city'        => 'Budakalász',
                'road_name'   => 'Petőfi Sándor',
                'public_space_type' => 'utca',
                'building_number'   => '9.',
            ],
            [
                'location_id' => null,
                'country'     => 'Magyarország',
                'postalcode'  => 1026,
                'city'        => 'Budapest',
                'road_name'   => 'Gábor Áron',
                'public_space_type' => 'utca',
                'building_number'   => '26.',
            ],
            [
                'location_id' => null,
                'country'     => 'Magyarország',
                'postalcode'  => 2310,
                'city'        => 'Szigetszentmiklós',
                'road_name'   => 'Diósgyőri',
                'public_space_type' => 'utca',
                'building_number'   => '1.',
            ],
            [
                'location_id' => null,
                'country'     => 'Magyarország',
                'postalcode'  => 2141,
                'city'        => 'Csömör',
                'road_name'   => 'Szőlő',
                'public_space_type' => 'köz',
                'building_number'   => '1.',
            ],
            [
                'location_id' => 9,
                'country' => 'Magyarország',
                'postalcode' => 6710,
                'city' => 'Szeged',
                'road_name' => 'Zsoldos',
                'public_space_type' => 'köz',
                'building_number' => '4.',
            ],
        ];

        foreach ($addresses as $address) {
            Address::create($address);
        }
    }
}
