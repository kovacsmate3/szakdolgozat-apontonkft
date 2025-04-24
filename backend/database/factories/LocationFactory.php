<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Location>
 */
class LocationFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->address(),
            'location_type' => fake()->randomElement(['partner', 'telephely', 'töltőállomás', 'bolt', 'egyéb']),
            'is_headquarter' => false,
            'user_id' => User::inRandomOrder()->first()->id ?? null,
        ];
    }
}
