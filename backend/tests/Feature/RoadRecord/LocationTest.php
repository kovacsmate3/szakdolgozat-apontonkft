<?php

namespace Tests\Feature\RoadRecord;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use App\Models\Role;
use App\Models\Location;
use App\Models\Address;
use App\Models\TravelPurposeDictionary;
use PHPUnit\Framework\Attributes\Test;

class LocationTest extends TestCase
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
    public function test_user_can_list_locations()
    {
        // Create some test locations
        Location::create([
            'name' => 'Headquarters',
            'location_type' => 'telephely',
            'is_headquarter' => true,
        ]);

        Location::create([
            'name' => 'Gas Station',
            'location_type' => 'töltőállomás',
            'is_headquarter' => false,
        ]);

        $response = $this->actingAs($this->regularUser)
            ->getJson('/api/locations');

        $response->assertStatus(200)
            ->assertJsonCount(2);
    }

    #[Test]
    public function test_user_can_create_location()
    {
        $locationData = [
            'name' => 'New Office',
            'location_type' => 'partner', // Vagy 'egyéb', ha nem telephely
            'is_headquarter' => false,

            // Kötelező címadatok hozzáadása
            'country' => 'Magyarország',
            'postalcode' => 1000,
            'city' => 'Budapest',
            'road_name' => 'Teszt',
            'public_space_type' => 'utca',
            'building_number' => '1',
        ];

        $response = $this->actingAs($this->regularUser)
            ->postJson('/api/locations', $locationData);

        $response->assertStatus(201)
            ->assertJson([
                'message' => 'A helyszín sikeresen létrehozva.'
            ]);

        $this->assertDatabaseHas('locations', [
            'name' => 'New Office',
            'location_type' => 'partner'
        ]);

        // Opcionálisan ellenőrizheted a címet is
        $this->assertDatabaseHas('addresses', [
            'city' => 'Budapest',
            'road_name' => 'Teszt'
        ]);
    }

    #[Test]
    public function test_location_type_validation()
    {
        $invalidLocationData = [
            'name' => 'Invalid Location',
            'location_type' => 'invalid_type', // Not in the allowed list
        ];

        $response = $this->actingAs($this->regularUser)
            ->postJson('/api/locations', $invalidLocationData);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['location_type']);
    }

    #[Test]
    public function test_user_can_update_location()
    {
        $location = Location::create([
            'name' => 'Old Name',
            'location_type' => 'partner',
            'is_headquarter' => false,
            'user_id' => $this->regularUser->id,
        ]);

        $updateData = [
            'name' => 'Updated Name',
            'is_headquarter' => true,
        ];

        $response = $this->actingAs($this->regularUser)
            ->putJson("/api/locations/{$location->id}", $updateData);

        $response->assertStatus(200)
            ->assertJson([
                'message' => 'A helyszín adatai sikeresen frissítve lettek.'
            ]);

        $this->assertDatabaseHas('locations', [
            'id' => $location->id,
            'name' => 'Updated Name',
            'is_headquarter' => true
        ]);
    }

    #[Test]
    public function test_user_can_view_location_with_relations()
    {
        $location = Location::create([
            'name' => 'Main Office',
            'location_type' => 'telephely',
            'is_headquarter' => true,
        ]);

        // Create an address for this location
        Address::create([
            'location_id' => $location->id,
            'country' => 'Magyarország',
            'postalcode' => 1000,
            'city' => 'Budapest',
            'road_name' => 'Main',
            'public_space_type' => 'utca',
            'building_number' => '1',
        ]);

        $response = $this->actingAs($this->regularUser)
            ->getJson("/api/locations/{$location->id}?include=address");

        $response->assertStatus(200)
            ->assertJsonStructure([
                'id',
                'name',
                'location_type',
                'is_headquarter',
                'address' => [
                    'id',
                    'location_id',
                    'country',
                    'postalcode',
                    'city',
                    'road_name',
                    'public_space_type',
                    'building_number'
                ]
            ]);
    }

    #[Test]
    public function test_location_cannot_be_deleted_with_related_records()
    {
        $start = Location::factory()->create();
        $destination = Location::factory()->create();
        $car = \App\Models\Car::factory()->create(['user_id' => $this->regularUser->id]);

        // Hozz létre egy startTrip kapcsolódó rekordot (pl. Trip modellel)
        \App\Models\Trip::factory()->create([
            'start_location_id' => $start->id,
            'destination_location_id' => $destination->id,
            'car_id' => $car->id,
            'user_id' => $this->regularUser->id,
        ]);

        $response = $this->actingAs($this->adminUser)
            ->deleteJson("/api/locations/{$start->id}");

        $response->assertStatus(403)
            ->assertJson([
                'message' => 'Ez a helyszín utazásokhoz vagy üzemanyag költségekhez van rendelve, ezért nem törölhető.'
            ]);
    }

    #[Test]
    public function test_deleting_location_also_deletes_address()
    {
        $location = Location::create([
            'name' => 'Deletable Location',
            'location_type' => 'egyéb',
            'is_headquarter' => false,
        ]);

        // Create an address for this location
        $address = Address::create([
            'location_id' => $location->id,
            'country' => 'Magyarország',
            'postalcode' => 1000,
            'city' => 'Budapest',
            'road_name' => 'Temp',
            'public_space_type' => 'utca',
            'building_number' => '1',
        ]);

        $response = $this->actingAs($this->adminUser)
            ->deleteJson("/api/locations/{$location->id}");

        $response->assertStatus(200)
            ->assertJson([
                'message' => "Deletable Location helyszín sikeresen törölve."
            ]);

        $this->assertDatabaseMissing('locations', [
            'id' => $location->id
        ]);

        $this->assertDatabaseMissing('addresses', [
            'id' => $address->id
        ]);
    }

    #[Test]
    public function test_user_can_create_address()
    {
        $addressData = [
            'country' => 'Magyarország',
            'postalcode' => 1000,
            'city' => 'Budapest',
            'road_name' => 'New',
            'public_space_type' => 'utca',
            'building_number' => '1',
        ];

        $response = $this->actingAs($this->regularUser)
            ->postJson('/api/addresses', $addressData);

        $response->assertStatus(201)
            ->assertJson([
                'message' => 'A cím sikeresen létrehozva.'
            ]);

        $this->assertDatabaseHas('addresses', [
            'city' => 'Budapest',
            'road_name' => 'New'
        ]);
    }

    #[Test]
    public function test_address_validation()
    {
        $invalidAddressData = [
            'country' => '', // Required field
            'postalcode' => 'abc', // Should be integer
            'city' => 'Budapest',
            'road_name' => 'Test',
            'public_space_type' => 'utca',
            'building_number' => '1',
        ];

        $response = $this->actingAs($this->regularUser)
            ->postJson('/api/addresses', $invalidAddressData);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['country', 'postalcode']);
    }

    public function test_user_can_update_address()
    {
        $address = Address::create([
            'country' => 'Magyarország',
            'postalcode' => 1000,
            'city' => 'Budapest',
            'road_name' => 'Old',
            'public_space_type' => 'utca',
            'building_number' => '1',
        ]);

        $updateData = [
            'city' => 'Debrecen',
            'postalcode' => 4000,
        ];

        $response = $this->actingAs($this->regularUser)
            ->putJson("/api/addresses/{$address->id}", $updateData);

        $response->assertStatus(200)
            ->assertJson([
                'message' => 'A cím adatai sikeresen frissítve lettek.'
            ]);

        $this->assertDatabaseHas('addresses', [
            'id' => $address->id,
            'city' => 'Debrecen',
            'postalcode' => 4000
        ]);
    }

    #[Test]
    public function test_user_can_assign_travel_purposes_to_location()
    {
        $location = Location::create([
            'name' => 'Business Office',
            'location_type' => 'telephely',
            'is_headquarter' => false,
        ]);

        // Create travel purposes
        $purpose1 = TravelPurposeDictionary::create([
            'travel_purpose' => 'Business Meeting',
            'type' => 'Üzleti',
            'is_system' => false,
        ]);

        $purpose2 = TravelPurposeDictionary::create([
            'travel_purpose' => 'Training',
            'type' => 'Üzleti',
            'is_system' => false,
        ]);

        $response = $this->actingAs($this->regularUser)
            ->postJson("/api/locations/{$location->id}/travel-purposes", [
                'travel_purposes' => [$purpose1->id, $purpose2->id]
            ]);

        $response->assertStatus(200)
            ->assertJson([
                'message' => "Utazási célok sikeresen hozzárendelve a(z) Business Office helyszínhez."
            ]);

        // Check the pivot table entries
        $this->assertDatabaseHas('location_purpose', [
            'location_id' => $location->id,
            'travel_purpose_id' => $purpose1->id
        ]);

        $this->assertDatabaseHas('location_purpose', [
            'location_id' => $location->id,
            'travel_purpose_id' => $purpose2->id
        ]);
    }

    #[Test]
    public function test_user_can_sync_travel_purposes_to_location()
    {
        $location = Location::create([
            'name' => 'Office Location',
            'location_type' => 'telephely',
            'is_headquarter' => false,
        ]);

        // Create travel purposes
        $purpose1 = TravelPurposeDictionary::create([
            'travel_purpose' => 'Purpose 1',
            'type' => 'Üzleti',
            'is_system' => false,
        ]);

        $purpose2 = TravelPurposeDictionary::create([
            'travel_purpose' => 'Purpose 2',
            'type' => 'Üzleti',
            'is_system' => false,
        ]);

        $purpose3 = TravelPurposeDictionary::create([
            'travel_purpose' => 'Purpose 3',
            'type' => 'Üzleti',
            'is_system' => false,
        ]);

        // First assign two purposes
        $location->travelPurposes()->attach([$purpose1->id, $purpose2->id]);

        // Now sync with a different set (should replace the existing ones)
        $response = $this->actingAs($this->regularUser)
            ->postJson("/api/locations/{$location->id}/travel-purposes/sync", [
                'travel_purposes' => [$purpose2->id, $purpose3->id]
            ]);

        $response->assertStatus(200)
            ->assertJson([
                'message' => "Utazási célok sikeresen szinkronizálva a(z) Office Location helyszínhez."
            ]);

        // Check the first purpose is no longer associated
        $this->assertDatabaseMissing('location_purpose', [
            'location_id' => $location->id,
            'travel_purpose_id' => $purpose1->id
        ]);

        // Check the other two purposes are associated
        $this->assertDatabaseHas('location_purpose', [
            'location_id' => $location->id,
            'travel_purpose_id' => $purpose2->id
        ]);

        $this->assertDatabaseHas('location_purpose', [
            'location_id' => $location->id,
            'travel_purpose_id' => $purpose3->id
        ]);
    }

    #[Test]
    public function test_user_can_remove_travel_purpose_from_location()
    {
        $location = Location::create([
            'name' => 'Test Location',
            'location_type' => 'telephely',
            'is_headquarter' => false,
        ]);

        $purpose = TravelPurposeDictionary::create([
            'travel_purpose' => 'Removable Purpose',
            'type' => 'Üzleti',
            'is_system' => false,
        ]);

        // Assign the purpose to the location
        $location->travelPurposes()->attach($purpose->id);

        // Now remove it
        $response = $this->actingAs($this->regularUser)
            ->deleteJson("/api/locations/{$location->id}/travel-purposes/{$purpose->id}");

        $response->assertStatus(200)
            ->assertJson([
                'message' => "Az utazási cél (Removable Purpose) sikeresen eltávolítva a(z) Test Location helyszíntől."
            ]);

        // Check it's no longer in the pivot table
        $this->assertDatabaseMissing('location_purpose', [
            'location_id' => $location->id,
            'travel_purpose_id' => $purpose->id
        ]);
    }

    #[Test]
    public function test_address_cannot_be_deleted_with_projects()
    {
        $address = Address::factory()->create();

        \App\Models\Project::factory()->create([
            'address_id' => $address->id
        ]);

        $response = $this->actingAs($this->adminUser)
            ->deleteJson("/api/addresses/{$address->id}");

        $response->assertStatus(403)
            ->assertJson([
                'message' => 'Ez a cím projektekhez van rendelve, ezért nem törölhető.'
            ]);
    }

    #[Test]
    public function test_user_can_delete_address_without_relations()
    {
        $address = Address::create([
            'country' => 'Magyarország',
            'postalcode' => 1000,
            'city' => 'Budapest',
            'road_name' => 'Deletable',
            'public_space_type' => 'utca',
            'building_number' => '1',
        ]);

        $response = $this->actingAs($this->adminUser)
            ->deleteJson("/api/addresses/{$address->id}");

        $response->assertStatus(200);

        $this->assertDatabaseMissing('addresses', [
            'id' => $address->id
        ]);
    }
}
