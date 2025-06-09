<?php

namespace Tests\Feature\RoadRecord;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use App\Models\Role;
use App\Models\Car;
use App\Models\Trip;
use App\Models\Location;
use PHPUnit\Framework\Attributes\Test;

class TripTest extends TestCase
{
    use RefreshDatabase;

    protected $adminUser;
    protected $regularUser;
    protected $car;
    protected $startLocation;
    protected $endLocation;

    public function setUp(): void
    {
        parent::setUp();

        // Create roles
        $adminRole = Role::create([
            'slug' => 'admin',
            'title' => 'Adminisztrátor',
            'description' => 'Admin jogosultságok'
        ]);

        $employeeRole = Role::create([
            'slug' => 'employee',
            'title' => 'Alkalmazott',
            'description' => 'Korlátozott jogosultságok'
        ]);

        // Create users with specific roles
        $this->adminUser = User::factory()->create([
            'role_id' => $adminRole->id,
        ]);

        $this->regularUser = User::factory()->create([
            'role_id' => $employeeRole->id,
        ]);

        // Create a car
        $this->car = Car::create([
            'user_id' => $this->regularUser->id,
            'car_type' => 'sedan',
            'license_plate' => 'ABC-123',
            'manufacturer' => 'Toyota',
            'model' => 'Corolla',
            'fuel_type' => 'petrol',
            'standard_consumption' => 7.5,
            'capacity' => 1800,
            'fuel_tank_capacity' => 55,
        ]);

        // Create locations
        $this->startLocation = Location::create([
            'name' => 'Office',
            'location_type' => 'telephely',
            'is_headquarter' => true,
        ]);

        $this->endLocation = Location::create([
            'name' => 'Client Site',
            'location_type' => 'partner',
            'is_headquarter' => false,
        ]);
    }

    #[Test]
    public function test_user_can_list_trips()
    {
        $travelPurpose = \App\Models\TravelPurposeDictionary::factory()->create();;
        // Create some test trips
        Trip::create([
            'car_id' => $this->car->id,
            'user_id' => $this->regularUser->id,
            'start_location_id' => $this->startLocation->id,
            'destination_location_id' => $this->endLocation->id,
            'start_time' => now()->subHours(2),
            'end_time' => now()->subHour(),
            'planned_distance' => 25.5,
            'actual_distance' => 26.2,
            'start_odometer' => 10000,
            'end_odometer' => 10026,
            'planned_duration' => '01:00:00',
            'actual_duration' => '01:05:00',
            'dict_id' => $travelPurpose->id
        ]);

        Trip::create([
            'car_id' => $this->car->id,
            'user_id' => $this->regularUser->id,
            'start_location_id' => $this->endLocation->id,
            'destination_location_id' => $this->startLocation->id,
            'start_time' => now()->subMinutes(30),
            'end_time' => now(),
            'planned_distance' => 25.5,
            'actual_distance' => 24.8,
            'start_odometer' => 10026,
            'end_odometer' => 10051,
            'planned_duration' => '01:00:00',
            'actual_duration' => '00:30:00',
            'dict_id' => $travelPurpose->id
        ]);

        $response = $this->actingAs($this->regularUser)
            ->getJson('/api/trips');

        $response->assertStatus(200)
            ->assertJsonCount(2);
    }

    #[Test]
    public function test_index_filtering_and_sorting_by_start_date()
    {
        $trip1 = Trip::create([
            'car_id' => $this->car->id,
            'user_id' => $this->regularUser->id,
            'start_location_id' => $this->startLocation->id,
            'destination_location_id' => $this->endLocation->id,
            'start_time' => now()->subDays(2),
            'end_time' => now()->subDays(2)->addHours(1),
        ]);

        $trip2 = Trip::create([
            'car_id' => $this->car->id,
            'user_id' => $this->regularUser->id,
            'start_location_id' => $this->startLocation->id,
            'destination_location_id' => $this->endLocation->id,
            'start_time' => now()->subDay(),
            'end_time' => now()->subDay()->addHours(1),
        ]);

        $response = $this->actingAs($this->regularUser)->getJson('/api/trips?start_date=' . now()->subDays(1)->toDateString());

        $response->assertStatus(200)->assertJsonFragment(['id' => $trip2->id]);
    }

    #[Test]
    public function test_user_can_create_trip()
    {
        $tripData = [
            'car_id' => $this->car->id,
            'start_location_id' => $this->startLocation->id,
            'destination_location_id' => $this->endLocation->id,
            'start_time' => now()->subHours(2)->format('Y-m-d H:i:s'),
            'end_time' => now()->subHour()->format('Y-m-d H:i:s'),
            'planned_distance' => 25.5,
            'actual_distance' => 26.2,
            'start_odometer' => 10000,
            'end_odometer' => 10026,
            'planned_duration' => '01:00:00',
            'actual_duration' => '01:05:00',
        ];

        $response = $this->actingAs($this->regularUser)
            ->postJson('/api/trips', $tripData);

        $response->assertStatus(201)
            ->assertJson([
                'message' => 'Az út sikeresen létrehozva.'
            ]);

        $this->assertDatabaseHas('trips', [
            'car_id' => $this->car->id,
            'user_id' => $this->regularUser->id,
            'start_location_id' => $this->startLocation->id,
            'destination_location_id' => $this->endLocation->id
        ]);
    }

