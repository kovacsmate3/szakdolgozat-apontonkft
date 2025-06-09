<?php

namespace Tests\Unit\Models;

use App\Models\Car;
use App\Models\Location;
use App\Models\Trip;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\TestCase;
use PHPUnit\Framework\Attributes\Test;


class TripTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function it_belongs_to_car()
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

        $this->assertEquals($car->id, $trip->car->id);
        $this->assertInstanceOf(Car::class, $trip->car);
    }

    #[Test]
    public function it_belongs_to_user()
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

        $this->assertEquals($user->id, $trip->user->id);
        $this->assertInstanceOf(User::class, $trip->user);
    }

    #[Test]
    public function it_belongs_to_start_location()
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

        $this->assertEquals($startLocation->id, $trip->startLocation->id);
        $this->assertInstanceOf(Location::class, $trip->startLocation);
    }

    #[Test]
    public function it_belongs_to_destination_location()
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

        $this->assertEquals($endLocation->id, $trip->destinationLocation->id);
        $this->assertInstanceOf(Location::class, $trip->destinationLocation);
    }

    #[Test]
    public function it_casts_attributes_correctly()
    {
        $user = User::factory()->create();

        $car = Car::factory()->create([
            'user_id' => $user->id,
        ]);

        $startLocation = Location::factory()->create();
        $endLocation = Location::factory()->create();

        $trip = Trip::factory()->create([
            'car_id' => $car->id,
            'user_id' => $user->id,
            'start_location_id' => $startLocation->id,
            'destination_location_id' => $endLocation->id,
            'start_time' => now()->subHour(),
            'end_time' => now(),
            'planned_distance' => 25.5,
            'actual_distance' => 26.2,
            'start_odometer' => 10000,
            'end_odometer' => 10026,
        ]);

        $this->assertInstanceOf(\Carbon\Carbon::class, $trip->start_time);
        $this->assertInstanceOf(\Carbon\Carbon::class, $trip->end_time);
        $this->assertIsFloat($trip->planned_distance);
        $this->assertIsFloat($trip->actual_distance);
        $this->assertIsInt($trip->start_odometer);
        $this->assertIsInt($trip->end_odometer);
    }
}
