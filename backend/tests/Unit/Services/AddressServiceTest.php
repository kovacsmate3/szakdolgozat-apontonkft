<?php

namespace Tests\Unit\Services;

use App\Models\Address;
use App\Models\Location;
use App\Services\AddressService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class AddressServiceTest extends TestCase
{
    use RefreshDatabase;

    protected $addressService;

    public function setUp(): void
    {
        parent::setUp();
        $this->addressService = new AddressService();
        DB::isFake();
    }

    #[Test]
    public function can_create_address_for_location()
    {
        // Create a location
        $location = Location::factory()->create([
            'name' => 'Test Location',
            'location_type' => 'partner'
        ]);

        // Address data
        $addressData = [
            'country' => 'Magyarország',
            'postalcode' => 1151,
            'city' => 'Budapest',
            'road_name' => 'Test Road',
            'public_space_type' => 'utca',
            'building_number' => '1'
        ];

        // Call the method
        $address = $this->addressService->createAddressForLocation($addressData, $location);

        // Assert the address was created correctly
        $this->assertInstanceOf(Address::class, $address);
        $this->assertEquals($location->id, $address->location_id);
        $this->assertEquals('Magyarország', $address->country);
        $this->assertEquals(1151, $address->postalcode);
        $this->assertEquals('Budapest', $address->city);
        $this->assertEquals('Test Road', $address->road_name);
        $this->assertEquals('utca', $address->public_space_type);
        $this->assertEquals('1', $address->building_number);

        // Assert the address exists in the database
        $this->assertDatabaseHas('addresses', [
            'location_id' => $location->id,
            'city' => 'Budapest',
            'road_name' => 'Test Road'
        ]);
    }

    #[Test]
    public function can_update_address_for_location()
    {
        // Create a location with an address
        $location = Location::factory()->create();
        $address = Address::factory()->create([
            'location_id' => $location->id,
            'country' => 'Magyarország',
            'postalcode' => 1151,
            'city' => 'Budapest',
            'road_name' => 'Old Road',
            'public_space_type' => 'utca',
            'building_number' => '1'
        ]);

        // New address data
        $newAddressData = [
            'country' => 'Magyarország',
            'postalcode' => 1152,
            'city' => 'Budapest',
            'road_name' => 'New Road',
            'public_space_type' => 'út',
            'building_number' => '2'
        ];

        // Call the method
        $updatedAddress = $this->addressService->updateAddressForLocation($newAddressData, $location);

        // Assert the address was updated correctly
        $this->assertInstanceOf(Address::class, $updatedAddress);
        $this->assertEquals($location->id, $updatedAddress->location_id);
        $this->assertEquals('Magyarország', $updatedAddress->country);
        $this->assertEquals(1152, $updatedAddress->postalcode);
        $this->assertEquals('Budapest', $updatedAddress->city);
        $this->assertEquals('New Road', $updatedAddress->road_name);
        $this->assertEquals('út', $updatedAddress->public_space_type);
        $this->assertEquals('2', $updatedAddress->building_number);

        // Assert the address was updated in the database
        $this->assertDatabaseHas('addresses', [
            'id' => $address->id,
            'location_id' => $location->id,
            'postalcode' => 1152,
            'road_name' => 'New Road',
            'public_space_type' => 'út',
            'building_number' => '2'
        ]);
    }

    #[Test]
    public function creates_new_address_for_location_when_none_exists()
    {
        // Create a location without an address
        $location = Location::factory()->create();

        // Address data
        $addressData = [
            'country' => 'Magyarország',
            'postalcode' => 1151,
            'city' => 'Budapest',
            'road_name' => 'New Road',
            'public_space_type' => 'utca',
            'building_number' => '1'
        ];

        // Call the method
        $newAddress = $this->addressService->updateAddressForLocation($addressData, $location);

        // Assert a new address was created
        $this->assertInstanceOf(Address::class, $newAddress);
        $this->assertEquals($location->id, $newAddress->location_id);
        $this->assertEquals('Magyarország', $newAddress->country);
        $this->assertEquals(1151, $newAddress->postalcode);
        $this->assertEquals('Budapest', $newAddress->city);
        $this->assertEquals('New Road', $newAddress->road_name);
        $this->assertEquals('utca', $newAddress->public_space_type);
        $this->assertEquals('1', $newAddress->building_number);

        // Assert the new address exists in the database
        $this->assertDatabaseHas('addresses', [
            'location_id' => $location->id,
            'city' => 'Budapest',
            'road_name' => 'New Road'
        ]);
    }

    #[Test]
    public function can_create_location_with_address()
    {
        // Location data
        $locationData = [
            'name' => 'Test Location',
            'location_type' => 'partner',
            'is_headquarter' => false,
            'user_id' => null
        ];

        // Address data
        $addressData = [
            'country' => 'Magyarország',
            'postalcode' => 1151,
            'city' => 'Budapest',
            'road_name' => 'Test Road',
            'public_space_type' => 'utca',
            'building_number' => '1'
        ];

        // Call the method
        $location = $this->addressService->createLocationWithAddress($locationData, $addressData);

        // Assert the location was created correctly
        $this->assertInstanceOf(Location::class, $location);
        $this->assertEquals('Test Location', $location->name);
        $this->assertEquals('partner', $location->location_type);
        $this->assertFalse($location->is_headquarter);

        // Assert the location exists in the database
        $this->assertDatabaseHas('locations', [
            'name' => 'Test Location',
            'location_type' => 'partner'
        ]);

        // Assert the location has an address
        $this->assertInstanceOf(Address::class, $location->address);
        $this->assertEquals('Magyarország', $location->address->country);
        $this->assertEquals(1151, $location->address->postalcode);
        $this->assertEquals('Budapest', $location->address->city);
        $this->assertEquals('Test Road', $location->address->road_name);
        $this->assertEquals('utca', $location->address->public_space_type);
        $this->assertEquals('1', $location->address->building_number);

        // Assert the address exists in the database
        $this->assertDatabaseHas('addresses', [
            'location_id' => $location->id,
            'city' => 'Budapest',
            'road_name' => 'Test Road'
        ]);
    }

    #[Test]
    public function can_update_location_with_address()
    {
        // Create a location with an address
        $location = Location::factory()->create([
            'name' => 'Old Location',
            'location_type' => 'partner',
            'is_headquarter' => false
        ]);

        $address = Address::factory()->create([
            'location_id' => $location->id,
            'country' => 'Magyarország',
            'postalcode' => 1151,
            'city' => 'Budapest',
            'road_name' => 'Old Road',
            'public_space_type' => 'utca',
            'building_number' => '1'
        ]);

        // New location data
        $newLocationData = [
            'name' => 'Updated Location',
            'is_headquarter' => true
        ];

        // New address data
        $newAddressData = [
            'city' => 'Debrecen',
            'postalcode' => 4032,
            'road_name' => 'New Road'
        ];

        // Call the method
        $updatedLocation = $this->addressService->updateLocationWithAddress($location, $newLocationData, $newAddressData);

        // Assert the location was updated correctly
        $this->assertInstanceOf(Location::class, $updatedLocation);
        $this->assertEquals('Updated Location', $updatedLocation->name);
        $this->assertTrue($updatedLocation->is_headquarter);

        // Assert the location was updated in the database
        $this->assertDatabaseHas('locations', [
            'id' => $location->id,
            'name' => 'Updated Location',
            'is_headquarter' => 1
        ]);

        // Assert the location's address was updated
        $this->assertInstanceOf(Address::class, $updatedLocation->address);
        $this->assertEquals('Debrecen', $updatedLocation->address->city);
        $this->assertEquals(4032, $updatedLocation->address->postalcode);
        $this->assertEquals('New Road', $updatedLocation->address->road_name);

        // Assert the address was updated in the database
        $this->assertDatabaseHas('addresses', [
            'id' => $address->id,
            'location_id' => $location->id,
            'city' => 'Debrecen',
            'postalcode' => 4032,
            'road_name' => 'New Road'
        ]);
    }

    #[Test]
    public function can_set_headquarters_flag()
    {
        // Create a location
        $location1 = Location::factory()->create(['is_headquarter' => true]);

        // New location data with is_headquarter = true
        $newLocationData = [
            'name' => 'New HQ',
            'location_type' => 'partner',
            'is_headquarter' => true,
            'user_id' => null
        ];

        $addressData = [
            'country' => 'Magyarország',
            'postalcode' => 1151,
            'city' => 'Budapest',
            'road_name' => 'HQ Road',
            'public_space_type' => 'utca',
            'building_number' => '1'
        ];

        // Call the method
        $location = $this->addressService->createLocationWithAddress($newLocationData, $addressData);

        // Assert the new location is set as headquarter
        $this->assertTrue($location->is_headquarter);

        // Just verify it exists in the database
        $this->assertDatabaseHas('locations', [
            'name' => 'New HQ',
            'is_headquarter' => 1
        ]);
    }

    public function tearDown(): void
    {
        DB::rollBack(); // Explicit tranzakció visszagörgetés
        parent::tearDown();
    }
}
