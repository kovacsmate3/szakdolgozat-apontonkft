<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\LeaveRequest>
 */
class LeaveRequestFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $start = fake()->dateTimeBetween('-1 month', '+1 month');
        $end   = (clone $start)->modify('+' . rand(1, 7) . ' days');

        $status = fake()->randomElement(['függőben lévő','jóváhagyott','elutasított']);

        if ($status !== 'függőben lévő') {
            $processedAt = fake()->dateTimeBetween($start, '+2 days');
            $processedBy = 1;
            $decisionComment = fake()->optional()->sentence();
        } else {
            $processedAt = null;
            $processedBy = null;
            $decisionComment = null;
        }


        return [
            'start_date' => $start->format('Y-m-d'),
            'end_date'   => $end->format('Y-m-d'),
            'status'     => $status,
            'reason'     => fake()->sentence(),
            'processed_at'=> $processedAt,
            'processed_by'=> $processedBy,
            'decision_comment'=> $decisionComment,
        ];
    }
}
