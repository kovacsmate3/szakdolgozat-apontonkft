<?php

namespace Tests\Feature\RoadRecord;

use App\Models\Location;
use App\Models\TravelPurposeDictionary;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;
use PHPUnit\Framework\Attributes\Test;

class LocationPurposeTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    protected function setUp(): void
    {
        parent::setUp();

        // Hitelesített felhasználó létrehozása a tesztekhez
        Sanctum::actingAs(
            User::factory()->create()
        );
    }

    #[Test]
    public function test_user_can_get_all_travel_purposes_for_location()
    {
        // Létrehozunk egy helyszínt és utazási célokat
        $location = Location::factory()->create();
        $travelPurposes = TravelPurposeDictionary::factory()->count(3)->create();

        // Hozzárendeljük az utazási célokat a helyszínhez
        $location->travelPurposes()->attach($travelPurposes->pluck('id')->toArray());

        // API hívás
        $response = $this->getJson("/api/locations/{$location->id}/travel-purposes");

        // Ellenőrzés
        $response->assertStatus(200)
            ->assertJsonCount(3, 'travel_purposes');
    }

    #[Test]
    public function test_user_can_add_travel_purpose_to_location()
    {
        // Létrehozunk egy helyszínt és utazási célt
        $location = Location::factory()->create();
        $travelPurpose = TravelPurposeDictionary::factory()->create();

        // API hívás
        $response = $this->postJson("/api/locations/{$location->id}/travel-purposes", [
            'travel_purposes' => [$travelPurpose->id]
        ]);

        // Ellenőrzés
        $response->assertStatus(200)
            ->assertJsonPath('message', "Utazási célok sikeresen hozzárendelve a(z) {$location->name} helyszínhez.");

        // Ellenőrizzük az adatbázisban is
        $this->assertDatabaseHas('location_purpose', [
            'location_id' => $location->id,
            'travel_purpose_id' => $travelPurpose->id
        ]);
    }

    #[Test]
    public function test_user_can_view_specific_travel_purpose_for_location()
    {
        // Létrehozunk egy helyszínt és utazási célt
        $location = Location::factory()->create();
        $travelPurpose = TravelPurposeDictionary::factory()->create();

        // Hozzárendeljük az utazási célt a helyszínhez
        $location->travelPurposes()->attach($travelPurpose->id);

        // API hívás
        $response = $this->getJson("/api/locations/{$location->id}/travel-purposes/{$travelPurpose->id}");

        // Ellenőrzés
        $response->assertStatus(200)
            ->assertJsonPath('id', $travelPurpose->id)
            ->assertJsonPath('travel_purpose', $travelPurpose->travel_purpose);
    }

    #[Test]
    public function test_user_can_delete_travel_purpose_from_location()
    {
        // Létrehozunk egy helyszínt és utazási célt
        $location = Location::factory()->create();
        $travelPurpose = TravelPurposeDictionary::factory()->create();

        // Hozzárendeljük az utazási célt a helyszínhez
        $location->travelPurposes()->attach($travelPurpose->id);

        // API hívás
        $response = $this->deleteJson("/api/locations/{$location->id}/travel-purposes/{$travelPurpose->id}");

        // Ellenőrzés
        $response->assertStatus(200)
            ->assertJsonPath('message', "Az utazási cél ({$travelPurpose->travel_purpose}) sikeresen eltávolítva a(z) {$location->name} helyszíntől.");

        // Ellenőrizzük az adatbázisban is
        $this->assertDatabaseMissing('location_purpose', [
            'location_id' => $location->id,
            'travel_purpose_id' => $travelPurpose->id
        ]);
    }

    #[Test]
    public function test_user_can_sync_travel_purposes_for_location()
    {
        // Létrehozunk egy helyszínt és utazási célokat
        $location = Location::factory()->create();
        $oldTravelPurpose = TravelPurposeDictionary::factory()->create();
        $newTravelPurposes = TravelPurposeDictionary::factory()->count(2)->create();

        // Először hozzárendeljük a régi utazási célt
        $location->travelPurposes()->attach($oldTravelPurpose->id);

        // API hívás a szinkronizációhoz
        $response = $this->postJson("/api/locations/{$location->id}/travel-purposes/sync", [
            'travel_purposes' => $newTravelPurposes->pluck('id')->toArray()
        ]);

        // Ellenőrzés
        $response->assertStatus(200)
            ->assertJsonCount(2, 'travel_purposes')
            ->assertJsonPath('message', "Utazási célok sikeresen szinkronizálva a(z) {$location->name} helyszínhez.");

        // Ellenőrizzük, hogy a régi eltűnt, és csak az újak vannak meg
        $this->assertDatabaseMissing('location_purpose', [
            'location_id' => $location->id,
            'travel_purpose_id' => $oldTravelPurpose->id
        ]);

        foreach ($newTravelPurposes as $purpose) {
            $this->assertDatabaseHas('location_purpose', [
                'location_id' => $location->id,
                'travel_purpose_id' => $purpose->id
            ]);
        }
    }

    #[Test]
    public function test_location_not_found_returns_proper_message()
    {
        // Nemlétező helyszín ID
        $nonExistentId = 999;

        // API hívás
        $response = $this->getJson("/api/locations/{$nonExistentId}/travel-purposes");

        // Ellenőrzés
        $response->assertStatus(404)
            ->assertJsonPath('message', "A megadott azonosítójú (ID: {$nonExistentId}) helyszín nem található.");
    }

    #[Test]
    public function test_travel_purpose_not_found_returns_proper_message()
    {
        // Létrehozunk egy helyszínt
        $location = Location::factory()->create();
        $nonExistentId = 999;

        // API hívás
        $response = $this->getJson("/api/locations/{$location->id}/travel-purposes/{$nonExistentId}");

        // Ellenőrzés
        $response->assertStatus(404)
            ->assertJsonPath('message', "Az utazási cél (ID: {$nonExistentId}) nem tartozik a(z) {$location->name} helyszínhez, vagy nem létezik.");
    }
}
