<?php

namespace Tests\Unit\Models;

use App\Models\FuelPrice;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\TestCase;
use PHPUnit\Framework\Attributes\Test;

class FuelPriceTest extends TestCase
{
    use RefreshDatabase;

    public function setUp(): void
    {
        parent::setUp();

        // Kezdj új tranzakciót
        \DB::beginTransaction();
    }

    #[Test]
    public function it_casts_attributes_correctly()
    {
        $fuelPrice = FuelPrice::factory()->create([
            'period' => '2024-01-01',
            'petrol' => 624.50,
            'mixture' => 675.20,
            'diesel' => 638.30,
            'lp_gas' => 384.40,
        ]);

        $this->assertInstanceOf(\Carbon\Carbon::class, $fuelPrice->period);
        $this->assertIsFloat($fuelPrice->petrol);
        $this->assertIsFloat($fuelPrice->mixture);
        $this->assertIsFloat($fuelPrice->diesel);
        $this->assertIsFloat($fuelPrice->lp_gas);
    }

    public function tearDown(): void
    {
        // Görgess vissza minden tranzakciót
        \DB::rollBack();

        parent::tearDown();
    }
}
