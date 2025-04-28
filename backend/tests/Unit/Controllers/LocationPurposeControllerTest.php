<?php

namespace Tests\Unit\Controllers;

use App\Http\Controllers\Api\RoadRecord\LocationPurposeController;
use App\Models\Location;
use App\Models\TravelPurposeDictionary;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class LocationPurposeControllerTest extends TestCase
{
    use RefreshDatabase;

    protected $locationPurposeController;

    public function setUp(): void
    {
        parent::setUp();
        if (\DB::transactionLevel() > 0) {
            \DB::rollBack();
        }
        $this->locationPurposeController = new LocationPurposeController();
    }

    #[Test]
    public function index_returns_all_travel_purposes_for_location()
    {
        // Create a location
        $location = Location::factory()->create(['name' => 'Test Location']);

        // Create some travel purposes and attach them to the location
        $travelPurpose1 = TravelPurposeDictionary::factory()->create(['travel_purpose' => 'Business Meeting']);
        $travelPurpose2 = TravelPurposeDictionary::factory()->create(['travel_purpose' => 'Client Visit']);

        $location->travelPurposes()->attach([$travelPurpose1->id, $travelPurpose2->id]);

        // Call the index method
        $response = $this->locationPurposeController->index($location->id);

        // Assert the response has HTTP 200 status
        $this->assertEquals(Response::HTTP_OK, $response->getStatusCode());

        // Assert the response contains the location data and travel purposes
        $responseData = json_decode($response->getContent(), true);
        $this->assertEquals($location->id, $responseData['location']['id']);
        $this->assertEquals('Test Location', $responseData['location']['name']);
        $this->assertCount(2, $responseData['travel_purposes']);
        $this->assertEquals('Business Meeting', $responseData['travel_purposes'][0]['travel_purpose']);
        $this->assertEquals('Client Visit', $responseData['travel_purposes'][1]['travel_purpose']);
    }

    #[Test]
    public function index_returns_not_found_for_invalid_location_id()
    {
        // Call the index method with a non-existent location ID
        $response = $this->locationPurposeController->index(9999);

        // Assert the response has HTTP 404 status
        $this->assertEquals(Response::HTTP_NOT_FOUND, $response->getStatusCode());

        // Assert the response contains the error message
        $responseData = json_decode($response->getContent(), true);
        $this->assertEquals('A megadott azonosítójú (ID: 9999) helyszín nem található.', $responseData['message']);
    }

    #[Test]
    public function store_adds_travel_purposes_to_location()
    {
        // Create a location
        $location = Location::factory()->create(['name' => 'Test Location']);

        // Create travel purposes
        $travelPurpose1 = TravelPurposeDictionary::factory()->create();
        $travelPurpose2 = TravelPurposeDictionary::factory()->create();

        // Create request data
        $requestData = [
            'travel_purposes' => [$travelPurpose1->id, $travelPurpose2->id]
        ];

        // Create a request
        $request = new Request($requestData);
        $request->setMethod('POST');

        // Call the store method
        $response = $this->locationPurposeController->store($request, $location->id);

        // Assert the response has HTTP 200 status
        $this->assertEquals(Response::HTTP_OK, $response->getStatusCode());

        // Assert the response contains the success message
        $responseData = json_decode($response->getContent(), true);
        $this->assertEquals("Utazási célok sikeresen hozzárendelve a(z) Test Location helyszínhez.", $responseData['message']);

        // Assert the travel purposes were attached to the location
        $this->assertCount(2, $responseData['travel_purposes']);
        $this->assertContains($travelPurpose1->id, array_column($responseData['travel_purposes'], 'id'));
        $this->assertContains($travelPurpose2->id, array_column($responseData['travel_purposes'], 'id'));

        // Assert the relationship is in the database
        $this->assertDatabaseHas('location_purpose', [
            'location_id' => $location->id,
            'travel_purpose_id' => $travelPurpose1->id
        ]);
        $this->assertDatabaseHas('location_purpose', [
            'location_id' => $location->id,
            'travel_purpose_id' => $travelPurpose2->id
        ]);
    }

    #[Test]
    public function store_returns_not_found_for_invalid_location_id()
    {
        // Create a request
        $request = new Request(['travel_purposes' => [1, 2]]);
        $request->setMethod('POST');

        // Call the store method with a non-existent location ID
        $response = $this->locationPurposeController->store($request, 9999);

        // Assert the response has HTTP 404 status
        $this->assertEquals(Response::HTTP_NOT_FOUND, $response->getStatusCode());

        // Assert the response contains the error message
        $responseData = json_decode($response->getContent(), true);
        $this->assertEquals('A megadott azonosítójú (ID: 9999) helyszín nem található.', $responseData['message']);
    }

    #[Test]
    public function show_returns_specific_travel_purpose_for_location()
    {
        // Create a location
        $location = Location::factory()->create(['name' => 'Test Location']);

        // Create a travel purpose and attach it to the location
        $travelPurpose = TravelPurposeDictionary::factory()->create(['travel_purpose' => 'Business Meeting']);
        $location->travelPurposes()->attach($travelPurpose->id);

        // Call the show method
        $response = $this->locationPurposeController->show($location->id, $travelPurpose->id);

        // Assert the response has HTTP 200 status
        $this->assertEquals(Response::HTTP_OK, $response->getStatusCode());

        // Assert the response contains the travel purpose data
        $responseData = json_decode($response->getContent(), true);
        $this->assertEquals($travelPurpose->id, $responseData['id']);
        $this->assertEquals('Business Meeting', $responseData['travel_purpose']);
    }

    #[Test]
    public function show_returns_not_found_for_unattached_travel_purpose()
    {
        // Create a location
        $location = Location::factory()->create(['name' => 'Test Location']);

        // Create a travel purpose but don't attach it to the location
        $travelPurpose = TravelPurposeDictionary::factory()->create();

        // Call the show method
        $response = $this->locationPurposeController->show($location->id, $travelPurpose->id);

        // Assert the response has HTTP 404 status
        $this->assertEquals(Response::HTTP_NOT_FOUND, $response->getStatusCode());

        // Assert the response contains the error message
        $responseData = json_decode($response->getContent(), true);
        $this->assertEquals("Az utazási cél (ID: {$travelPurpose->id}) nem tartozik a(z) Test Location helyszínhez, vagy nem létezik.", $responseData['message']);
    }

    #[Test]
    public function destroy_removes_travel_purpose_from_location()
    {
        // Create a location
        $location = Location::factory()->create(['name' => 'Test Location']);

        // Create a travel purpose and attach it to the location
        $travelPurpose = TravelPurposeDictionary::factory()->create(['travel_purpose' => 'Business Meeting']);
        $location->travelPurposes()->attach($travelPurpose->id);

        // Call the destroy method
        $response = $this->locationPurposeController->destroy($location->id, $travelPurpose->id);

        // Assert the response has HTTP 200 status
        $this->assertEquals(Response::HTTP_OK, $response->getStatusCode());

        // Assert the response contains the success message
        $responseData = json_decode($response->getContent(), true);
        $this->assertEquals("Az utazási cél (Business Meeting) sikeresen eltávolítva a(z) Test Location helyszíntől.", $responseData['message']);

        // Assert the relationship is removed from the database
        $this->assertDatabaseMissing('location_purpose', [
            'location_id' => $location->id,
            'travel_purpose_id' => $travelPurpose->id
        ]);
    }

    #[Test]
    public function sync_replaces_all_travel_purposes_for_location()
    {
        // Create a location
        $location = Location::factory()->create(['name' => 'Test Location']);

        // Create initial travel purposes and attach them to the location
        $oldTravelPurpose = TravelPurposeDictionary::factory()->create();
        $location->travelPurposes()->attach($oldTravelPurpose->id);

        // Create new travel purposes for syncing
        $newTravelPurpose1 = TravelPurposeDictionary::factory()->create();
        $newTravelPurpose2 = TravelPurposeDictionary::factory()->create();

        // Create request data
        $requestData = [
            'travel_purposes' => [$newTravelPurpose1->id, $newTravelPurpose2->id]
        ];

        // Create a request
        $request = new Request($requestData);
        $request->setMethod('POST');

        // Call the sync method
        $response = $this->locationPurposeController->sync($request, $location->id);

        // Assert the response has HTTP 200 status
        $this->assertEquals(Response::HTTP_OK, $response->getStatusCode());

        // Assert the response contains the success message
        $responseData = json_decode($response->getContent(), true);
        $this->assertEquals("Utazási célok sikeresen szinkronizálva a(z) Test Location helyszínhez.", $responseData['message']);

        // Assert the new travel purposes are attached to the location
        $this->assertCount(2, $responseData['travel_purposes']);
        $this->assertContains($newTravelPurpose1->id, array_column($responseData['travel_purposes'], 'id'));
        $this->assertContains($newTravelPurpose2->id, array_column($responseData['travel_purposes'], 'id'));

        // Assert the old travel purpose is no longer attached
        $this->assertNotContains($oldTravelPurpose->id, array_column($responseData['travel_purposes'], 'id'));

        // Assert the database state
        $this->assertDatabaseHas('location_purpose', [
            'location_id' => $location->id,
            'travel_purpose_id' => $newTravelPurpose1->id
        ]);
        $this->assertDatabaseHas('location_purpose', [
            'location_id' => $location->id,
            'travel_purpose_id' => $newTravelPurpose2->id
        ]);
        $this->assertDatabaseMissing('location_purpose', [
            'location_id' => $location->id,
            'travel_purpose_id' => $oldTravelPurpose->id
        ]);
    }

    public function tearDown(): void
    {
        if (\DB::transactionLevel() > 0) {
            \DB::rollBack();
        }
        parent::tearDown();
    }
}
