<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\FuelPrice>
 */
class FuelPriceFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'period' => fake()->dateTimeBetween('-1 year', '+1 year'),
            'petrol' => fake()->randomFloat(0, 500, 800),
            'mixture' => fake()->randomFloat(0, 500, 800),
            'diesel' => fake()->randomFloat(0, 500, 800),
            'lp_gas' => fake()->randomFloat(0, 300, 600),
        ];
    }
}
