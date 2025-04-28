<?php

namespace Tests\Unit\Models;

use App\Models\Address;
use App\Models\Car;
use App\Models\Location;
use App\Models\TravelPurposeDictionary;
use App\Models\Trip;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\TestCase;
use PHPUnit\Framework\Attributes\Test;

class LocationTest extends TestCase
{
    use RefreshDatabase;

    public function setUp(): void
    {
        parent::setUp();

        // Kezdj új tranzakciót
        \DB::beginTransaction();
    }

    #[Test]
    public function it_has_one_address()
    {
        $location = Location::factory()->create();
        $address = Address::factory()->create(['location_id' => $location->id]);

        $this->assertEquals($address->id, $location->address->id);
        $this->assertInstanceOf(Address::class, $location->address);
    }

    #[Test]
    public function it_has_many_start_trips()
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

        $this->assertTrue($startLocation->startTrips->contains($trip));
        $this->assertInstanceOf(Trip::class, $startLocation->startTrips->first());
    }

    #[Test]
    public function it_has_many_destination_trips()
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

        $this->assertTrue($endLocation->destinationTrips->contains($trip));
        $this->assertInstanceOf(Trip::class, $endLocation->destinationTrips->first());
    }

    #[Test]
    public function it_belongs_to_many_travel_purposes()
    {
        $location = Location::factory()->create();
        $travelPurpose = TravelPurposeDictionary::factory()->create();

        $location->travelPurposes()->attach($travelPurpose->id);

        $this->assertTrue($location->travelPurposes->contains($travelPurpose));
        $this->assertInstanceOf(TravelPurposeDictionary::class, $location->travelPurposes->first());
    }

    #[Test]
    public function it_casts_is_headquarter_to_boolean()
    {
        $location = Location::factory()->create(['is_headquarter' => 1]);
        $this->assertIsBool($location->is_headquarter);
        $this->assertTrue($location->is_headquarter);
    }

    public function tearDown(): void
    {
        // Görgess vissza minden tranzakciót
        \DB::rollBack();
        parent::tearDown();
    }
}
