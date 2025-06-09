<?php

namespace Tests\Feature\Shared\UserManagement;

use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;
use PHPUnit\Framework\Attributes\Test;

class RoleTest extends TestCase
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
    public function test_user_can_get_all_roles()
    {
        // Szerepkörök létrehozása
        $roles = Role::factory()->count(3)->create();

        // API hívás
        $response = $this->getJson('/api/roles');

        // Ellenőrzés
        $response->assertStatus(200);
        $this->assertCount(3, $response->json());
    }

    #[Test]
    public function test_user_can_create_role()
    {
        // Adatok előkészítése
        $data = [
            'slug' => 'test-role',
            'title' => 'Test Role',
            'description' => 'This is a test role',
        ];

        // API hívás
        $response = $this->postJson('/api/roles', $data);

        // Ellenőrzés
        $response->assertStatus(201)
            ->assertJsonPath('message', 'A szerepkör sikeresen létrehozva.')
            ->assertJsonPath('role.slug', 'test-role')
            ->assertJsonPath('role.title', 'Test Role');

        // Ellenőrizzük, hogy az adatbázisban is megvan-e
        $this->assertDatabaseHas('roles', [
            'slug' => 'test-role',
            'title' => 'Test Role',
        ]);
    }

    #[Test]
    public function test_user_can_show_role()
    {
        // Szerepkör létrehozása
        $role = Role::factory()->create();

        // API hívás
        $response = $this->getJson('/api/roles/' . $role->id);

        // Ellenőrzés
        $response->assertStatus(200)
            ->assertJsonPath('id', $role->id)
            ->assertJsonPath('slug', $role->slug)
            ->assertJsonPath('title', $role->title);
    }

    #[Test]
    public function test_user_can_update_role()
    {
        // Szerepkör létrehozása
        $role = Role::factory()->create();

        // Frissítési adatok
        $data = [
            'title' => 'Updated Role Title',
            'description' => 'Updated role description',
        ];

        // API hívás
        $response = $this->putJson('/api/roles/' . $role->id, $data);

        // Ellenőrzés
        $response->assertStatus(200)
            ->assertJsonPath('message', 'A szerepkör adatai sikeresen frissítve lettek.')
            ->assertJsonPath('role.title', 'Updated Role Title')
            ->assertJsonPath('role.description', 'Updated role description');

        // Ellenőrizzük, hogy az adatbázisban is frissült-e
        $this->assertDatabaseHas('roles', [
            'id' => $role->id,
            'title' => 'Updated Role Title',
            'description' => 'Updated role description',
        ]);
    }

    #[Test]
    public function test_user_can_delete_role()
    {
        // Szerepkör létrehozása (amelyhez nem tartoznak felhasználók)
        $role = Role::factory()->create();

        // API hívás
        $response = $this->deleteJson('/api/roles/' . $role->id);

        // Ellenőrzés
        $response->assertStatus(200)
            ->assertJsonPath('message', "{$role->slug} szerepkör sikeresen törölve.");

        // Ellenőrizzük, hogy az adatbázisból valóban törlődött-e
        $this->assertDatabaseMissing('roles', ['id' => $role->id]);
    }

    #[Test]
    public function test_validate_role_creation()
    {
        // Hiányos adatok
        $data = [
            'slug' => '',
            'title' => '',
        ];

        // API hívás
        $response = $this->postJson('/api/roles', $data);

        // Ellenőrzés
        $response->assertStatus(422)
            ->assertJsonValidationErrors(['slug', 'title']);
    }

    #[Test]
    public function test_not_found_returns_proper_message()
    {
        // Nem létező azonosító
        $response = $this->getJson('/api/roles/999');

        // Ellenőrzés
        $response->assertStatus(404)
            ->assertJsonPath('message', 'A megadott azonosítójú (ID: 999) szerepkör nem található.');
    }

    #[Test]
    public function test_role_with_users_cannot_be_deleted()
    {
        // Szerepkör létrehozása
        $role = Role::factory()->create();

        // Felhasználó rendelése a szerepkörhöz
        $user = User::factory()->create(['role_id' => $role->id]);

        // API hívás
        $response = $this->deleteJson('/api/roles/' . $role->id);

        // Ellenőrzés - mivel van felhasználó a szerepkörhöz, nem törölhető
        $response->assertStatus(403)
            ->assertJsonPath('message', 'Ez a szerepkör felhasználókhoz van rendelve, ezért nem törölhető.');
    }
}
