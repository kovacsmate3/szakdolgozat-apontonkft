<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Address>
 */
class AddressFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'country' => fake()->country(), // alapértelmezett
            'postalcode' => fake()->numberBetween(1000, 9999),
            'city' => fake()->city(),
            'road_name' => fake()->streetName(),
            'public_space_type' => fake()->randomElement(['sor','erdősor','köz','utca', 'tér', 'sétány','út', 'határút','bányatelep', 'ösvény','sugárút','rakpart','pincesor', 'dűlő']),
            'building_number' => fake()->buildingNumber(),
        ];
    }
}
