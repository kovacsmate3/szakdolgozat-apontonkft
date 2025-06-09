<?php

namespace Database\Factories;

use App\Models\Trip;
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
            'trip_id' => null,
        ];
    }

    /**
     * Kapcsolódó utazást ad hozzá a töltési adathoz.
     */
    public function forTrip(?Trip $trip = null)
    {
        // Ha nincs megadva konkrét Trip objektum, akkor véletlenszerűen választunk egyet
        if (!$trip && Trip::count() > 0) {
            $trip = Trip::inRandomOrder()->first();
        }

        if (!$trip) {
            return $this->state(function (array $attributes) {
                return [
                    'trip_id' => null,
                ];
            });
        }

        // Ha van Trip, akkor az adataihoz igazítjuk a töltést
        return $this->state(function (array $attributes) use ($trip) {
            return [
                'car_id' => $trip->car_id,
                'user_id' => $trip->user_id,
                'expense_date' => $trip->end_time ?? $trip->start_time,
                'trip_id' => $trip->id,
                'odometer' => $trip->end_odometer ?? $trip->start_odometer, // Lehetőleg a út végállomása
            ];
        });
    }
}
