<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Law>
 */
class LawFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'title' => fake()->sentence(),
            'official_ref' => fake()->regexify('[0-9]{4}\. évi [A-Z]{1,3}\. tv'),
            'date_of_enactment' => fake()->date(),
            'is_active' => fake()->boolean(75),
            'link' => fake()->url(),
            'category_id' => null,
        ];
    }
}
