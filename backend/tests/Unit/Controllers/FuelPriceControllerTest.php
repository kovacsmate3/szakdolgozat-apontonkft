<?php

namespace Tests\Unit\Controllers;

use App\Http\Controllers\Api\RoadRecord\FuelPriceController;
use App\Models\FuelPrice;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Mockery;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class FuelPriceControllerTest extends TestCase
{
    use RefreshDatabase;

    protected $fuelPriceController;

    public function setUp(): void
    {
        parent::setUp();
        if (\DB::transactionLevel() > 0) {
            \DB::rollBack();
        }
        $this->fuelPriceController = new FuelPriceController();
    }

    #[Test]
    public function index_returns_all_fuel_prices_ordered_by_period()
    {
        // Állítsuk be az alapértelmezett időzónát
        date_default_timezone_set('UTC');
        Carbon::setTestNow();

        // Töröljük a meglévő adatokat
        \DB::table('fuel_prices')->truncate();

        // Explicit módon hozzuk létre a tesztadatokat
        $testPrices = [
            ['period' => '2024-01-01', 'petrol' => 550, 'mixture' => 600, 'diesel' => 530, 'lp_gas' => 300],
            ['period' => '2024-02-01', 'petrol' => 560, 'mixture' => 610, 'diesel' => 540, 'lp_gas' => 310],
            ['period' => '2024-03-01', 'petrol' => 570, 'mixture' => 620, 'diesel' => 550, 'lp_gas' => 320]
        ];

        // Explicit módon hozzuk létre a rekordokat
        foreach ($testPrices as $priceData) {
            // Explicit Carbon objektum létrehozása UTC időzónában
            $carbonPeriod = Carbon::createFromFormat('Y-m-d', $priceData['period'], 'UTC');
            $priceData['period'] = $carbonPeriod;
            FuelPrice::create($priceData);
        }

        // Request létrehozása
        $request = new Request();

        // Kontroller metódus meghívása
        $response = $this->fuelPriceController->index($request);

        // Ellenőrzés: helyes HTTP státuszkód
        $this->assertEquals(Response::HTTP_OK, $response->getStatusCode());

        // Ellenőrzés: megfelelő rekordok száma
        $responseData = json_decode($response->getContent(), true);

        // Ellenőrizzük a dátumok sorrendjét
        $responsePeriods = array_map(fn($item) => substr($item['period'], 0, 10), $responseData);

        // Elvárt sorrend: legutóbbi dátumtól a legkorábbi felé
        $expectedPeriods = ['2024-03-01', '2024-02-01', '2024-01-01'];

        // Ellenőrzés: pontosan ezek vannak-e a válaszban, a megfelelő sorrendben
        $this->assertEquals(
            $expectedPeriods,
            array_slice($responsePeriods, 0, 3),
        );
    }

    #[Test]
    public function store_creates_new_fuel_price_and_returns_success()
    {
        // Create fuel price data
        $fuelPriceData = [
            'period' => '2024-04-01',
            'petrol' => 650,
            'mixture' => 680,
            'diesel' => 640,
            'lp_gas' => 390
        ];

        // Create a request
        $request = new Request($fuelPriceData);
        $request->setMethod('POST');

        // Call the store method
        $response = $this->fuelPriceController->store($request);

        // Assert the response has HTTP 201 status
        $this->assertEquals(Response::HTTP_CREATED, $response->getStatusCode());

        // Assert the response contains the success message
        $responseData = json_decode($response->getContent(), true);
        $this->assertEquals('Az üzemanyagár sikeresen létrehozva.', $responseData['message']);

        // Assert the fuel price was created in the database
        $this->assertDatabaseHas('fuel_prices', [
            'petrol' => 650,
            'mixture' => 680,
            'diesel' => 640,
            'lp_gas' => 390
        ]);
    }

    #[Test]
    public function store_rejects_duplicate_month_periods()
    {
        // Create an existing fuel price for April 2024
        FuelPrice::factory()->create(['period' => '2024-04-01']);

        // Set expectation for ValidationException
        $this->expectException(\Illuminate\Validation\ValidationException::class);

        // Try to create another fuel price for April 2024 but with a different day
        $fuelPriceData = [
            'period' => '2024-04-15',
            'petrol' => 650,
            'mixture' => 680,
            'diesel' => 640,
            'lp_gas' => 390
        ];

        // Create a request
        $request = new Request($fuelPriceData);
        $request->setMethod('POST');

        // Call the store method - this should throw a ValidationException
        $this->fuelPriceController->store($request);
    }

    #[Test]
    public function show_returns_fuel_price_by_id()
    {
        // Állítsuk be az alapértelmezett időzónát
        date_default_timezone_set('UTC');
        Carbon::setTestNow();

        // Create a fuel price
        $fuelPrice = FuelPrice::factory()->create([
            'period' => Carbon::createFromFormat('Y-m-d', '2024-04-01', 'UTC'),
            'petrol' => 650,
            'mixture' => 680,
            'diesel' => 640,
            'lp_gas' => 390
        ]);

        // Call the show method
        $response = $this->fuelPriceController->show($fuelPrice->id);

        // Assert the response has HTTP 200 status
        $this->assertEquals(Response::HTTP_OK, $response->getStatusCode());

        // Assert the response contains the fuel price data
        $responseData = json_decode($response->getContent(), true);

        $this->assertEquals($fuelPrice->id, $responseData['id']);
        $this->assertEquals('2024-04-01', substr($responseData['period'], 0, 10), 'A dátum nem egyezik az elvárttal');
        $this->assertEquals(650, $responseData['petrol']);
        $this->assertEquals(680, $responseData['mixture']);
        $this->assertEquals(640, $responseData['diesel']);
        $this->assertEquals(390, $responseData['lp_gas']);
    }

    #[Test]
    public function show_returns_not_found_for_invalid_id()
    {
        // Call the show method with a non-existent ID
        $response = $this->fuelPriceController->show(9999);

        // Assert the response has HTTP 404 status
        $this->assertEquals(Response::HTTP_NOT_FOUND, $response->getStatusCode());

        // Assert the response contains the error message
        $responseData = json_decode($response->getContent(), true);
        $this->assertEquals('A megadott azonosítójú (ID: 9999) üzemanyagár nem található.', $responseData['message']);
    }

    #[Test]
    public function update_modifies_fuel_price_and_returns_success()
    {
        // Create a fuel price
        $fuelPrice = FuelPrice::factory()->create([
            'period' => '2024-04-01',
            'petrol' => 650,
            'mixture' => 680,
            'diesel' => 640,
            'lp_gas' => 390
        ]);

        // Create update data
        $updateData = [
            'petrol' => 660,
            'diesel' => 650
        ];

        // Create a request
        $request = new Request($updateData);
        $request->setMethod('PUT');

        // Call the update method
        $response = $this->fuelPriceController->update($request, $fuelPrice->id);

        // Assert the response has HTTP 200 status
        $this->assertEquals(Response::HTTP_OK, $response->getStatusCode());

        // Assert the response contains the success message
        $responseData = json_decode($response->getContent(), true);
        $this->assertEquals('Az üzemanyagár adatai sikeresen frissítve lettek.', $responseData['message']);

        // Assert the fuel price was updated in the database
        $this->assertDatabaseHas('fuel_prices', [
            'id' => $fuelPrice->id,
            'petrol' => 660,
            'diesel' => 650,
            'mixture' => 680, // Unchanged
            'lp_gas' => 390   // Unchanged
        ]);
    }

    #[Test]
    public function update_returns_not_found_for_invalid_id()
    {
        // Create a request
        $request = new Request(['petrol' => 660]);
        $request->setMethod('PUT');

        // Call the update method with a non-existent ID
        $response = $this->fuelPriceController->update($request, 9999);

        // Assert the response has HTTP 404 status
        $this->assertEquals(Response::HTTP_NOT_FOUND, $response->getStatusCode());

        // Assert the response contains the error message
        $responseData = json_decode($response->getContent(), true);
        $this->assertEquals('A megadott azonosítójú (ID: 9999) üzemanyagár nem található.', $responseData['message']);
    }

    #[Test]
    public function destroy_deletes_fuel_price_and_returns_success()
    {
        // Create a fuel price
        $fuelPrice = FuelPrice::factory()->create([
            'period' => '2024-04-01'
        ]);

        // Mock Carbon locale
        Carbon::setLocale('hu');

        // Call the destroy method
        $response = $this->fuelPriceController->destroy($fuelPrice->id);

        // Assert the response has HTTP 200 status
        $this->assertEquals(Response::HTTP_OK, $response->getStatusCode());

        // Assert the response contains the success message
        $responseData = json_decode($response->getContent(), true);
        $this->assertStringContainsString('időszak üzemanyagárai sikeresen törölve', $responseData['message']);

        // Assert the fuel price was deleted from the database
        $this->assertDatabaseMissing('fuel_prices', ['id' => $fuelPrice->id]);
    }

    #[Test]
    public function destroy_returns_not_found_for_invalid_id()
    {
        // Call the destroy method with a non-existent ID
        $response = $this->fuelPriceController->destroy(9999);

        // Assert the response has HTTP 404 status
        $this->assertEquals(Response::HTTP_NOT_FOUND, $response->getStatusCode());

        // Assert the response contains the error message
        $responseData = json_decode($response->getContent(), true);
        $this->assertEquals('A megadott azonosítójú (ID: 9999) üzemanyagár nem található.', $responseData['message']);
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
