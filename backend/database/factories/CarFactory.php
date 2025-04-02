<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Car>
 */
class CarFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'car_type' => fake()->vehicleType(),
            'license_plate' => fake()->vehicleRegistration('[A-Z]{3}-[0-9]{3}'),
            'manufacturer' => fake()->vehicleBrand(),
            'model' => fake()->vehicleModel(),
            'fuel_type' => fake()->vehicleFuelType(),
            'standard_consumption' => fake()->vehicleFuelConsumptionValue(),
            'capacity' => fake()->numberBetween(800, 3000),
            'fuel_tank_capacity' => fake()->numberBetween(30, 120),
        ];
    }
}
