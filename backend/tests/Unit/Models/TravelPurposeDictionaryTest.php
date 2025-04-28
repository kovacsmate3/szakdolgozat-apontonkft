<?php

namespace Tests\Unit\Models;

use App\Models\Car;
use App\Models\Location;
use App\Models\TravelPurposeDictionary;
use App\Models\Trip;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\TestCase;
use PHPUnit\Framework\Attributes\Test;

class TravelPurposeDictionaryTest extends TestCase
{
    use RefreshDatabase;

    public function setUp(): void
    {
        parent::setUp();

        // Kezdj új tranzakciót
        \DB::beginTransaction();
    }

    #[Test]
    public function it_belongs_to_user()
    {
        $user = User::factory()->create();
        $travelPurpose = TravelPurposeDictionary::factory()->create(['user_id' => $user->id]);

        $this->assertEquals($user->id, $travelPurpose->user->id);
        $this->assertInstanceOf(User::class, $travelPurpose->user);
    }

    #[Test]
    public function it_belongs_to_many_locations()
    {
        $travelPurpose = TravelPurposeDictionary::factory()->create();
        $location = Location::factory()->create();

        $travelPurpose->locations()->attach($location->id);

        $this->assertTrue($travelPurpose->locations->contains($location));
        $this->assertInstanceOf(Location::class, $travelPurpose->locations->first());
    }

    #[Test]
    public function it_has_many_trips()
    {
        $travelPurpose = TravelPurposeDictionary::factory()->create();
        $user = User::factory()->create();
        $car = Car::factory()->create(['user_id' => $user->id]);
        $startLocation = Location::factory()->create();
        $endLocation = Location::factory()->create();

        $trip = Trip::factory()->create([
            'user_id' => $user->id,
            'car_id' => $car->id,
            'start_location_id' => $startLocation->id,
            'destination_location_id' => $endLocation->id,
            'dict_id' => $travelPurpose->id
        ]);

        $this->assertTrue($travelPurpose->trips->contains($trip));
        $this->assertInstanceOf(Trip::class, $travelPurpose->trips->first());
    }

    #[Test]
    public function it_casts_is_system_to_boolean()
    {
        $travelPurpose = TravelPurposeDictionary::factory()->create(['is_system' => 1]);
        $this->assertIsBool($travelPurpose->is_system);
        $this->assertTrue($travelPurpose->is_system);
    }

    public function tearDown(): void
    {
        // Görgess vissza minden tranzakciót
        \DB::rollBack();
        parent::tearDown();
    }
}
