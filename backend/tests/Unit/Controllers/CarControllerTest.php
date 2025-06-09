<?php

namespace Tests\Unit\Controllers;

use App\Http\Controllers\Api\RoadRecord\CarController;
use App\Models\Car;
use App\Models\FuelExpense;
use App\Models\Location;
use App\Models\Trip;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;


class CarControllerTest extends TestCase
{
    use RefreshDatabase;

    protected $carController;

    public function setUp(): void
    {
        parent::setUp();
        if (\DB::transactionLevel() > 0) {
            \DB::rollBack();
        }
        $this->carController = new CarController();
    }

    #[Test]
    public function index_returns_all_cars_with_users()
    {
        // Create some test data
        $user = User::factory()->create();
        $cars = Car::factory()->count(3)->create(['user_id' => $user->id]);

        // Call the index method
        $response = $this->carController->index();

        // Assert the response has HTTP 200 status
        $this->assertEquals(Response::HTTP_OK, $response->getStatusCode());

        // Convert response to array and check it contains all cars
        $responseData = json_decode($response->getContent(), true);
        $this->assertCount(3, $responseData);
    }

    #[Test]
    public function store_creates_new_car_and_returns_success()
    {
        // Create a user
        $user = User::factory()->create();

        // Create request data
        $requestData = [
            'user_id' => $user->id,
            'car_type' => 'sedan',
            'license_plate' => 'ABC-123',
            'manufacturer' => 'Toyota',
            'model' => 'Corolla',
            'fuel_type' => 'petrol',
            'standard_consumption' => 7.5,
            'capacity' => 1800,
            'fuel_tank_capacity' => 55,
        ];

        // Create a request
        $request = new Request($requestData);
        $request->setMethod('POST');

        // Call the store method
        $response = $this->carController->store($request);

        // Assert the response has HTTP 201 status
        $this->assertEquals(Response::HTTP_CREATED, $response->getStatusCode());

        // Assert the response contains the success message
        $responseData = json_decode($response->getContent(), true);
        $this->assertEquals('A jármű sikeresen létrehozva.', $responseData['message']);

        // Assert the car was created in the database
        $this->assertDatabaseHas('cars', [
            'user_id' => $user->id,
            'license_plate' => 'ABC-123',
        ]);
    }

    #[Test]
    public function show_returns_car_by_id()
    {
        // Create a user and car
        $user = User::factory()->create();
        $car = Car::factory()->create([
            'user_id' => $user->id,
            'license_plate' => 'ABC-123'
        ]);

        // Create a request
        $request = new Request();

        // Call the show method
        $response = $this->carController->show($request, $car->id);

        // Assert the response has HTTP 200 status
        $this->assertEquals(Response::HTTP_OK, $response->getStatusCode());

        // Assert the response contains the car data
        $responseData = json_decode($response->getContent(), true);
        $this->assertEquals($car->id, $responseData['id']);
        $this->assertEquals('ABC-123', $responseData['license_plate']);
    }

    #[Test]
    public function show_returns_car_with_specified_includes()
    {
        // Create a user and car
        $user = User::factory()->create();
        $car = Car::factory()->create(['user_id' => $user->id]);

        // Create trips for this car
        $startLocation = Location::factory()->create();
        $endLocation = Location::factory()->create();
        $trips = Trip::factory()->count(2)->create([
            'car_id' => $car->id,
            'user_id' => $user->id,
            'start_location_id' => $startLocation->id,
            'destination_location_id' => $endLocation->id
        ]);

        // Create a request with includes
        $request = new Request(['include' => 'user,trips']);

        // Call the show method
        $response = $this->carController->show($request, $car->id);

        // Assert the response has HTTP 200 status
        $this->assertEquals(Response::HTTP_OK, $response->getStatusCode());

        // Convert response to array
        $responseData = json_decode($response->getContent(), true);

        // Assert the response includes the user and trips
        $this->assertArrayHasKey('user', $responseData);
        $this->assertArrayHasKey('trips', $responseData);
        $this->assertCount(2, $responseData['trips']);
    }

    #[Test]
    public function show_returns_not_found_for_invalid_id()
    {
        // Create a request
        $request = new Request();

        // Call the show method with a non-existent ID
        $response = $this->carController->show($request, 9999);

        // Assert the response has HTTP 404 status
        $this->assertEquals(Response::HTTP_NOT_FOUND, $response->getStatusCode());

        // Assert the response contains the error message
        $responseData = json_decode($response->getContent(), true);
        $this->assertEquals('A megadott azonosítójú (ID: 9999) jármű nem található.', $responseData['message']);
    }

