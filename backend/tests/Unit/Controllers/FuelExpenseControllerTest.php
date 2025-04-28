<?php

namespace Tests\Unit\Controllers;

use App\Http\Controllers\Api\RoadRecord\FuelExpenseController;
use App\Models\Car;
use App\Models\FuelExpense;
use App\Models\Location;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Mockery;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class FuelExpenseControllerTest extends TestCase
{
    use RefreshDatabase;

    protected $fuelExpenseController;

    public function setUp(): void
    {
        parent::setUp();
        if (\DB::transactionLevel() > 0) {
            \DB::rollBack();
        }
        $this->fuelExpenseController = new FuelExpenseController();
    }

    #[Test]
    public function index_returns_all_fuel_expenses()
    {
        // Create test data
        $user = User::factory()->create();
        $car = Car::factory()->create(['user_id' => $user->id]);
        $location = Location::factory()->create(['location_type' => 'töltőállomás']);

        $fuelExpenses = FuelExpense::factory()->count(3)->create([
            'user_id' => $user->id,
            'car_id' => $car->id,
            'location_id' => $location->id
        ]);

        // Create a request
        $request = new Request();

        // Call the index method
        $response = $this->fuelExpenseController->index($request);

        // Assert the response has HTTP 200 status
        $this->assertEquals(Response::HTTP_OK, $response->getStatusCode());

        // Convert response to array and check it contains all fuel expenses
        $responseData = json_decode($response->getContent(), true);
        $this->assertCount(3, $responseData);
    }

    #[Test]
    public function index_filters_by_car_id()
    {
        // Create test data
        $user = User::factory()->create();

        // Create two cars
        $car1 = Car::factory()->create(['user_id' => $user->id]);
        $car2 = Car::factory()->create(['user_id' => $user->id]);

        // Create a location
        $location = Location::factory()->create(['location_type' => 'töltőállomás']);

        // Create fuel expenses for each car
        FuelExpense::factory()->create([
            'user_id' => $user->id,
            'car_id' => $car1->id,
            'location_id' => $location->id
        ]);

        FuelExpense::factory()->count(2)->create([
            'user_id' => $user->id,
            'car_id' => $car2->id,
            'location_id' => $location->id
        ]);

        // Create a request with car_id filter
        $request = new Request(['car_id' => $car2->id]);

        // Call the index method
        $response = $this->fuelExpenseController->index($request);

        // Assert the response has HTTP 200 status
        $this->assertEquals(Response::HTTP_OK, $response->getStatusCode());

        // Convert response to array and check it contains only car2's fuel expenses
        $responseData = json_decode($response->getContent(), true);
        $this->assertCount(2, $responseData);
        $this->assertEquals($car2->id, $responseData[0]['car_id']);
        $this->assertEquals($car2->id, $responseData[1]['car_id']);
    }

    #[Test]
    public function index_filters_by_date_range()
    {
        // Create test data
        $user = User::factory()->create();
        $car = Car::factory()->create(['user_id' => $user->id]);
        $location = Location::factory()->create(['location_type' => 'töltőállomás']);

        // Create fuel expenses with different dates
        FuelExpense::factory()->create([
            'user_id' => $user->id,
            'car_id' => $car->id,
            'location_id' => $location->id,
            'expense_date' => now()->subDays(30)
        ]);

        FuelExpense::factory()->create([
            'user_id' => $user->id,
            'car_id' => $car->id,
            'location_id' => $location->id,
            'expense_date' => now()->subDays(15)
        ]);

        FuelExpense::factory()->create([
            'user_id' => $user->id,
            'car_id' => $car->id,
            'location_id' => $location->id,
            'expense_date' => now()->subDays(5)
        ]);

        // Create a request with date range filter
        $request = new Request([
            'from_date' => now()->subDays(20)->format('Y-m-d'),
            'to_date' => now()->subDays(2)->format('Y-m-d')
        ]);

        // Call the index method
        $response = $this->fuelExpenseController->index($request);

        // Assert the response has HTTP 200 status
        $this->assertEquals(Response::HTTP_OK, $response->getStatusCode());

        // Convert response to array and check it contains only expenses in the date range
        $responseData = json_decode($response->getContent(), true);
        $this->assertCount(2, $responseData);
    }

    #[Test]
    public function store_creates_new_fuel_expense_and_returns_success()
    {
        // Create test data
        $user = User::factory()->create();
        $car = Car::factory()->create(['user_id' => $user->id]);
        $location = Location::factory()->create(['location_type' => 'töltőállomás']);

        // Mock Auth facade
        Auth::shouldReceive('user')->andReturn($user);
        Auth::shouldReceive('id')->andReturn($user->id);

        // Create a role mock with slug
        $role = new \stdClass();
        $role->slug = 'employee';
        $user->role = $role;

        // Create request data
        $fuelExpenseData = [
            'car_id' => $car->id,
            'location_id' => $location->id,
            'expense_date' => now()->format('Y-m-d H:i:s'),
            'amount' => 20000,
            'currency' => 'HUF',
            'fuel_quantity' => 30.5,
            'odometer' => 10500,
        ];

        // Create a request
        $request = new Request($fuelExpenseData);
        $request->setMethod('POST');

        // Call the store method
        $response = $this->fuelExpenseController->store($request);

        // Assert the response has HTTP 201 status
        $this->assertEquals(Response::HTTP_CREATED, $response->getStatusCode());

        // Assert the response contains the success message
        $responseData = json_decode($response->getContent(), true);
        $this->assertEquals('A tankolási adat sikeresen létrehozva.', $responseData['message']);

        // Assert the fuel expense was created in the database
        $this->assertDatabaseHas('fuel_expenses', [
            'car_id' => $car->id,
            'user_id' => $user->id,
            'location_id' => $location->id,
            'amount' => 20000,
            'fuel_quantity' => 30.5,
        ]);
    }

    #[Test]
    public function show_returns_fuel_expense_by_id()
    {
        // Create test data
        $user = User::factory()->create();
        $car = Car::factory()->create(['user_id' => $user->id]);
        $location = Location::factory()->create(['location_type' => 'töltőállomás']);

        $fuelExpense = FuelExpense::factory()->create([
            'user_id' => $user->id,
            'car_id' => $car->id,
            'location_id' => $location->id,
            'amount' => 25000,
            'fuel_quantity' => 35.5,
        ]);

        // Call the show method
        $response = $this->fuelExpenseController->show($fuelExpense->id);

        // Assert the response has HTTP 200 status
        $this->assertEquals(Response::HTTP_OK, $response->getStatusCode());

        // Assert the response contains the fuel expense data
        $responseData = json_decode($response->getContent(), true);
        $this->assertEquals($fuelExpense->id, $responseData['id']);
        $this->assertEquals($car->id, $responseData['car_id']);
        $this->assertEquals(25000, $responseData['amount']);
        $this->assertEquals(35.5, $responseData['fuel_quantity']);
    }

    #[Test]
    public function show_returns_not_found_for_invalid_id()
    {
        // Call the show method with a non-existent ID
        $response = $this->fuelExpenseController->show(9999);

        // Assert the response has HTTP 404 status
        $this->assertEquals(Response::HTTP_NOT_FOUND, $response->getStatusCode());

        // Assert the response contains the error message
        $responseData = json_decode($response->getContent(), true);
        $this->assertEquals('A megadott azonosítójú (ID: 9999) tankolási adat nem található.', $responseData['message']);
    }

    #[Test]
    public function update_modifies_fuel_expense_and_returns_success()
    {
        // Create test data
        $user = User::factory()->create();
        $car = Car::factory()->create(['user_id' => $user->id]);
        $location = Location::factory()->create(['location_type' => 'töltőállomás']);

        $fuelExpense = FuelExpense::factory()->create([
            'user_id' => $user->id,
            'car_id' => $car->id,
            'location_id' => $location->id,
            'amount' => 25000,
            'fuel_quantity' => 35.5,
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
            'amount' => 26000,
            'fuel_quantity' => 37.0,
        ];

        // Create a request
        $request = new Request($updateData);
        $request->setMethod('PUT');


        // Call the update method
        $response = $this->fuelExpenseController->update($request, $fuelExpense->id);

        // Assert the response has HTTP 200 status
        $this->assertEquals(Response::HTTP_OK, $response->getStatusCode());

        // Assert the response contains the success message
        $responseData = json_decode($response->getContent(), true);
        $this->assertEquals('A tankolási adat sikeresen frissítve.', $responseData['message']);

        // Assert the fuel expense was updated in the database
        $this->assertDatabaseHas('fuel_expenses', [
            'id' => $fuelExpense->id,
            'amount' => 26000,
            'fuel_quantity' => 37.0,
        ]);
    }

    #[Test]
    public function update_returns_not_found_for_invalid_id()
    {
        // Create a request
        $request = new Request(['amount' => 26000]);
        $request->setMethod('PUT');

        // Mock Auth facade
        $user = User::factory()->create();
        Auth::shouldReceive('user')->andReturn($user);

        // Create a role mock with slug
        $role = new \stdClass();
        $role->slug = 'employee';
        $user->role = $role;

        // Call the update method with a non-existent ID
        $response = $this->fuelExpenseController->update($request, 9999);

        // Assert the response has HTTP 404 status
        $this->assertEquals(Response::HTTP_NOT_FOUND, $response->getStatusCode());

        // Assert the response contains the error message
        $responseData = json_decode($response->getContent(), true);
        $this->assertEquals('A megadott azonosítójú (ID: 9999) tankolási adat nem található.', $responseData['message']);
    }

    #[Test]
    public function destroy_deletes_fuel_expense_and_returns_success()
    {
        // Create test data
        $user = User::factory()->create();
        $car = Car::factory()->create(['user_id' => $user->id]);
        $location = Location::factory()->create(['location_type' => 'töltőállomás']);

        $fuelExpense = FuelExpense::factory()->create([
            'user_id' => $user->id,
            'car_id' => $car->id,
            'location_id' => $location->id,
            'expense_date' => now(),
            'fuel_quantity' => 35.5,
        ]);

        // Mock Auth facade
        Auth::shouldReceive('user')->andReturn($user);
        Auth::shouldReceive('id')->andReturn($user->id);

        // Create a role mock with slug
        $role = new \stdClass();
        $role->slug = 'employee';
        $user->role = $role;

        // Call the destroy method
        $response = $this->fuelExpenseController->destroy($fuelExpense->id);

        // Assert the response has HTTP 200 status
        $this->assertEquals(Response::HTTP_OK, $response->getStatusCode());

        // Assert the response contains the success message
        $responseData = json_decode($response->getContent(), true);
        $this->assertStringContainsString('sikeresen törölve', $responseData['message']);

        // Assert the fuel expense was deleted from the database
        $this->assertDatabaseMissing('fuel_expenses', ['id' => $fuelExpense->id]);
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
        $response = $this->fuelExpenseController->destroy(9999);

        // Assert the response has HTTP 404 status
        $this->assertEquals(Response::HTTP_NOT_FOUND, $response->getStatusCode());

        // Assert the response contains the error message
        $responseData = json_decode($response->getContent(), true);
        $this->assertEquals('A megadott azonosítójú (ID: 9999) tankolási adat nem található.', $responseData['message']);
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
