<?php

namespace Tests\Feature\RoadRecord;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use App\Models\Role;
use App\Models\Car;
use App\Models\Location;
use App\Models\FuelExpense;
use Carbon\Carbon;
use PHPUnit\Framework\Attributes\Test;

class FuelExpenseTest extends TestCase
{
    use RefreshDatabase;

    protected $adminUser;
    protected $regularUser;
    protected $car;
    protected $location;

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

        // Create a gas station location
        $this->location = Location::create([
            'name' => 'Test Gas Station',
            'location_type' => 'töltőállomás',
            'is_headquarter' => false,
        ]);
    }

    #[Test]
    public function test_user_can_list_fuel_expenses()
    {
        // Create test fuel expenses
        FuelExpense::create([
            'car_id' => $this->car->id,
            'user_id' => $this->regularUser->id,
            'location_id' => $this->location->id,
            'expense_date' => Carbon::now()->subDays(10),
            'amount' => 25000,
            'currency' => 'HUF',
            'fuel_quantity' => 35.5,
            'odometer' => 45000,
        ]);

        FuelExpense::create([
            'car_id' => $this->car->id,
            'user_id' => $this->regularUser->id,
            'location_id' => $this->location->id,
            'expense_date' => Carbon::now()->subDays(5),
            'amount' => 28000,
            'currency' => 'HUF',
            'fuel_quantity' => 40.0,
            'odometer' => 45500,
        ]);

        $response = $this->actingAs($this->regularUser)
            ->getJson('/api/fuel-expenses');

        $response->assertStatus(200)
            ->assertJsonCount(2);
    }

    #[Test]
    public function test_user_can_filter_fuel_expenses()
    {
        // Create test fuel expenses with different dates
        FuelExpense::create([
            'car_id' => $this->car->id,
            'user_id' => $this->regularUser->id,
            'location_id' => $this->location->id,
            'expense_date' => Carbon::now()->subDays(30),
            'amount' => 25000,
            'currency' => 'HUF',
            'fuel_quantity' => 35.5,
            'odometer' => 45000,
        ]);

        FuelExpense::create([
            'car_id' => $this->car->id,
            'user_id' => $this->regularUser->id,
            'location_id' => $this->location->id,
            'expense_date' => Carbon::now()->subDays(5),
            'amount' => 28000,
            'currency' => 'HUF',
            'fuel_quantity' => 40.0,
            'odometer' => 45500,
        ]);

        // Test filtering by date range
        $response = $this->actingAs($this->regularUser)
            ->getJson('/api/fuel-expenses?from_date=' . Carbon::now()->subDays(10)->format('Y-m-d'));

        $response->assertStatus(200)
            ->assertJsonCount(1);

        // Test filtering by car
        $response = $this->actingAs($this->regularUser)
            ->getJson('/api/fuel-expenses?car_id=' . $this->car->id);

        $response->assertStatus(200)
            ->assertJsonCount(2);
    }

    #[Test]
    public function test_user_can_create_fuel_expense()
    {
        $fuelExpenseData = [
            'car_id' => $this->car->id,
            'location_id' => $this->location->id,
            'expense_date' => Carbon::now()->format('Y-m-d H:i:s'),
            'amount' => 30000,
            'currency' => 'HUF',
            'fuel_quantity' => 42.5,
            'odometer' => 46000,
        ];

        $response = $this->actingAs($this->regularUser)
            ->postJson('/api/fuel-expenses', $fuelExpenseData);

        $response->assertStatus(201)
            ->assertJson([
                'message' => 'A tankolási adat sikeresen létrehozva.'
            ]);

        $this->assertDatabaseHas('fuel_expenses', [
            'car_id' => $this->car->id,
            'user_id' => $this->regularUser->id, // Should be set automatically
            'amount' => 30000
        ]);
    }

    #[Test]
    public function test_admin_can_create_fuel_expense_for_other_user()
    {
        $fuelExpenseData = [
            'car_id' => $this->car->id,
            'user_id' => $this->regularUser->id, // Explicitly setting user_id
            'location_id' => $this->location->id,
            'expense_date' => Carbon::now()->format('Y-m-d H:i:s'),
            'amount' => 30000,
            'currency' => 'HUF',
            'fuel_quantity' => 42.5,
            'odometer' => 46000,
        ];

        $response = $this->actingAs($this->adminUser)
            ->postJson('/api/fuel-expenses', $fuelExpenseData);

        $response->assertStatus(201);

        $this->assertDatabaseHas('fuel_expenses', [
            'car_id' => $this->car->id,
            'user_id' => $this->regularUser->id,
            'amount' => 30000
        ]);
    }

    #[Test]
    public function test_location_must_be_gas_station_type()
    {
        // Create a non-gas station location
        $officeLocation = Location::create([
            'name' => 'Office Location',
            'location_type' => 'telephely', // Not a gas station
            'is_headquarter' => false,
        ]);

        $fuelExpenseData = [
            'car_id' => $this->car->id,
            'location_id' => $officeLocation->id, // Invalid location type
            'expense_date' => Carbon::now()->format('Y-m-d H:i:s'),
            'amount' => 30000,
            'currency' => 'HUF',
            'fuel_quantity' => 42.5,
            'odometer' => 46000,
        ];

        $response = $this->actingAs($this->regularUser)
            ->postJson('/api/fuel-expenses', $fuelExpenseData);

        $response->assertStatus(422)
            ->assertJsonFragment([
                'location_id' => ['A megadott helyszín nem töltőállomás típusú.']
            ]);
    }

    #[Test]
    public function test_validation_for_fuel_expense_creation()
    {
        $invalidFuelExpenseData = [
            'car_id' => 999, // Non-existent car
            'location_id' => $this->location->id,
            'expense_date' => 'invalid-date', // Invalid date format
            'amount' => -500, // Negative amount
            'currency' => 'HUF',
            'fuel_quantity' => -10, // Negative quantity
            'odometer' => 'abc', // Not a number
        ];

        $response = $this->actingAs($this->regularUser)
            ->postJson('/api/fuel-expenses', $invalidFuelExpenseData);

        $response->assertStatus(422)
            ->assertJsonValidationErrors([
                'car_id',
                'expense_date',
                'amount',
                'fuel_quantity',
                'odometer'
            ]);
    }

    #[Test]
    public function test_user_can_view_specific_fuel_expense()
    {
        $fuelExpense = FuelExpense::create([
            'car_id' => $this->car->id,
            'user_id' => $this->regularUser->id,
            'location_id' => $this->location->id,
            'expense_date' => Carbon::now(),
            'amount' => 25000,
            'currency' => 'HUF',
            'fuel_quantity' => 35.5,
            'odometer' => 45000,
        ]);

        $response = $this->actingAs($this->regularUser)
            ->getJson("/api/fuel-expenses/{$fuelExpense->id}");

        $response->assertStatus(200)
            ->assertJsonStructure([
                'id',
                'car_id',
                'user_id',
                'location_id',
                'expense_date',
                'amount',
                'currency',
                'fuel_quantity',
                'odometer',
                'car',
                'user',
                'location'
            ])
            ->assertJsonFragment([
                'amount' => 25000,
                'fuel_quantity' => 35.5
            ]);
    }

    #[Test]
    public function test_user_can_update_own_fuel_expense()
    {
        $fuelExpense = FuelExpense::create([
            'car_id' => $this->car->id,
            'user_id' => $this->regularUser->id,
            'location_id' => $this->location->id,
            'expense_date' => Carbon::now(),
            'amount' => 25000,
            'currency' => 'HUF',
            'fuel_quantity' => 35.5,
            'odometer' => 45000,
        ]);

        $updateData = [
            'amount' => 26000,
            'fuel_quantity' => 37.0,
        ];

        $response = $this->actingAs($this->regularUser)
            ->putJson("/api/fuel-expenses/{$fuelExpense->id}", $updateData);

        $response->assertStatus(200)
            ->assertJson([
                'message' => 'A tankolási adat sikeresen frissítve.'
            ]);

        $this->assertDatabaseHas('fuel_expenses', [
            'id' => $fuelExpense->id,
            'amount' => 26000,
            'fuel_quantity' => 37.0
        ]);
    }

    #[Test]
    public function test_user_cannot_update_another_users_fuel_expense()
    {
        // Create another user
        $anotherUser = User::factory()->create();

        // Create a fuel expense for another user
        $fuelExpense = FuelExpense::create([
            'car_id' => $this->car->id,
            'user_id' => $anotherUser->id,
            'location_id' => $this->location->id,
            'expense_date' => Carbon::now(),
            'amount' => 25000,
            'currency' => 'HUF',
            'fuel_quantity' => 35.5,
            'odometer' => 45000,
        ]);

        $updateData = [
            'amount' => 26000,
        ];

        $response = $this->actingAs($this->regularUser)
            ->putJson("/api/fuel-expenses/{$fuelExpense->id}", $updateData);

        $response->assertStatus(403)
            ->assertJson([
                'message' => 'Nincs jogosultsága módosítani ezt a tankolási adatot.'
            ]);
    }

    #[Test]
    public function test_admin_can_update_any_fuel_expense()
    {
        $fuelExpense = FuelExpense::create([
            'car_id' => $this->car->id,
            'user_id' => $this->regularUser->id,
            'location_id' => $this->location->id,
            'expense_date' => Carbon::now(),
            'amount' => 25000,
            'currency' => 'HUF',
            'fuel_quantity' => 35.5,
            'odometer' => 45000,
        ]);

        $updateData = [
            'amount' => 27000,
            'user_id' => $this->adminUser->id, // Change the owner
        ];

        $response = $this->actingAs($this->adminUser)
            ->putJson("/api/fuel-expenses/{$fuelExpense->id}", $updateData);

        $response->assertStatus(200);

        $this->assertDatabaseHas('fuel_expenses', [
            'id' => $fuelExpense->id,
            'amount' => 27000,
            'user_id' => $this->adminUser->id
        ]);
    }

    #[Test]
    public function test_user_can_delete_own_fuel_expense()
    {
        $fuelExpense = FuelExpense::create([
            'car_id' => $this->car->id,
            'user_id' => $this->regularUser->id,
            'location_id' => $this->location->id,
            'expense_date' => Carbon::now(),
            'amount' => 25000,
            'currency' => 'HUF',
            'fuel_quantity' => 35.5,
            'odometer' => 45000,
        ]);

        $response = $this->actingAs($this->regularUser)
            ->deleteJson("/api/fuel-expenses/{$fuelExpense->id}");

        $response->assertStatus(200);

        $this->assertDatabaseMissing('fuel_expenses', [
            'id' => $fuelExpense->id
        ]);
    }

    #[Test]
    public function test_user_cannot_delete_another_users_fuel_expense()
    {
        // Create another user
        $anotherUser = User::factory()->create();

        // Create a fuel expense for another user
        $fuelExpense = FuelExpense::create([
            'car_id' => $this->car->id,
            'user_id' => $anotherUser->id,
            'location_id' => $this->location->id,
            'expense_date' => Carbon::now(),
            'amount' => 25000,
            'currency' => 'HUF',
            'fuel_quantity' => 35.5,
            'odometer' => 45000,
        ]);

        $response = $this->actingAs($this->regularUser)
            ->deleteJson("/api/fuel-expenses/{$fuelExpense->id}");

        $response->assertStatus(403)
            ->assertJson([
                'message' => 'Nincs jogosultsága törölni ezt a tankolási adatot.'
            ]);

        $this->assertDatabaseHas('fuel_expenses', [
            'id' => $fuelExpense->id
        ]);
    }

    #[Test]
    public function test_admin_can_delete_any_fuel_expense()
    {
        $fuelExpense = FuelExpense::create([
            'car_id' => $this->car->id,
            'user_id' => $this->regularUser->id,
            'location_id' => $this->location->id,
            'expense_date' => Carbon::now(),
            'amount' => 25000,
            'currency' => 'HUF',
            'fuel_quantity' => 35.5,
            'odometer' => 45000,
        ]);

        $response = $this->actingAs($this->adminUser)
            ->deleteJson("/api/fuel-expenses/{$fuelExpense->id}");

        $response->assertStatus(200);

        $this->assertDatabaseMissing('fuel_expenses', [
            'id' => $fuelExpense->id
        ]);
    }
}
