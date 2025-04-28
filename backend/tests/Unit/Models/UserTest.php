<?php

namespace Tests\Unit\Models;

use App\Models\Car;
use App\Models\FuelExpense;
use App\Models\Location;
use App\Models\Trip;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\TestCase;
use PHPUnit\Framework\Attributes\Test;

class UserTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function it_has_many_cars()
    {
        $user = User::factory()->create();
        $car = Car::factory()->create(['user_id' => $user->id]);

        $this->assertTrue($user->cars->contains($car));
        $this->assertInstanceOf(Car::class, $user->cars->first());
    }

    #[Test]
    public function it_has_many_fuel_expenses()
    {
        $user = User::factory()->create();
        $car = Car::factory()->create(['user_id' => $user->id]);
        $location = Location::factory()->create(['location_type' => 'töltőállomás']);
        $fuelExpense = FuelExpense::factory()->create([
            'user_id' => $user->id,
            'car_id' => $car->id,
            'location_id' => $location->id
        ]);

        $this->assertTrue($user->fuelExpenses->contains($fuelExpense));
        $this->assertInstanceOf(FuelExpense::class, $user->fuelExpenses->first());
    }

    #[Test]
    public function it_has_many_trips()
    {
        $user = User::factory()->create();
        $car = Car::factory()->create(['user_id' => $user->id]);
        $startLocation = Location::factory()->create();
        $endLocation = Location::factory()->create();

        $trip = Trip::factory()->create([
            'user_id' => $user->id,
            'car_id' => $car->id,
            'start_location_id' => $startLocation->id,
            'destination_location_id' => $endLocation->id
        ]);

        $this->assertTrue($user->trips->contains($trip));
        $this->assertInstanceOf(Trip::class, $user->trips->first());
    }

    #[Test]
    public function it_belongs_to_role()
    {
        $role = \App\Models\Role::factory()->create();
        $user = User::factory()->create(['role_id' => $role->id]);

        $this->assertEquals($role->id, $user->role->id);
    }

    #[Test]
    public function it_casts_attributes_correctly()
    {
        $user = User::factory()->create([
            'birthdate' => '1990-01-01',
            'email_verified_at' => now(),
            'password_changed_at' => now()
        ]);

        $this->assertIsObject($user->birthdate);
        $this->assertInstanceOf(\Carbon\Carbon::class, $user->birthdate);
        $this->assertInstanceOf(\Carbon\Carbon::class, $user->email_verified_at);
        $this->assertInstanceOf(\Carbon\Carbon::class, $user->password_changed_at);
    }
}
