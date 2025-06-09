<?php

namespace Tests\Feature\Shared;

use App\Models\Address;
use App\Models\Location;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class AddressTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
    }

    #[Test]
    public function test_authenticated_user_can_view_addresses(): void
    {
        Sanctum::actingAs(User::factory()->create(), ['*']);

        $location = Location::factory()->create();
        $address = Address::factory()->create([
            'location_id' => $location->id,
        ]);

        $response = $this->getJson('/api/addresses');

        $response->assertStatus(200)
            ->assertJsonFragment([
                'city' => $address->city,
                'road_name' => $address->road_name,
                'building_number' => $address->building_number,
            ]);
    }

    #[Test]
    public function test_authenticated_user_can_create_address(): void
    {
        Sanctum::actingAs(User::factory()->create(), ['*']);

        $location = Location::factory()->create();

        $data = [
            'location_id' => $location->id,
            'country' => 'Magyarország',
            'postalcode' => 1234,
            'city' => 'Tesztváros',
            'road_name' => 'Teszt utca',
            'public_space_type' => 'utca',
            'building_number' => '12.',
        ];

        $response = $this->postJson('/api/addresses', $data);

        $response->assertStatus(201)
            ->assertJsonFragment(['city' => 'Tesztváros']);

        $this->assertDatabaseHas('addresses', $data);
    }

    #[Test]
    public function test_authenticated_user_can_update_address(): void
    {
        Sanctum::actingAs(User::factory()->create(), ['*']);

        $location = Location::factory()->create();
        $address = Address::factory()->create(['location_id' => $location->id]);

        $updated = [
            'location_id' => $location->id,
            'country' => 'Magyarország',
            'postalcode' => 9999,
            'city' => 'Frissítettváros',
            'road_name' => 'Új utca',
            'public_space_type' => 'köz',
            'building_number' => '5B',
        ];

        $response = $this->putJson("/api/addresses/{$address->id}", $updated);

        $response->assertStatus(200)
            ->assertJsonFragment(['city' => 'Frissítettváros']);

        $this->assertDatabaseHas('addresses', $updated);
    }

    #[Test]
    public function test_authenticated_user_can_delete_address(): void
    {
        Sanctum::actingAs(User::factory()->create(), ['*']);

        $address = Address::factory()->create();

        $response = $this->deleteJson("/api/addresses/{$address->id}");

        $response->assertStatus(200);
        $this->assertDatabaseMissing('addresses', ['id' => $address->id]);
    }

    #[Test]
    public function test_guest_cannot_access_address_endpoints(): void
    {
        $response = $this->getJson('/api/addresses');
        $response->assertStatus(401);

        $response = $this->postJson('/api/addresses', []);
        $response->assertStatus(401);

        $response = $this->putJson('/api/addresses/1', []);
        $response->assertStatus(401);

        $response = $this->deleteJson('/api/addresses/1');
        $response->assertStatus(401);
    }
}
