<?php

namespace Tests\Unit\Controllers;

use App\Http\Controllers\Api\RoadRecord\LocationController;
use App\Models\Location;
use App\Models\User;
use App\Services\AddressService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Mockery;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;


class LocationControllerTest extends TestCase
{
    use RefreshDatabase;

    protected $locationController;
    protected $addressServiceMock;

    public function setUp(): void
    {
        parent::setUp();
        if (\DB::transactionLevel() > 0) {
            \DB::rollBack();
        }
        $this->addressServiceMock = Mockery::mock(AddressService::class);
        $this->locationController = new LocationController($this->addressServiceMock);
    }

    #[Test]
    public function index_returns_all_locations()
    {
        // Create some test data
        $locations = Location::factory()->count(3)->create();

        // Call the index method
        $request = new Request();
        $response = $this->locationController->index($request);

        // Assert the response has HTTP 200 status
        $this->assertEquals(Response::HTTP_OK, $response->getStatusCode());

        // Convert response to array and check it contains all locations
        $responseData = json_decode($response->getContent(), true);
        $this->assertCount(3, $responseData);
    }

    #[Test]
    public function index_filters_by_location_type()
    {
        // Create locations with different types
        Location::factory()->create(['location_type' => 'partner']);
        Location::factory()->create(['location_type' => 'töltőállomás']);
        Location::factory()->create(['location_type' => 'töltőállomás']);

        // Create a request with location_type filter
        $request = new Request(['location_type' => 'töltőállomás']);

        // Call the index method
        $response = $this->locationController->index($request);

        // Assert the response has HTTP 200 status
        $this->assertEquals(Response::HTTP_OK, $response->getStatusCode());

        // Convert response to array and check it contains only the filtered locations
        $responseData = json_decode($response->getContent(), true);
        $this->assertCount(2, $responseData);
        $this->assertEquals('töltőállomás', $responseData[0]['location_type']);
        $this->assertEquals('töltőállomás', $responseData[1]['location_type']);
    }

    #[Test]
    public function index_filters_by_search_term()
    {
        // Create locations with different names
        Location::factory()->create(['name' => 'MOL Töltőállomás']);
        Location::factory()->create(['name' => 'Shell Benzinkút']);
        Location::factory()->create(['name' => 'Iroda']);

        // Create a request with search filter
        $request = new Request(['search' => 'töltő']);

        // Call the index method
        $response = $this->locationController->index($request);

        // Assert the response has HTTP 200 status
        $this->assertEquals(Response::HTTP_OK, $response->getStatusCode());

        // Convert response to array and check it contains only the filtered locations
        $responseData = json_decode($response->getContent(), true);
        $this->assertCount(1, $responseData);
        $this->assertEquals('MOL Töltőállomás', $responseData[0]['name']);
    }

    #[Test]
    public function store_creates_new_location_with_address()
    {
        // Create a user
        $user = User::factory()->create();
        Auth::shouldReceive('user')->andReturn($user);

        // Location data for validation
        $locationData = [
            'name' => 'Test Location',
            'location_type' => 'partner',
            'is_headquarter' => false
        ];

        // Address data for validation
        $addressData = [
            'country' => 'Magyarország',
            'postalcode' => 1151,
            'city' => 'Budapest',
            'road_name' => 'Test Road',
            'public_space_type' => 'utca',
            'building_number' => '1'
        ];

        // Mock validation and services
        $locationRequestMock = Mockery::mock('App\Http\Requests\LocationRequest');
        $locationRequestMock->shouldReceive('validated')->andReturn($locationData);

        $addressRequestMock = Mockery::mock('App\Http\Requests\AddressRequest');
        $addressRequestMock->shouldReceive('validated')->andReturn($addressData);

        // Mock DB check for duplicate address
        $queryMock = Mockery::mock('Illuminate\Database\Eloquent\Builder');
        $queryMock->shouldReceive('where')->andReturnSelf();
        $queryMock->shouldReceive('where')->andReturnSelf();
        $queryMock->shouldReceive('where')->andReturnSelf();
        $queryMock->shouldReceive('where')->andReturnSelf();
        $queryMock->shouldReceive('where')->andReturnSelf();
        $queryMock->shouldReceive('where')->andReturnSelf();
        $queryMock->shouldReceive('exists')->andReturn(false);

        // Create a new location with the mock address service
        $newLocation = new Location($locationData);
        $newLocation->id = 1;

        // Mock the address service's createLocationWithAddress method
        $this->addressServiceMock
            ->shouldReceive('createLocationWithAddress')
            ->with(Mockery::on(function ($arg) use ($locationData, $user) {
                return $arg['name'] === $locationData['name'] &&
                    $arg['location_type'] === $locationData['location_type'] &&
                    $arg['user_id'] === $user->id;
            }), $addressData)
            ->andReturn($newLocation);

        // Call the store method
        $response = $this->locationController->store($locationRequestMock, $addressRequestMock);

        // Assert the response has HTTP 201 status
        $this->assertEquals(Response::HTTP_CREATED, $response->getStatusCode());

        // Assert the response contains the success message
        $responseData = json_decode($response->getContent(), true);
        $this->assertEquals('A helyszín sikeresen létrehozva.', $responseData['message']);
        $this->assertEquals($newLocation->id, $responseData['location']['id']);
    }

    #[Test]
    public function store_returns_forbidden_if_non_admin_creates_headquarter()
    {
        // Create a user with a non-admin role
        $user = User::factory()->create();
        $role = Mockery::mock();
        $role->slug = 'employee';
        $user->role = $role;

        Auth::shouldReceive('user')->andReturn($user);

        // Location data with headquarter type
        $locationData = [
            'location_type' => 'telephely',
            'is_headquarter' => false
        ];

        // Mock validation
        $locationRequestMock = Mockery::mock('App\Http\Requests\LocationRequest');
        $locationRequestMock->shouldReceive('validated')->andReturn($locationData);

        $addressRequestMock = Mockery::mock('App\Http\Requests\AddressRequest');

        // Call the store method
        $response = $this->locationController->store($locationRequestMock, $addressRequestMock);

        // Assert the response has HTTP 403 status
        $this->assertEquals(Response::HTTP_FORBIDDEN, $response->getStatusCode());

        // Assert the response contains the error message
        $responseData = json_decode($response->getContent(), true);
        $this->assertEquals('Telephely létrehozására nincs jogosultsága.', $responseData['message']);
    }

    #[Test]
    public function show_returns_location_by_id()
    {
        // Create a location
        $location = Location::factory()->create(['name' => 'Test Location']);

        // Create a request
        $request = new Request();

        // Call the show method
        $response = $this->locationController->show($request, $location->id);

        // Assert the response has HTTP 200 status
        $this->assertEquals(Response::HTTP_OK, $response->getStatusCode());

        // Assert the response contains the location data
        $responseData = json_decode($response->getContent(), true);
        $this->assertEquals($location->id, $responseData['id']);
        $this->assertEquals('Test Location', $responseData['name']);
    }

    #[Test]
    public function show_returns_not_found_for_invalid_id()
    {
        // Create a request
        $request = new Request();

        // Call the show method with a non-existent ID
        $response = $this->locationController->show($request, 9999);

        // Assert the response has HTTP 404 status
        $this->assertEquals(Response::HTTP_NOT_FOUND, $response->getStatusCode());

        // Assert the response contains the error message
        $responseData = json_decode($response->getContent(), true);
        $this->assertEquals('A megadott azonosítójú (ID: 9999) helyszín nem található.', $responseData['message']);
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
