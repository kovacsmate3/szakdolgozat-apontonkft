<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\FuelExpense>
 */
class FuelExpenseFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'expense_date' => fake()->dateTimeBetween('-2 year', 'now'),
            'amount' => fake()->randomFloat(2, 1000, 40000),
            'currency' => fake()->currencyCode(),
            'fuel_quantity' => fake()->randomFloat(1, 5, 80),
            'odometer' => fake()->numberBetween(10000, 300000),
        ];
    }
}
