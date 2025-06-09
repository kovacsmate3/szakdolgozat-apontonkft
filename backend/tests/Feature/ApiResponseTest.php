<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class ApiResponseTest extends TestCase
{
    #[Test]
    public function test_api_root_returns_welcome_message()
    {
        $response = $this->getJson('/api');

        $response->assertStatus(200)
            ->assertJsonPath('message', 'Üdvözöljük az A-Ponton Kft. API felületén!')
            ->assertJsonStructure([
                'message',
                'company',
                'services',
                'status',
                'version',
                'date'
            ]);
    }

    #[Test]
    public function test_unauthenticated_access_to_protected_route_returns_401()
    {
        $response = $this->getJson('/api/profile');

        $response->assertStatus(401);
    }

    #[Test]
    public function test_invalid_route_returns_404()
    {
        $response = $this->getJson('/api/nonexistent-route');

        $response->assertStatus(404);
    }
}
