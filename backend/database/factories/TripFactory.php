<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Trip>
 */
class TripFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $startTime = $this->faker->dateTimeBetween('-1 month', 'now');
        $endTime = (clone $startTime)->modify('+'.rand(30, 300).' minutes');

        $startOdometer = $this->faker->numberBetween(10000, 200000);
        $endOdometer = $startOdometer + $this->faker->numberBetween(10, 300);

        return [
            'start_time' => $startTime,
            'end_time' => $endTime,
            'planned_distance' => $this->faker->randomFloat(1, 10, 300),
            'actual_distance' => $this->faker->randomFloat(1, 10, 300),
            'start_odometer' => $startOdometer,
            'end_odometer' => $endOdometer,
            'planned_duration' => $this->faker->time('H:i:s', '3:00:00'),
            'actual_duration' => $this->faker->time('H:i:s', '4:00:00'),
        ];
    }
}
