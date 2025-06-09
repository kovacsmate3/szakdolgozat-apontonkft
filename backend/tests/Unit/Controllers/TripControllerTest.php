<?php

namespace Tests\Unit\Controllers;

use App\Http\Controllers\Api\RoadRecord\TripController;
use App\Models\Car;
use App\Models\Location;
use App\Models\Trip;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Mockery;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class TripControllerTest extends TestCase
{
    use RefreshDatabase;

    protected $tripController;

    public function setUp(): void
    {
        parent::setUp();
        if (\DB::transactionLevel() > 0) {
            \DB::rollBack();
        }
        $this->tripController = new TripController();
    }

    #[Test]
    public function index_returns_all_trips()
    {
        // Create some test data
        $user = User::factory()->create();
        $car = Car::factory()->create(['user_id' => $user->id]);
        $startLocation = Location::factory()->create();
        $endLocation = Location::factory()->create();

        $trips = Trip::factory()->count(3)->create([
            'user_id' => $user->id,
            'car_id' => $car->id,
            'start_location_id' => $startLocation->id,
            'destination_location_id' => $endLocation->id
        ]);

        // Create a request
        $request = new Request();

        // Call the index method
        $response = $this->tripController->index($request);

        // Assert the response has HTTP 200 status
        $this->assertEquals(Response::HTTP_OK, $response->getStatusCode());

        // Convert response to array and check it contains all trips
        $responseData = json_decode($response->getContent(), true);
        $this->assertCount(3, $responseData);
    }

    #[Test]
    public function index_filters_by_car_id()
    {
        // Create a user
        $user = User::factory()->create();

        // Create two cars
        $car1 = Car::factory()->create(['user_id' => $user->id]);
        $car2 = Car::factory()->create(['user_id' => $user->id]);

        // Create locations
        $startLocation = Location::factory()->create();
        $endLocation = Location::factory()->create();

        // Create trips for each car
        Trip::factory()->create([
            'user_id' => $user->id,
            'car_id' => $car1->id,
            'start_location_id' => $startLocation->id,
            'destination_location_id' => $endLocation->id
        ]);

        Trip::factory()->count(2)->create([
            'user_id' => $user->id,
            'car_id' => $car2->id,
            'start_location_id' => $startLocation->id,
            'destination_location_id' => $endLocation->id
        ]);

        // Create a request with car_id filter
        $request = new Request(['car_id' => $car2->id]);

        // Call the index method
        $response = $this->tripController->index($request);

        // Assert the response has HTTP 200 status
        $this->assertEquals(Response::HTTP_OK, $response->getStatusCode());

        // Convert response to array and check it contains only car2's trips
        $responseData = json_decode($response->getContent(), true);
        $this->assertCount(2, $responseData);
        $this->assertEquals($car2->id, $responseData[0]['car_id']);
        $this->assertEquals($car2->id, $responseData[1]['car_id']);
    }

    #[Test]
    public function store_creates_new_trip_and_returns_success()
    {
        // Create test data
        $user = User::factory()->create();
        $car = Car::factory()->create(['user_id' => $user->id]);
        $startLocation = Location::factory()->create();
        $endLocation = Location::factory()->create();

        // Mock Auth facade
        Auth::shouldReceive('user')->andReturn($user);
        Auth::shouldReceive('id')->andReturn($user->id);

        // Create request data
        $tripData = [
            'car_id' => $car->id,
            'start_location_id' => $startLocation->id,
            'destination_location_id' => $endLocation->id,
            'start_time' => now()->subHour()->format('Y-m-d H:i:s'),
            'end_time' => now()->format('Y-m-d H:i:s'),
            'planned_distance' => 25.0,
            'actual_distance' => 26.5,
            'start_odometer' => 10000,
            'end_odometer' => 10026,
            'planned_duration' => '01:00:00',
            'actual_duration' => '01:05:00',
        ];

        // Create a request
        $request = new Request($tripData);
        $request->setMethod('POST');

        // Call the store method
        $response = $this->tripController->store($request);

        // Assert the response has HTTP 201 status
        $this->assertEquals(Response::HTTP_CREATED, $response->getStatusCode());

        // Assert the response contains the success message
        $responseData = json_decode($response->getContent(), true);
        $this->assertEquals('Az út sikeresen létrehozva.', $responseData['message']);

        // Assert the trip was created in the database
        $this->assertDatabaseHas('trips', [
            'car_id' => $car->id,
            'user_id' => $user->id,
            'start_location_id' => $startLocation->id,
            'destination_location_id' => $endLocation->id,
        ]);
    }

    #[Test]
    public function show_returns_trip_by_id()
    {
        // Create test data
        $user = User::factory()->create();
        $car = Car::factory()->create(['user_id' => $user->id]);
        $startLocation = Location::factory()->create();
        $endLocation = Location::factory()->create();

        $trip = Trip::factory()->create([
            'user_id' => $user->id,
            'car_id' => $car->id,
            'start_location_id' => $startLocation->id,
            'destination_location_id' => $endLocation->id,
            'planned_distance' => 25.0,
            'actual_distance' => 26.5,
        ]);

        // Create a request
        $request = new Request();

        // Call the show method
        $response = $this->tripController->show($request, $trip->id);

        // Assert the response has HTTP 200 status
        $this->assertEquals(Response::HTTP_OK, $response->getStatusCode());

        // Assert the response contains the trip data
        $responseData = json_decode($response->getContent(), true);
        $this->assertEquals($trip->id, $responseData['id']);
        $this->assertEquals($car->id, $responseData['car_id']);
        $this->assertEquals(25.0, $responseData['planned_distance']);
        $this->assertEquals(26.5, $responseData['actual_distance']);
    }

    #[Test]
    public function show_returns_not_found_for_invalid_id()
    {
        // Create a request
        $request = new Request();

        // Call the show method with a non-existent ID
        $response = $this->tripController->show($request, 9999);

        // Assert the response has HTTP 404 status
        $this->assertEquals(Response::HTTP_NOT_FOUND, $response->getStatusCode());

        // Assert the response contains the error message
        $responseData = json_decode($response->getContent(), true);
        $this->assertEquals('A megadott azonosítójú (ID: 9999) út nem található.', $responseData['message']);
    }

    #[Test]
    public function update_modifies_trip_and_returns_success()
    {
        // Create test data
        $user = User::factory()->create();
        $car = Car::factory()->create(['user_id' => $user->id]);
        $startLocation = Location::factory()->create();
        $endLocation = Location::factory()->create();

        $trip = Trip::factory()->create([
            'user_id' => $user->id,
            'car_id' => $car->id,
            'start_location_id' => $startLocation->id,
            'destination_location_id' => $endLocation->id,
            'planned_distance' => 25.0,
            'actual_distance' => 26.5,
            'start_odometer' => 10000, // Add start odometer
            'end_odometer' => 10026,   // Existing end odometer
        ]);

        // Mock Auth facade
        Auth::shouldReceive('user')->andReturn($user);
        Auth::shouldReceive('id')->andReturn($user->id);

        // Create a role mock with slug
        $role = new \stdClass();
        $role->slug = 'employee';
        $user->role = $role;

        // Create update data
        $updateData = [
            'actual_distance' => 27.5,
            'end_odometer' => 10028,
        ];

        // Create a request
        $request = new Request($updateData);
        $request->setMethod('PUT');

        // Call the update method
        $response = $this->tripController->update($request, $trip->id);

        // Assert the response has HTTP 200 status
        $this->assertEquals(Response::HTTP_OK, $response->getStatusCode());

        // Assert the response contains the success message
        $responseData = json_decode($response->getContent(), true);
        $this->assertEquals('Az út adatai sikeresen frissítve lettek.', $responseData['message']);

        // Assert the trip was updated in the database
        $this->assertDatabaseHas('trips', [
            'id' => $trip->id,
            'actual_distance' => 27.5,
            'end_odometer' => 10028
        ]);
    }

    #[Test]
    public function update_returns_not_found_for_invalid_id()
    {
        // Create a request
        $request = new Request(['actual_distance' => 27.5]);
        $request->setMethod('PUT');

        // Mock Auth facade
        $user = User::factory()->create();
        Auth::shouldReceive('user')->andReturn($user);

        // Create a role mock with slug
        $role = new \stdClass();
        $role->slug = 'employee';
        $user->role = $role;

        // Call the update method with a non-existent ID
        $response = $this->tripController->update($request, 9999);

        // Assert the response has HTTP 404 status
        $this->assertEquals(Response::HTTP_NOT_FOUND, $response->getStatusCode());

        // Assert the response contains the error message
        $responseData = json_decode($response->getContent(), true);
        $this->assertEquals('A megadott azonosítójú (ID: 9999) út nem található.', $responseData['message']);
    }

    #[Test]
    public function destroy_deletes_trip_and_returns_success()
    {
        // Create test data
        $user = User::factory()->create();
        $car = Car::factory()->create(['user_id' => $user->id]);
        $startLocation = Location::factory()->create(['name' => 'Start']);
        $endLocation = Location::factory()->create(['name' => 'End']);

        $trip = Trip::factory()->create([
            'user_id' => $user->id,
            'car_id' => $car->id,
            'start_location_id' => $startLocation->id,
            'destination_location_id' => $endLocation->id,
            'start_time' => now()->subHour(),
        ]);

        // Mock Auth facade
        Auth::shouldReceive('user')->andReturn($user);
        Auth::shouldReceive('id')->andReturn($user->id);

        // Create a role mock with slug
        $role = new \stdClass();
        $role->slug = 'employee';
        $user->role = $role;

        // Call the destroy method
        $response = $this->tripController->destroy($trip->id);

        // Assert the response has HTTP 200 status
        $this->assertEquals(Response::HTTP_OK, $response->getStatusCode());

        // Assert the response contains the success message
        $responseData = json_decode($response->getContent(), true);
        $this->assertStringContainsString('sikeresen törölve', $responseData['message']);

        // Assert the trip was deleted from the database
        $this->assertDatabaseMissing('trips', ['id' => $trip->id]);
    }

    #[Test]
    public function destroy_returns_not_found_for_invalid_id()
    {
        // Mock Auth facade
        $user = User::factory()->create();
        Auth::shouldReceive('user')->andReturn($user);

        // Create a role mock with slug
        $role = new \stdClass();
        $role->slug = 'employee';
        $user->role = $role;

        // Call the destroy method with a non-existent ID
        $response = $this->tripController->destroy(9999);

        // Assert the response has HTTP 404 status
        $this->assertEquals(Response::HTTP_NOT_FOUND, $response->getStatusCode());

        // Assert the response contains the error message
        $responseData = json_decode($response->getContent(), true);
        $this->assertEquals('A megadott azonosítójú (ID: 9999) út nem található.', $responseData['message']);
    }

    public function tearDown(): void
    {
        if (\DB::transactionLevel() > 0) {
            \DB::rollBack();
        }
        Mockery::close();
        parent::tearDown();
    }
}
