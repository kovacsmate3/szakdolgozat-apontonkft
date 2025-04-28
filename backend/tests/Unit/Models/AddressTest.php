<?php

namespace Tests\Unit\Models;

use App\Models\Address;
use App\Models\Location;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\TestCase;
use PHPUnit\Framework\Attributes\Test;

class AddressTest extends TestCase
{
    use RefreshDatabase;

    public function setUp(): void
    {
        parent::setUp();

        \DB::beginTransaction();
    }

    #[Test]
    public function it_belongs_to_location()
    {
        $location = Location::factory()->create();
        $address = Address::factory()->create(['location_id' => $location->id]);

        $this->assertEquals($location->id, $address->location->id);
        $this->assertInstanceOf(Location::class, $address->location);
    }

    #[Test]
    public function it_formats_full_address_correctly()
    {
        $address = Address::factory()->create([
            'country' => 'Magyarország',
            'postalcode' => 1151,
            'city' => 'Budapest',
            'road_name' => 'Esthajnal',
            'public_space_type' => 'utca',
            'building_number' => '3.',
        ]);

        $expectedFormat = "1151 Budapest, Esthajnal utca 3.";
        $this->assertEquals($expectedFormat, $address->fullAddress());
    }

    #[Test]
    public function it_casts_postalcode_to_integer()
    {
        $address = Address::factory()->create(['postalcode' => '1151']);
        $this->assertIsInt($address->postalcode);
        $this->assertEquals(1151, $address->postalcode);
    }

    public function tearDown(): void
    {
        // Görgess vissza minden tranzakciót
        \DB::rollBack();
        parent::tearDown();
    }
}
