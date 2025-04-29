<?php

namespace Tests\Feature\RoadRecord;

use App\Models\FuelPrice;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;
use PHPUnit\Framework\Attributes\Test;
use Carbon\Carbon;

class FuelPriceTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    protected function setUp(): void
    {
        parent::setUp();

        // Hitelesített felhasználó létrehozása a tesztekhez
        Sanctum::actingAs(
            User::factory()->create()
        );

        date_default_timezone_set('UTC');
    }

    #[Test]
    public function test_user_can_get_all_fuel_prices()
    {
        // Üzemanyagárak létrehozása
        $fuelPrices = FuelPrice::factory()->count(3)->create();

        // API hívás
        $response = $this->getJson('/api/fuel-prices');

        // Ellenőrzés
        $response->assertStatus(200);
        $this->assertCount(3, $response->json());
    }

    #[Test]
    public function test_user_can_create_fuel_price()
    {
        // Adatok előkészítése
        $data = [
            'period' => '2025-05-01',
            'petrol' => 650,
            'mixture' => 680,
            'diesel' => 640,
            'lp_gas' => 390,
        ];

        // API hívás
        $response = $this->postJson('/api/fuel-prices', $data);

        // Ellenőrzés
        $response->assertStatus(201)
            ->assertJsonPath('message', 'Az üzemanyagár sikeresen létrehozva.')
            ->assertJsonPath('fuel_price.petrol', 650)
            ->assertJsonPath('fuel_price.mixture', 680)
            ->assertJsonPath('fuel_price.diesel', 640)
            ->assertJsonPath('fuel_price.lp_gas', 390);

        // Ellenőrizzük, hogy az adatbázisban is megvan-e
        // A dátum formátum miatt használjunk like keresést
        $this->assertDatabaseHas('fuel_prices', [
            'petrol' => 650,
            'mixture' => 680,
            'diesel' => 640,
            'lp_gas' => 390,
        ]);

        // A dátumot külön ellenőrizzük egyszerűbb módszerrel
        $latestFuelPrice = FuelPrice::latest('id')->first();
        $this->assertEquals('2025-05-01', $latestFuelPrice->period->format('Y-m-d'));
    }

    #[Test]
    public function test_user_can_show_fuel_price()
    {
        // Üzemanyagár létrehozása
        $testDate = Carbon::create(2024, 11, 13)->startOfDay()->setTimezone('UTC');

        $fuelPrice = FuelPrice::factory()->create([
            'period' => $testDate,
        ]);

        // API hívás
        $response = $this->getJson('/api/fuel-prices/' . $fuelPrice->id);

        // Ellenőrzés
        $response->assertStatus(200)
            ->assertJsonPath('id', $fuelPrice->id);

        // Float értékek összehasonlítása assertEquals használatával
        $this->assertEquals($fuelPrice->petrol, $response->json('petrol'));
        $this->assertEquals($fuelPrice->mixture, $response->json('mixture'));
        $this->assertEquals($fuelPrice->diesel, $response->json('diesel'));
        $this->assertEquals($fuelPrice->lp_gas, $response->json('lp_gas'));

        // Dátum ellenőrzése
        $periodFromResponse = Carbon::parse($response->json('period'))->setTimezone('UTC');
        $periodFromDatabase = Carbon::parse($fuelPrice->period)->setTimezone('UTC');

        // Dátumok ellenőrzése UTC időzónában
        $this->assertEquals(
            $periodFromDatabase->format('Y-m-d'),
            $periodFromResponse->format('Y-m-d'),
            "Dates don't match after timezone normalization"
        );
    }

    #[Test]
    public function test_user_can_update_fuel_price()
    {
        // Üzemanyagár létrehozása
        $fuelPrice = FuelPrice::factory()->create();

        // Frissítési adatok
        $data = [
            'petrol' => 680,
            'mixture' => 700,
        ];

        // API hívás
        $response = $this->putJson('/api/fuel-prices/' . $fuelPrice->id, $data);

        // Ellenőrzés
        $response->assertStatus(200)
            ->assertJsonPath('message', 'Az üzemanyagár adatai sikeresen frissítve lettek.');

        // Float értékek összehasonlítása
        $this->assertEquals(680, $response->json('fuel_price.petrol'));
        $this->assertEquals(700, $response->json('fuel_price.mixture'));
        $this->assertEquals($fuelPrice->id, $response->json('fuel_price.id'));

        // Ellenőrizzük, hogy az adatbázisban is frissült-e
        $this->assertDatabaseHas('fuel_prices', [
            'id' => $fuelPrice->id,
            'petrol' => 680,
            'mixture' => 700,
        ]);
    }

    #[Test]
    public function test_user_can_delete_fuel_price()
    {
        // Üzemanyagár létrehozása
        $fuelPrice = FuelPrice::factory()->create();

        // API hívás
        $response = $this->deleteJson('/api/fuel-prices/' . $fuelPrice->id);

        // Ellenőrzés
        $response->assertStatus(200)
            ->assertJsonPath('message', 'A(z) ' . $fuelPrice->period->translatedFormat('Y. F') . ' időszak üzemanyagárai sikeresen törölve.');

        // Ellenőrizzük, hogy az adatbázisból valóban törlődött-e
        $this->assertDatabaseMissing('fuel_prices', ['id' => $fuelPrice->id]);
    }

    #[Test]
    public function test_validate_fuel_price_creation()
    {
        // Hiányos adatok
        $data = [
            'period' => '',
            'petrol' => 'abc',
            'mixture' => -10,
            'diesel' => '',
            'lp_gas' => '',
        ];

        // API hívás
        $response = $this->postJson('/api/fuel-prices', $data);

        // Ellenőrzés
        $response->assertStatus(422)
            ->assertJsonValidationErrors(['period', 'petrol', 'mixture', 'diesel', 'lp_gas']);
    }

    #[Test]
    public function test_cannot_create_duplicate_fuel_price_for_same_period(): void
    {
        // Létrehozunk egy rekordot 2025 decemberére
        FuelPrice::create([
            'period' => "2025-12-01", // December
            'petrol' => 624,
            'mixture' => 675,
            'diesel' => 638,
            'lp_gas' => 384,
        ]);

        // Most próbálunk egy második rekordot létrehozni ugyanarra a hónapra, más nappal
        $secondData = [
            'period' => "2025-12-31", // Ugyanaz a hónap
            'petrol' => 645.50,
            'mixture' => 650.75,
            'diesel' => 639.20,
            'lp_gas' => 310.00,
        ];

        // Most már csak a 422 a megfelelő válasz
        $response = $this->postJson('/api/fuel-prices', $secondData);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['period']);
    }

    #[Test]
    public function test_not_found_returns_proper_message()
    {
        // Nem létező azonosító
        $response = $this->getJson('/api/fuel-prices/999');

        // Ellenőrzés
        $response->assertStatus(404)
            ->assertJsonPath('message', 'A megadott azonosítójú (ID: 999) üzemanyagár nem található.');
    }
}
