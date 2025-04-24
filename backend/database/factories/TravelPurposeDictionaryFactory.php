<?php

namespace Database\Factories;

use App\Models\User;
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
        // Véletlenszerűen választunk egy felhasználót, ha van legalább egy az adatbázisban
        $userId = null;
        if (User::count() > 0) {
            $userId = User::inRandomOrder()->first()->id;
        }

        return [
            'travel_purpose' => fake()->words(2, true),
            'type' => fake()->randomElement(['Üzleti', 'Magán', 'Egyéb']),
            'note' => fake()->boolean(30) ? fake()->sentence() : null,
            'is_system' => fake()->boolean(10),
            'user_id' => $userId,
        ];
    }

    /**
     * Rendszerszintű rekord jelölése.
     */
    public function systemRecord()
    {
        return $this->state(function (array $attributes) {
            return [
                'is_system' => true,
            ];
        });
    }

    /**
     * Magánrekord jelölése.
     */
    public function personalRecord()
    {
        return $this->state(function (array $attributes) {
            return [
                'is_system' => false,
            ];
        });
    }

    /**
     * Beállít egy konkrét felhasználót a rekordhoz.
     */
    public function forUser(User $user)
    {
        return $this->state(function (array $attributes) use ($user) {
            return [
                'user_id' => $user->id,
            ];
        });
    }
}
