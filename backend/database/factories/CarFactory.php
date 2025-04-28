<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Faker\Provider\Fakecar;

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
        $this->faker->addProvider(new FakeCar($this->faker));
        $vehicle = $this->faker->vehicleArray();

        return [
            'user_id' => fake()->randomElement([1, 2, 3, 4, 5, 6, 7]),
            'car_type' => fake()->randomElement(['hatchback', 'sedan', 'convertible', 'SUV', 'coupe']),
            'license_plate' => $this->faker->vehicleRegistration,
            'manufacturer' => $vehicle['brand'],
            'model' => $vehicle['model'],
            'fuel_type' => $this->faker->vehicleFuelType,
            'standard_consumption' => fake()->randomFloat(1, 4, 15),
            'capacity' => fake()->numberBetween(800, 3000),
            'fuel_tank_capacity' => fake()->numberBetween(30, 120),
        ];
    }
}