    #[Test]
    public function test_validation_rules_for_trip_creation()
    {
        $invalidTripData = [
            // Missing car_id
            'start_location_id' => $this->startLocation->id,
            'destination_location_id' => $this->startLocation->id, // Same as start (should be different)
            'start_time' => null, // Missing required field
            'end_time' => now()->addHour()->format('Y-m-d H:i:s'), // Future time (should be in the past)
            'planned_distance' => -5, // Negative value
            'start_odometer' => 10000,
            'end_odometer' => 9000, // Less than start_odometer
        ];

        $response = $this->actingAs($this->regularUser)
            ->postJson('/api/trips', $invalidTripData);

        $response->assertStatus(422)
            ->assertJsonValidationErrors([
                'car_id',
                'start_location_id',
                'start_time',
            ]);
    }

    #[Test]
    public function test_admin_can_create_trip_for_other_user()
    {
        $travelPurpose = \App\Models\TravelPurposeDictionary::factory()->create();;

        $tripData = [
            'car_id' => $this->car->id,
            'user_id' => $this->regularUser->id,
            'start_location_id' => $this->startLocation->id,
            'destination_location_id' => $this->endLocation->id,
            'start_time' => now()->subHours(2)->format('Y-m-d H:i:s'),
            'end_time' => now()->subHour()->format('Y-m-d H:i:s'),
            'planned_distance' => 20.5,
            'actual_distance' => 21.3,
            'start_odometer' => 10000,
            'end_odometer' => 10021,
            'planned_duration' => '01:00:00',
            'actual_duration' => '01:10:00',
            'dict_id' => $travelPurpose->id
        ];

        $response = $this->actingAs($this->adminUser)
            ->postJson('/api/trips', $tripData);

        $response->assertStatus(201)
            ->assertJsonFragment(['message' => 'Az út sikeresen létrehozva.']);

        $this->assertDatabaseHas('trips', [
            'user_id' => $this->regularUser->id,
            'dict_id' => $travelPurpose->id
        ]);
    }

    #[Test]
    public function test_trip_show_returns_not_found_for_invalid_id()
    {
        $response = $this->actingAs($this->regularUser)->getJson('/api/trips/999999');

        $response->assertStatus(404)
            ->assertJson(['message' => 'A megadott azonosítójú (ID: 999999) út nem található.']);
    }

    #[Test]
    public function test_user_can_view_specific_trip()
    {
        $trip = Trip::create([
            'car_id' => $this->car->id,
            'user_id' => $this->regularUser->id,
            'start_location_id' => $this->startLocation->id,
            'destination_location_id' => $this->endLocation->id,
            'start_time' => now()->subHours(2),
            'end_time' => now()->subHour(),
            'planned_distance' => 25.5,
            'actual_distance' => 26.2,
            'start_odometer' => 10000,
            'end_odometer' => 10026,
            'planned_duration' => '01:00:00',
            'actual_duration' => '01:05:00',
        ]);

        $response = $this->actingAs($this->regularUser)
            ->getJson("/api/trips/{$trip->id}?include=car,startLocation,destinationLocation");

        $response->assertStatus(200)
            ->assertJsonFragment([
                'id' => $trip->id,
                'car_id' => $this->car->id,
                'start_location_id' => $this->startLocation->id,
                'destination_location_id' => $this->endLocation->id
            ]);

        // Check if relations are included
        $response->assertJsonStructure([
            'car',
            'start_location',
            'destination_location'
        ]);
    }

    #[Test]
    public function test_user_can_update_own_trip()
    {
        $trip = Trip::create([
            'car_id' => $this->car->id,
            'user_id' => $this->regularUser->id,
            'start_location_id' => $this->startLocation->id,
            'destination_location_id' => $this->endLocation->id,
            'start_time' => now()->subHours(2),
            'end_time' => now()->subHour(),
            'planned_distance' => 25.5,
            'actual_distance' => 26.2,
            'start_odometer' => 10000,
            'end_odometer' => 10026,
            'planned_duration' => '01:00:00',
            'actual_duration' => '01:05:00',
        ]);

        $updateData = [
            'actual_distance' => 27.5,
            'end_odometer' => 10028,
            'actual_duration' => '01:10:00',
        ];

        $response = $this->actingAs($this->regularUser)
            ->putJson("/api/trips/{$trip->id}", $updateData);

        $response->assertStatus(200)
            ->assertJson([
                'message' => 'Az út adatai sikeresen frissítve lettek.'
            ]);

        $this->assertDatabaseHas('trips', [
            'id' => $trip->id,
            'actual_distance' => 27.5,
            'end_odometer' => 10028
        ]);
    }

