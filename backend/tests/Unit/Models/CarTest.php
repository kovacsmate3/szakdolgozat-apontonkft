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

class CarTest extends TestCase
{
    use RefreshDatabase;

    public function setUp(): void
    {
        parent::setUp();
        \DB::beginTransaction();
    }

    #[Test]
    public function it_belongs_to_user()
    {
        $user = User::factory()->create();
        $car = Car::factory()->create(['user_id' => $user->id]);

        $this->assertEquals($user->id, $car->user->id);
        $this->assertInstanceOf(User::class, $car->user);
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

        $this->assertTrue($car->fuelExpenses->contains($fuelExpense));
        $this->assertInstanceOf(FuelExpense::class, $car->fuelExpenses->first());
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

        $this->assertTrue($car->trips->contains($trip));
        $this->assertInstanceOf(Trip::class, $car->trips->first());
    }

    #[Test]
    public function it_casts_attributes_correctly()
    {
        $user = User::factory()->create();
        $car = Car::factory()->create([
            'user_id' => $user->id,
            'standard_consumption' => 7.5,
            'capacity' => 2000,
            'fuel_tank_capacity' => 60
        ]);

        $this->assertIsFloat($car->standard_consumption);
        $this->assertIsInt($car->capacity);
        $this->assertIsInt($car->fuel_tank_capacity);
    }

    public function tearDown(): void
    {
        // Görgess vissza minden tranzakciót
        \DB::rollBack();
        parent::tearDown();
    }
}
