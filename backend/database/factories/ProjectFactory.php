<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Project>
 */
class ProjectFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $year = date('Y');
        $jobNumberSequence = str_pad($this->faker->unique()->numberBetween(1, 999), 3, '0', STR_PAD_LEFT);
        $jobNumber = $year . '.' . $jobNumberSequence;

        return [
            'job_number' => $jobNumber,
            'project_name' => fake()->words(2, true),
            'location' => fake()->randomElement(['belterület','külterület','zártkert']),
            'parcel_identification_number' => fake()->regexify('[0-9]{5}(?:-[0-9]{1,2}(?:-[A-Z])?(?:-[0-9]{1,3})?)?'),
            'deadline' => fake()->dateTimeBetween('+1 month', '+6 months'),
            'description' => fake()->paragraph(),
            'status' => fake()->randomElement(['várakozó','befejezett','folyamatban lévő','felfüggesztett']),
            'address_id' => null,
        ];
    }
}