    #[Test]
    public function test_user_cannot_update_another_users_trip()
    {
        $anotherUser = User::factory()->create();

        $trip = Trip::create([
            'car_id' => $this->car->id,
            'user_id' => $anotherUser->id, // Different user
            'start_location_id' => $this->startLocation->id,
            'destination_location_id' => $this->endLocation->id,
            'start_time' => now()->subHours(2),
            'end_time' => now()->subHour(),
            'planned_distance' => 25.5,
            'actual_distance' => 26.2,
            'start_odometer' => 10000,
            'end_odometer' => 10026,
            'planned_duration' => '01:00:00',
            'actual_duration' => '01:05:00',
        ]);

        $updateData = [
            'actual_distance' => 27.5,
        ];

        $response = $this->actingAs($this->regularUser)
            ->putJson("/api/trips/{$trip->id}", $updateData);

        $response->assertStatus(403)
            ->assertJson([
                'message' => 'Nincs jogosultsága módosítani ezt az utat.'
            ]);
    }

    #[Test]
    public function test_admin_can_update_any_trip()
    {
        $trip = Trip::create([
            'car_id' => $this->car->id,
            'user_id' => $this->regularUser->id,
            'start_location_id' => $this->startLocation->id,
            'destination_location_id' => $this->endLocation->id,
            'start_time' => now()->subHours(2),
            'end_time' => now()->subHour(),
            'planned_distance' => 25.5,
            'actual_distance' => 26.2,
            'start_odometer' => 10000,
            'end_odometer' => 10026,
            'planned_duration' => '01:00:00',
            'actual_duration' => '01:05:00',
        ]);

        $updateData = [
            'actual_distance' => 28.0,
        ];

        $response = $this->actingAs($this->adminUser)
            ->putJson("/api/trips/{$trip->id}", $updateData);

        $response->assertStatus(200);

        $this->assertDatabaseHas('trips', [
            'id' => $trip->id,
            'actual_distance' => 28.0
        ]);
    }

    #[Test]
    public function test_trip_update_fails_if_same_start_and_destination()
    {
        $trip = Trip::create([
            'car_id' => $this->car->id,
            'user_id' => $this->regularUser->id,
            'start_location_id' => $this->startLocation->id,
            'destination_location_id' => $this->endLocation->id,
            'start_time' => now()->subHours(2),
            'end_time' => now()->subHour(),
        ]);

        $response = $this->actingAs($this->regularUser)
            ->putJson("/api/trips/{$trip->id}", [
                'destination_location_id' => $this->startLocation->id
            ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['destination_location_id']);
    }

    #[Test]
    public function test_user_can_delete_own_trip()
    {
        $trip = Trip::create([
            'car_id' => $this->car->id,
            'user_id' => $this->regularUser->id,
            'start_location_id' => $this->startLocation->id,
            'destination_location_id' => $this->endLocation->id,
            'start_time' => now()->subHours(2),
            'end_time' => now()->subHour(),
            'planned_distance' => 25.5,
            'actual_distance' => 26.2,
            'start_odometer' => 10000,
            'end_odometer' => 10026,
            'planned_duration' => '01:00:00',
            'actual_duration' => '01:05:00',
        ]);

        $response = $this->actingAs($this->regularUser)
            ->deleteJson("/api/trips/{$trip->id}");

        $response->assertStatus(200);

        $this->assertDatabaseMissing('trips', [
            'id' => $trip->id
        ]);
    }

    #[Test]
    public function test_user_cannot_delete_another_users_trip()
    {
        $anotherUser = User::factory()->create();

        $trip = Trip::create([
            'car_id' => $this->car->id,
            'user_id' => $anotherUser->id, // Different user
            'start_location_id' => $this->startLocation->id,
            'destination_location_id' => $this->endLocation->id,
            'start_time' => now()->subHours(2),
            'end_time' => now()->subHour(),
            'planned_distance' => 25.5,
            'actual_distance' => 26.2,
            'start_odometer' => 10000,
            'end_odometer' => 10026,
            'planned_duration' => '01:00:00',
            'actual_duration' => '01:05:00',
        ]);

        $response = $this->actingAs($this->regularUser)
            ->deleteJson("/api/trips/{$trip->id}");

        $response->assertStatus(403);

        $this->assertDatabaseHas('trips', [
            'id' => $trip->id
        ]);
    }

    #[Test]
    public function test_admin_can_delete_any_trip()
    {
        $trip = Trip::create([
            'car_id' => $this->car->id,
            'user_id' => $this->regularUser->id,
            'start_location_id' => $this->startLocation->id,
            'destination_location_id' => $this->endLocation->id,
            'start_time' => now()->subHours(2),
            'end_time' => now()->subHour(),
            'planned_distance' => 25.5,
            'actual_distance' => 26.2,
            'start_odometer' => 10000,
            'end_odometer' => 10026,
            'planned_duration' => '01:00:00',
            'actual_duration' => '01:05:00',
        ]);

        $response = $this->actingAs($this->adminUser)
            ->deleteJson("/api/trips/{$trip->id}");

        $response->assertStatus(200);

        $this->assertDatabaseMissing('trips', [
            'id' => $trip->id
        ]);
    }
}
