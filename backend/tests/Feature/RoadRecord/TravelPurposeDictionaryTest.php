<?php

namespace Tests\Feature\RoadRecord;

use App\Models\TravelPurposeDictionary;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;
use PHPUnit\Framework\Attributes\Test;

class TravelPurposeDictionaryTest extends TestCase
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
    public function test_user_can_get_all_travel_purposes()
    {
        // Utazási célok létrehozása
        $travelPurposes = TravelPurposeDictionary::factory()->count(3)->create();

        // API hívás
        $response = $this->getJson('/api/travel-purpose-dictionaries');

        // Ellenőrzés
        $response->assertStatus(200);
        $this->assertCount(3, $response->json());
    }

    #[Test]
    public function test_user_can_create_travel_purpose()
    {
        // Adatok előkészítése
        $data = [
            'travel_purpose' => 'Teszt utazási cél',
            'type' => 'Üzleti',
            'note' => 'Teszt megjegyzés',
            'is_system' => false,
        ];

        // API hívás
        $response = $this->postJson('/api/travel-purpose-dictionaries', $data);

        // Ellenőrzés
        $response->assertStatus(201)
            ->assertJsonPath('message', 'Az utazási cél sikeresen létrehozva.')
            ->assertJsonPath('travel_purpose.travel_purpose', 'Teszt utazási cél')
            ->assertJsonPath('travel_purpose.type', 'Üzleti');

        // Ellenőrizzük, hogy az adatbázisban is megvan-e
        $this->assertDatabaseHas('travel_purpose_dictionaries', [
            'travel_purpose' => 'Teszt utazási cél',
            'type' => 'Üzleti',
        ]);
    }

    #[Test]
    public function test_user_can_show_travel_purpose()
    {
        // Utazási cél létrehozása
        $travelPurpose = TravelPurposeDictionary::factory()->create();

        // API hívás
        $response = $this->getJson('/api/travel-purpose-dictionaries/' . $travelPurpose->id);

        // Ellenőrzés
        $response->assertStatus(200)
            ->assertJsonPath('id', $travelPurpose->id)
            ->assertJsonPath('travel_purpose', $travelPurpose->travel_purpose)
            ->assertJsonPath('type', $travelPurpose->type);
    }

    #[Test]
    public function test_user_can_update_travel_purpose()
    {
        // Utazási cél létrehozása
        $travelPurpose = TravelPurposeDictionary::factory()->create();

        // Frissítési adatok
        $data = [
            'travel_purpose' => 'Módosított utazási cél',
            'type' => 'Magán',
        ];

        // API hívás
        $response = $this->putJson('/api/travel-purpose-dictionaries/' . $travelPurpose->id, $data);

        // Ellenőrzés
        $response->assertStatus(200)
            ->assertJsonPath('message', 'Az utazási cél adatai sikeresen frissítve lettek.')
            ->assertJsonPath('travel_purpose.travel_purpose', 'Módosított utazási cél')
            ->assertJsonPath('travel_purpose.type', 'Magán');

        // Ellenőrizzük, hogy az adatbázisban is frissült-e
        $this->assertDatabaseHas('travel_purpose_dictionaries', [
            'id' => $travelPurpose->id,
            'travel_purpose' => 'Módosított utazási cél',
            'type' => 'Magán',
        ]);
    }

    #[Test]
    public function test_user_can_delete_travel_purpose()
    {
        // Utazási cél létrehozása (nem rendszerszintű)
        $travelPurpose = TravelPurposeDictionary::factory()->create([
            'is_system' => false
        ]);

        // API hívás
        $response = $this->deleteJson('/api/travel-purpose-dictionaries/' . $travelPurpose->id);

        // Ellenőrzés
        $response->assertStatus(200)
            ->assertJsonPath('message', "'{$travelPurpose->travel_purpose}' utazási cél sikeresen törölve.");

        // Ellenőrizzük, hogy az adatbázisból valóban törlődött-e
        $this->assertDatabaseMissing('travel_purpose_dictionaries', ['id' => $travelPurpose->id]);
    }

    #[Test]
    public function test_validate_travel_purpose_creation()
    {
        // Hiányos adatok
        $data = [
            'travel_purpose' => '',
            'type' => '',
        ];

        // API hívás
        $response = $this->postJson('/api/travel-purpose-dictionaries', $data);

        // Ellenőrzés
        $response->assertStatus(422)
            ->assertJsonValidationErrors(['travel_purpose', 'type']);
    }

    #[Test]
    public function test_not_found_returns_proper_message()
    {
        // Nem létező azonosító
        $response = $this->getJson('/api/travel-purpose-dictionaries/999');

        // Ellenőrzés
        $response->assertStatus(404)
            ->assertJsonPath('message', 'A megadott azonosítójú (ID: 999) utazási cél nem található.');
    }

    #[Test]
    public function test_system_purpose_cannot_be_deleted()
    {
        // Rendszerszintű utazási cél létrehozása
        $travelPurpose = TravelPurposeDictionary::factory()->create([
            'is_system' => true
        ]);

        // Felhasználó létrehozása role nélkül (nem admin)
        $regularUser = User::factory()->create(['role_id' => null]);
        Sanctum::actingAs($regularUser);

        // API hívás
        $response = $this->deleteJson('/api/travel-purpose-dictionaries/' . $travelPurpose->id);

        // Ellenőrzés - a nem admin felhasználó nem törölhet rendszerszintű elemet
        $response->assertStatus(403)
            ->assertJsonPath('message', 'Rendszerszintű utazási cél nem törölhető.');
    }
}
