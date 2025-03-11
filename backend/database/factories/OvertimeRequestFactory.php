<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\OvertimeRequest>
 */
class OvertimeRequestFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $date = fake()->dateTimeBetween('-2 months','now')->format('Y-m-d');
        $hours = fake()->time('H:i:s','04:00:00');

        $status = fake()->randomElement(['függőben lévő','jóváhagyott','elutasított']);

        if ($status !== 'függőben lévő') {
            $processedAt = fake()->dateTimeBetween($date, '+1 day');
            $processedBy = fake()->numberBetween(1, 2);
            $decisionComment = fake()->optional()->sentence();
        } else {
            $processedAt = null;
            $processedBy = null;
            $decisionComment = null;
        }

        return [
            'date' => $date,
            'hours' => $hours,
            'status' => $status,
            'reason' => fake()->sentence(),
            'processed_at' => $processedAt,
            'processed_by' => $processedBy,
            'decision_comment' => $decisionComment
        ];
    }
}
