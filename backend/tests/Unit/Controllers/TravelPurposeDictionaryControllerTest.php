<?php

namespace Tests\Unit\Controllers;

use App\Http\Controllers\Api\RoadRecord\TravelPurposeDictionaryController;
use App\Models\Role;
use App\Models\TravelPurposeDictionary;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Mockery;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class TravelPurposeDictionaryControllerTest extends TestCase
{
    use RefreshDatabase;

    protected $travelPurposeController;

    public function setUp(): void
    {
        parent::setUp();
        if (\DB::transactionLevel() > 0) {
            \DB::rollBack();
        }
        $this->travelPurposeController = new TravelPurposeDictionaryController();
    }

    #[Test]
    public function index_returns_all_travel_purposes()
    {
        // Create a user
        $user = User::factory()->create();

        // Mock Auth facade
        Auth::shouldReceive('user')->andReturn($user);

        // Create travel purposes
        $travelPurposes = TravelPurposeDictionary::factory()->count(3)->create();

        // Create a request
        $request = new Request();

        // Call the index method
        $response = $this->travelPurposeController->index($request);

        // Assert the response has HTTP 200 status
        $this->assertEquals(Response::HTTP_OK, $response->getStatusCode());

        // Assert the response contains all travel purposes
        $responseData = json_decode($response->getContent(), true);
        $this->assertCount(3, $responseData);
    }

    #[Test]
    public function index_filters_by_type()
    {
        // Create a user
        $user = User::factory()->create();

        // Mock Auth facade
        Auth::shouldReceive('user')->andReturn($user);

        // Create travel purposes with different types
        TravelPurposeDictionary::factory()->create(['type' => 'Üzleti']);
        TravelPurposeDictionary::factory()->create(['type' => 'Magán']);
        TravelPurposeDictionary::factory()->create(['type' => 'Üzleti']);

        // Create a request with type filter
        $request = new Request(['type' => 'Üzleti']);

        // Call the index method
        $response = $this->travelPurposeController->index($request);

        // Assert the response has HTTP 200 status
        $this->assertEquals(Response::HTTP_OK, $response->getStatusCode());

        // Assert the response contains only the filtered travel purposes
        $responseData = json_decode($response->getContent(), true);
        $this->assertCount(2, $responseData);
        $this->assertEquals('Üzleti', $responseData[0]['type']);
        $this->assertEquals('Üzleti', $responseData[1]['type']);
    }

    #[Test]
    public function store_creates_new_travel_purpose_and_returns_success()
    {
        // Hozz létre egy szerepkört
        $employeeRole = Role::factory()->create([
            'slug' => 'employee'
        ]);

        // Create a user
        $user = User::factory()->create([
            'role_id' => $employeeRole->id
        ]);

        // Mock Auth facade
        Auth::shouldReceive('user')->andReturn($user);

        // Create travel purpose data
        $travelPurposeData = [
            'travel_purpose' => 'New Purpose',
            'type' => 'Üzleti',
            'note' => 'Test note'
        ];

        // Create a request
        $request = new Request($travelPurposeData);
        $request->setMethod('POST');

        // Call the store method
        $response = $this->travelPurposeController->store($request);

        // Assert the response has HTTP 201 status
        $this->assertEquals(Response::HTTP_CREATED, $response->getStatusCode());

        // Assert the response contains the success message
        $responseData = json_decode($response->getContent(), true);
        $this->assertEquals('Az utazási cél sikeresen létrehozva.', $responseData['message']);

        // Assert the travel purpose was created in the database
        $this->assertDatabaseHas('travel_purpose_dictionaries', [
            'travel_purpose' => 'New Purpose',
            'type' => 'Üzleti',
            'note' => 'Test note',
            'user_id' => $user->id
        ]);
    }

    #[Test]
    public function store_prevents_non_admin_from_creating_system_record()
    {
        // Hozz létre egy szerepkört
        $employeeRole = Role::factory()->create([
            'slug' => 'employee'
        ]);

        // Create a user
        $user = User::factory()->create([
            'role_id' => $employeeRole->id
        ]);

        // Mock Auth facade
        Auth::shouldReceive('user')->andReturn($user);

        // Create travel purpose data with is_system = true
        $travelPurposeData = [
            'travel_purpose' => 'System Purpose',
            'type' => 'Üzleti',
            'is_system' => true
        ];

        // Create a request
        $request = new Request($travelPurposeData);
        $request->setMethod('POST');

        // Call the store method
        $response = $this->travelPurposeController->store($request);

        // Assert the response has HTTP 403 status
        $this->assertEquals(Response::HTTP_FORBIDDEN, $response->getStatusCode());

        // Assert the response contains the error message
        $responseData = json_decode($response->getContent(), true);
        $this->assertEquals('Csak adminisztrátor hozhat létre rendszerszintű utazási célt.', $responseData['message']);

        // Assert no travel purpose was created
        $this->assertDatabaseMissing('travel_purpose_dictionaries', [
            'travel_purpose' => 'System Purpose'
        ]);
    }

    #[Test]
    public function show_returns_travel_purpose_by_id()
    {
        // Create a user
        $user = User::factory()->create();

        // Mock Auth facade
        Auth::shouldReceive('user')->andReturn($user);

        // Create a travel purpose
        $travelPurpose = TravelPurposeDictionary::factory()->create([
            'travel_purpose' => 'Test Purpose',
            'type' => 'Üzleti',
            'note' => 'Test note'
        ]);

        // Call the show method
        $response = $this->travelPurposeController->show($travelPurpose->id);

        // Assert the response has HTTP 200 status
        $this->assertEquals(Response::HTTP_OK, $response->getStatusCode());

        // Assert the response contains the travel purpose data
        $responseData = json_decode($response->getContent(), true);
        $this->assertEquals($travelPurpose->id, $responseData['id']);
        $this->assertEquals('Test Purpose', $responseData['travel_purpose']);
        $this->assertEquals('Üzleti', $responseData['type']);
        $this->assertEquals('Test note', $responseData['note']);
    }

    #[Test]
    public function update_modifies_travel_purpose_and_returns_success()
    {
        // Create a user
        $user = User::factory()->create();

        // Mock Auth facade
        Auth::shouldReceive('user')->andReturn($user);

        // Create a travel purpose
        $travelPurpose = TravelPurposeDictionary::factory()->create([
            'travel_purpose' => 'Old Purpose',
            'type' => 'Üzleti',
            'note' => 'Old note',
            'user_id' => $user->id,
            'is_system' => false
        ]);

        // Create update data
        $updateData = [
            'travel_purpose' => 'Updated Purpose',
            'note' => 'Updated note'
        ];

        // Create a request
        $request = new Request($updateData);
        $request->setMethod('PUT');

        // Call the update method
        $response = $this->travelPurposeController->update($request, $travelPurpose->id);

        // Assert the response has HTTP 200 status
        $this->assertEquals(Response::HTTP_OK, $response->getStatusCode());

        // Assert the response contains the success message
        $responseData = json_decode($response->getContent(), true);
        $this->assertEquals('Az utazási cél adatai sikeresen frissítve lettek.', $responseData['message']);

        // Assert the travel purpose was updated in the database
        $this->assertDatabaseHas('travel_purpose_dictionaries', [
            'id' => $travelPurpose->id,
            'travel_purpose' => 'Updated Purpose',
            'note' => 'Updated note',
            'type' => 'Üzleti' // Unchanged
        ]);
    }

    #[Test]
    public function update_returns_forbidden_for_unauthorized_user()
    {
        // Create a user
        $user = User::factory()->create();

        // Mock Auth facade
        Auth::shouldReceive('user')->andReturn($user);

        // Create a travel purpose
        $travelPurpose = TravelPurposeDictionary::factory()->create([
            'travel_purpose' => 'System Purpose',
            'is_system' => true
        ]);

        // Create update data
        $updateData = [
            'travel_purpose' => 'Attempted Update'
        ];

        // Create a request
        $request = new Request($updateData);
        $request->setMethod('PUT');

        // Call the update method
        $response = $this->travelPurposeController->update($request, $travelPurpose->id);

        // Assert the response has HTTP 403 status
        $this->assertEquals(Response::HTTP_FORBIDDEN, $response->getStatusCode());

        // Assert the response contains the error message
        $responseData = json_decode($response->getContent(), true);
        $this->assertEquals('Nincs jogosultsága módosítani ezt az utazási célt.', $responseData['message']);

        // Assert the travel purpose was not updated
        $this->assertDatabaseHas('travel_purpose_dictionaries', [
            'id' => $travelPurpose->id,
            'travel_purpose' => 'System Purpose'
        ]);
    }

    #[Test]
    public function destroy_deletes_travel_purpose_and_returns_success()
    {
        // Create a user
        $user = User::factory()->create();

        // Mock Auth facade
        Auth::shouldReceive('user')->andReturn($user);

        // Create a travel purpose
        $travelPurpose = TravelPurposeDictionary::factory()->create([
            'travel_purpose' => 'Deletable Purpose',
            'user_id' => $user->id,
            'is_system' => false
        ]);

        // Call the destroy method
        $response = $this->travelPurposeController->destroy($travelPurpose->id);

        // Assert the response has HTTP 200 status
        $this->assertEquals(Response::HTTP_OK, $response->getStatusCode());

        // Assert the response contains the success message
        $responseData = json_decode($response->getContent(), true);
        $this->assertEquals("'Deletable Purpose' utazási cél sikeresen törölve.", $responseData['message']);

        // Assert the travel purpose was deleted from the database
        $this->assertDatabaseMissing('travel_purpose_dictionaries', ['id' => $travelPurpose->id]);
    }

    #[Test]
    public function destroy_returns_forbidden_for_unauthorized_user()
    {
        // Create a user
        $user = User::factory()->create();

        // Mock Auth facade
        Auth::shouldReceive('user')->andReturn($user);

        // Create a travel purpose
        $travelPurpose = TravelPurposeDictionary::factory()->create([
            'travel_purpose' => 'System Purpose',
            'is_system' => true
        ]);

        // Call the destroy method
        $response = $this->travelPurposeController->destroy($travelPurpose->id);

        // Assert the response has HTTP 403 status
        $this->assertEquals(Response::HTTP_FORBIDDEN, $response->getStatusCode());

        // Assert the response contains the error message
        $responseData = json_decode($response->getContent(), true);
        $this->assertEquals('Nincs jogosultsága törölni ezt az utazási célt.', $responseData['message']);

        // Assert the travel purpose was not deleted
        $this->assertDatabaseHas('travel_purpose_dictionaries', [
            'id' => $travelPurpose->id,
            'travel_purpose' => 'System Purpose'
        ]);
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
