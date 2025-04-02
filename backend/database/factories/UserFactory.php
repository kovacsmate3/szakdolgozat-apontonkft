<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\User>
 */
class UserFactory extends Factory
{
    /**
     * The current password being used by the factory.
     */
    protected static ?string $password;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $firstName = fake()->firstName();
        $lastName  = fake()->lastName();

        $baseUsername = Str::slug($firstName . ' ' . $lastName, '_');
        $username = $this->faker->unique()->bothify($baseUsername . '##');

        $baseEmail = strtolower(substr($firstName, 0, 1) . $lastName);

        return [
            'username' => $username,
            'firstname' => $firstName,
            'lastname' => $lastName,
            'birthdate' => fake()->date('Y-m-d', '2008-01-01'),
            'email' => $baseEmail . '@' . fake()->safeEmailDomain(),
            'email_verified_at' => now(),
            'password' => static::$password ??= Hash::make('password'),
            'password_changed_at' => now(),
            'role_id' => null,
            'phonenumber' => fake()->unique()->phoneNumber(),
            'remember_token' => null,
        ];
    }

    /**
     * Indicate that the model's email address should be unverified.
     */
    public function unverified(): static
    {
        return $this->state(fn(array $attributes) => [
            'email_verified_at' => null,
        ]);
    }
}
