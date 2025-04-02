<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\TravelPurposeDictionary>
 */
class TravelPurposeDictionaryFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'travel_purpose' => fake()->words(2, true),
            'type' => fake()->randomElement(['Üzleti','Magán','Egyéb']),
            'note' => fake()->boolean(30) ? fake()->sentence() : null,
            'is_system' => fake()->boolean(10),
        ];
    }
}
