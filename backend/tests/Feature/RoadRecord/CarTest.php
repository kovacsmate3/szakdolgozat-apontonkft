<?php

namespace Tests\Feature\RoadRecord;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use App\Models\Role;
use App\Models\Car;
use PHPUnit\Framework\Attributes\Test;

class CarTest extends TestCase
{
    use RefreshDatabase;

    protected $adminUser;
    protected $regularUser;

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
    }

    #[Test]
    public function test_user_can_list_cars()
    {
        // Create some test cars
        Car::create([
            'user_id' => $this->adminUser->id,
            'car_type' => 'sedan',
            'license_plate' => 'ABC-123',
            'manufacturer' => 'Toyota',
            'model' => 'Corolla',
            'fuel_type' => 'petrol',
            'standard_consumption' => 7.5,
            'capacity' => 1800,
            'fuel_tank_capacity' => 55,
        ]);

        Car::create([
            'user_id' => $this->regularUser->id,
            'car_type' => 'hatchback',
            'license_plate' => 'DEF-456',
            'manufacturer' => 'Volkswagen',
            'model' => 'Golf',
            'fuel_type' => 'diesel',
            'standard_consumption' => 6.2,
            'capacity' => 2000,
            'fuel_tank_capacity' => 50,
        ]);

        $response = $this->actingAs($this->regularUser)
            ->getJson('/api/cars');

        $response->assertStatus(200)
            ->assertJsonCount(2);
    }

    #[Test]
    public function test_user_can_view_specific_car()
    {
        $car = Car::create([
            'user_id' => $this->adminUser->id,
            'car_type' => 'sedan',
            'license_plate' => 'ABC-123',
            'manufacturer' => 'Toyota',
            'model' => 'Corolla',
            'fuel_type' => 'petrol',
            'standard_consumption' => 7.5,
            'capacity' => 1800,
            'fuel_tank_capacity' => 55,
        ]);

        $response = $this->actingAs($this->regularUser)
            ->getJson("/api/cars/{$car->id}");

        $response->assertStatus(200)
            ->assertJsonFragment([
                'license_plate' => 'ABC-123',
                'manufacturer' => 'Toyota',
                'model' => 'Corolla'
            ]);
    }

    #[Test]
    public function test_user_can_create_car()
    {
        $carData = [
            'user_id' => $this->regularUser->id,
            'car_type' => 'SUV',
            'license_plate' => 'GHI-789',
            'manufacturer' => 'Honda',
            'model' => 'CR-V',
            'fuel_type' => 'petrol',
            'standard_consumption' => 8.0,
            'capacity' => 2000,
            'fuel_tank_capacity' => 58,
        ];

        $response = $this->actingAs($this->regularUser)
            ->postJson('/api/cars', $carData);

        $response->assertStatus(201)
            ->assertJson([
                'message' => 'A jármű sikeresen létrehozva.'
            ]);

        $this->assertDatabaseHas('cars', [
            'license_plate' => 'GHI-789',
            'user_id' => $this->regularUser->id
        ]);
    }

    #[Test]
    public function test_user_cant_create_car_with_duplicate_license_plate()
    {
        // Create an initial car
        Car::create([
            'user_id' => $this->adminUser->id,
            'car_type' => 'sedan',
            'license_plate' => 'ABC-123',
            'manufacturer' => 'Toyota',
            'model' => 'Corolla',
            'fuel_type' => 'petrol',
            'standard_consumption' => 7.5,
            'capacity' => 1800,
            'fuel_tank_capacity' => 55,
        ]);

        // Try to create another car with the same license plate
        $carData = [
            'user_id' => $this->regularUser->id,
            'car_type' => 'SUV',
            'license_plate' => 'ABC-123', // Duplicate license plate
            'manufacturer' => 'Honda',
            'model' => 'CR-V',
            'fuel_type' => 'petrol',
            'standard_consumption' => 8.0,
            'capacity' => 2000,
            'fuel_tank_capacity' => 58,
        ];

        $response = $this->actingAs($this->regularUser)
            ->postJson('/api/cars', $carData);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['license_plate']);
    }

    #[Test]
    public function test_user_can_update_car()
    {
        $car = Car::create([
            'user_id' => $this->regularUser->id,
            'car_type' => 'hatchback',
            'license_plate' => 'DEF-456',
            'manufacturer' => 'Volkswagen',
            'model' => 'Golf',
            'fuel_type' => 'diesel',
            'standard_consumption' => 6.2,
            'capacity' => 2000,
            'fuel_tank_capacity' => 50,
        ]);

        $updateData = [
            'standard_consumption' => 6.5,
            'fuel_type' => 'benzin',
        ];

        $response = $this->actingAs($this->regularUser)
            ->putJson("/api/cars/{$car->id}", $updateData);

        $response->assertStatus(200)
            ->assertJson([
                'message' => 'A jármű adatai sikeresen frissítve lettek.'
            ]);

        $this->assertDatabaseHas('cars', [
            'id' => $car->id,
            'standard_consumption' => 6.5,
            'fuel_type' => 'benzin'
        ]);
    }

    #[Test]
    public function test_car_cannot_be_deleted_with_related_records()
    {
        $car = Car::create([
            'user_id' => $this->regularUser->id,
            'car_type' => 'hatchback',
            'license_plate' => 'DEF-456',
            'manufacturer' => 'Volkswagen',
            'model' => 'Golf',
            'fuel_type' => 'diesel',
            'standard_consumption' => 6.2,
            'capacity' => 2000,
            'fuel_tank_capacity' => 50,
        ]);

        $location = \App\Models\Location::create([
            'name' => fake()->address(),
            'location_type' => 'töltőállomás',
            'is_headquarter' => false,
        ]);

        \App\Models\FuelExpense::create([
            'car_id' => $car->id,
            'user_id' => $this->regularUser->id,
            'location_id' => $location->id, // vagy egy létező location_id
            'expense_date' => now(),
            'amount' => 20000,
            'currency' => 'HUF',
            'fuel_quantity' => 40.0,
            'odometer' => 150000,
        ]);

        $response = $this->actingAs($this->adminUser)
            ->deleteJson("/api/cars/{$car->id}");

        $response->assertStatus(403)
            ->assertJson([
                'message' => 'Ez a jármű tankolásokhoz vagy utazásokhoz van rendelve, ezért nem törölhető.'
            ]);
    }

    #[Test]
    public function test_admin_can_delete_car_without_related_records()
    {
        $car = Car::create([
            'user_id' => $this->regularUser->id,
            'car_type' => 'hatchback',
            'license_plate' => 'DEF-456',
            'manufacturer' => 'Volkswagen',
            'model' => 'Golf',
            'fuel_type' => 'diesel',
            'standard_consumption' => 6.2,
            'capacity' => 2000,
            'fuel_tank_capacity' => 50,
        ]);

        $response = $this->actingAs($this->adminUser)
            ->deleteJson("/api/cars/{$car->id}");

        $response->assertStatus(200)
            ->assertJson([
                'message' => "Volkswagen Golf (DEF-456) jármű sikeresen törölve."
            ]);

        $this->assertDatabaseMissing('cars', [
            'id' => $car->id
        ]);
    }

    #[Test]
    public function test_validation_rules_for_car_creation()
    {
        $invalidCarData = [
            'user_id' => $this->regularUser->id,
            // Missing required fields and invalid values to test validation
            'car_type' => '',
            'license_plate' => str_repeat('A', 25), // Too long
            'standard_consumption' => -1, // Negative value
            'capacity' => 0, // Should be at least 1
        ];

        $response = $this->actingAs($this->regularUser)
            ->postJson('/api/cars', $invalidCarData);

        $response->assertStatus(422)
            ->assertJsonValidationErrors([
                'car_type',
                'license_plate',
                'manufacturer',
                'model',
                'fuel_type',
                'standard_consumption',
                'capacity'
            ]);
    }
}
