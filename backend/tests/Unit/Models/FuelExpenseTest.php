<?php

namespace Tests\Unit\Models;

use App\Models\Car;
use App\Models\FuelExpense;
use App\Models\Location;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\TestCase;
use PHPUnit\Framework\Attributes\Test;

class FuelExpenseTest extends TestCase
{
    use RefreshDatabase;

    public function setUp(): void
    {
        parent::setUp();

        \DB::beginTransaction();
    }

    #[Test]
    public function it_belongs_to_car()
    {
        $user = User::factory()->create();
        $car = Car::factory()->create(['user_id' => $user->id]);
        $location = Location::factory()->create(['location_type' => 'töltőállomás']);

        $fuelExpense = FuelExpense::factory()->create([
            'user_id' => $user->id,
            'car_id' => $car->id,
            'location_id' => $location->id
        ]);

        $this->assertEquals($car->id, $fuelExpense->car->id);
        $this->assertInstanceOf(Car::class, $fuelExpense->car);
    }

    #[Test]
    public function it_belongs_to_user()
    {
        $user = User::factory()->create();
        $car = Car::factory()->create(['user_id' => $user->id]);
        $location = Location::factory()->create(['location_type' => 'töltőállomás']);

        $fuelExpense = FuelExpense::factory()->create([
            'user_id' => $user->id,
            'car_id' => $car->id,
            'location_id' => $location->id
        ]);

        $this->assertEquals($user->id, $fuelExpense->user->id);
        $this->assertInstanceOf(User::class, $fuelExpense->user);
    }

    #[Test]
    public function it_belongs_to_location()
    {
        $user = User::factory()->create();
        $car = Car::factory()->create(['user_id' => $user->id]);
        $location = Location::factory()->create(['location_type' => 'töltőállomás']);

        $fuelExpense = FuelExpense::factory()->create([
            'user_id' => $user->id,
            'car_id' => $car->id,
            'location_id' => $location->id
        ]);

        $this->assertEquals($location->id, $fuelExpense->location->id);
        $this->assertInstanceOf(Location::class, $fuelExpense->location);
    }

    #[Test]
    public function it_casts_attributes_correctly()
    {
        $user = User::factory()->create();
        $car = Car::factory()->create(['user_id' => $user->id]);
        $location = Location::factory()->create(['location_type' => 'töltőállomás']);

        $fuelExpense = FuelExpense::factory()->create([
            'user_id' => $user->id,
            'car_id' => $car->id,
            'location_id' => $location->id,
            'expense_date' => now(),
            'amount' => 15000.50,
            'fuel_quantity' => 25.5,
            'odometer' => 10000
        ]);

        $this->assertInstanceOf(\Carbon\Carbon::class, $fuelExpense->expense_date);
        $this->assertIsFloat($fuelExpense->amount);
        $this->assertIsFloat($fuelExpense->fuel_quantity);
        $this->assertIsInt($fuelExpense->odometer);
    }

    public function tearDown(): void
    {
        // Görgess vissza minden tranzakciót
        \DB::rollBack();
        parent::tearDown();
    }
}