    #[Test]
    public function update_modifies_car_and_returns_success()
    {
        // Create a user and car
        $user = User::factory()->create();
        $car = Car::factory()->create(['user_id' => $user->id]);

        // Create update data
        $updateData = [
            'manufacturer' => 'Honda',
            'model' => 'Civic',
            'standard_consumption' => 6.5
        ];

        // Create a request
        $request = new Request($updateData);
        $request->setMethod('PUT');

        // Call the update method
        $response = $this->carController->update($request, $car->id);

        // Assert the response has HTTP 200 status
        $this->assertEquals(Response::HTTP_OK, $response->getStatusCode());

        // Assert the response contains the success message
        $responseData = json_decode($response->getContent(), true);
        $this->assertEquals('A jármű adatai sikeresen frissítve lettek.', $responseData['message']);

        // Assert the car was updated in the database
        $this->assertDatabaseHas('cars', [
            'id' => $car->id,
            'manufacturer' => 'Honda',
            'model' => 'Civic',
            'standard_consumption' => 6.5
        ]);
    }

    #[Test]
    public function update_returns_not_found_for_invalid_id()
    {
        // Create a request
        $request = new Request(['manufacturer' => 'Honda']);
        $request->setMethod('PUT');

        // Call the update method with a non-existent ID
        $response = $this->carController->update($request, 9999);

        // Assert the response has HTTP 404 status
        $this->assertEquals(Response::HTTP_NOT_FOUND, $response->getStatusCode());

        // Assert the response contains the error message
        $responseData = json_decode($response->getContent(), true);
        $this->assertEquals('A megadott azonosítójú (ID: 9999) jármű nem található.', $responseData['message']);
    }

    #[Test]
    public function destroy_deletes_car_and_returns_success()
    {
        // Create a user and car
        $user = User::factory()->create();
        $car = Car::factory()->create([
            'user_id' => $user->id,
            'manufacturer' => 'Toyota',
            'model' => 'Corolla',
            'license_plate' => 'ABC-123'
        ]);

        // Call the destroy method
        $response = $this->carController->destroy($car->id);

        // Assert the response has HTTP 200 status
        $this->assertEquals(Response::HTTP_OK, $response->getStatusCode());

        // Assert the response contains the success message
        $responseData = json_decode($response->getContent(), true);
        $this->assertEquals('Toyota Corolla (ABC-123) jármű sikeresen törölve.', $responseData['message']);

        // Assert the car was deleted from the database
        $this->assertDatabaseMissing('cars', ['id' => $car->id]);
    }

    #[Test]
    public function destroy_returns_not_found_for_invalid_id()
    {
        // Call the destroy method with a non-existent ID
        $response = $this->carController->destroy(9999);

        // Assert the response has HTTP 404 status
        $this->assertEquals(Response::HTTP_NOT_FOUND, $response->getStatusCode());

        // Assert the response contains the error message
        $responseData = json_decode($response->getContent(), true);
        $this->assertEquals('A megadott azonosítójú (ID: 9999) jármű nem található.', $responseData['message']);
    }

    #[Test]
    public function destroy_returns_forbidden_if_car_has_related_records()
    {
        // Create a user and car
        $user = User::factory()->create();
        $car = Car::factory()->create(['user_id' => $user->id]);

        // Create a location
        $location = Location::factory()->create(['location_type' => 'töltőállomás']);

        // Create a fuel expense for this car
        $fuelExpense = FuelExpense::factory()->create([
            'car_id' => $car->id,
            'user_id' => $user->id,
            'location_id' => $location->id
        ]);

        // Call the destroy method
        $response = $this->carController->destroy($car->id);

        // Assert the response has HTTP 403 status
        $this->assertEquals(Response::HTTP_FORBIDDEN, $response->getStatusCode());

        // Assert the response contains the error message
        $responseData = json_decode($response->getContent(), true);
        $this->assertEquals('Ez a jármű tankolásokhoz vagy utazásokhoz van rendelve, ezért nem törölhető.', $responseData['message']);

        // Assert the car was not deleted from the database
        $this->assertDatabaseHas('cars', ['id' => $car->id]);
    }

    public function tearDown(): void
    {
        if (\DB::transactionLevel() > 0) {
            \DB::rollBack();
        }
        parent::tearDown();
    }
}
