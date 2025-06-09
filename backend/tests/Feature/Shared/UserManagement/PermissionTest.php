<?php

namespace Tests\Feature\Shared\UserManagement;

use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;
use PHPUnit\Framework\Attributes\Test;

class PermissionTest extends TestCase
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
    public function test_user_can_get_all_permissions()
    {
        // Jogosultságok létrehozása
        $permissions = Permission::factory()->count(3)->create();

        // API hívás
        $response = $this->getJson('/api/permissions');

        // Ellenőrzés
        $response->assertStatus(200);
        $this->assertCount(3, $response->json());
    }

    #[Test]
    public function test_user_can_create_permission()
    {
        // Adatok előkészítése
        $data = [
            'key' => 'test.permission',
            'module' => 'test-module',
            'description' => 'This is a test permission',
        ];

        // API hívás
        $response = $this->postJson('/api/permissions', $data);

        // Ellenőrzés
        $response->assertStatus(201)
            ->assertJsonPath('message', 'Az új engedély sikeresen létrehozva.')
            ->assertJsonPath('permission.key', 'test.permission')
            ->assertJsonPath('permission.module', 'test-module');

        // Ellenőrizzük, hogy az adatbázisban is megvan-e
        $this->assertDatabaseHas('permissions', [
            'key' => 'test.permission',
            'module' => 'test-module',
        ]);
    }

    #[Test]
    public function test_user_can_show_permission()
    {
        // Jogosultság létrehozása
        $permission = Permission::factory()->create();

        // API hívás
        $response = $this->getJson('/api/permissions/' . $permission->id);

        // Ellenőrzés
        $response->assertStatus(200)
            ->assertJsonPath('id', $permission->id)
            ->assertJsonPath('key', $permission->key)
            ->assertJsonPath('module', $permission->module);
    }

    #[Test]
    public function test_user_can_update_permission()
    {
        // Jogosultság létrehozása
        $permission = Permission::factory()->create();

        // Frissítési adatok
        $data = [
            'module' => 'updated-module',
            'description' => 'Updated permission description',
        ];

        // API hívás
        $response = $this->putJson('/api/permissions/' . $permission->id, $data);

        // Ellenőrzés
        $response->assertStatus(200)
            ->assertJsonPath('message', 'A jogosultság adatai sikeresen frissítve lettek.')
            ->assertJsonPath('permission.module', 'updated-module')
            ->assertJsonPath('permission.description', 'Updated permission description');

        // Ellenőrizzük, hogy az adatbázisban is frissült-e
        $this->assertDatabaseHas('permissions', [
            'id' => $permission->id,
            'module' => 'updated-module',
            'description' => 'Updated permission description',
        ]);
    }

    #[Test]
    public function test_user_can_delete_permission()
    {
        // Jogosultság létrehozása (amelyhez nem tartozik szerepkör)
        $permission = Permission::factory()->create();

        // API hívás
        $response = $this->deleteJson('/api/permissions/' . $permission->id);

        // Ellenőrzés
        $response->assertStatus(200)
            ->assertJsonPath('message', "{$permission->key} jogosultság sikeresen törölve.");

        // Ellenőrizzük, hogy az adatbázisból valóban törlődött-e
        $this->assertDatabaseMissing('permissions', ['id' => $permission->id]);
    }

    #[Test]
    public function test_validate_permission_creation()
    {
        // Hiányos adatok
        $data = [
            'key' => '',
            'module' => '',
        ];

        // API hívás
        $response = $this->postJson('/api/permissions', $data);

        // Ellenőrzés
        $response->assertStatus(422)
            ->assertJsonValidationErrors(['key', 'module']);
    }

    #[Test]
    public function test_not_found_returns_proper_message()
    {
        // Nem létező azonosító
        $response = $this->getJson('/api/permissions/999');

        // Ellenőrzés
        $response->assertStatus(404)
            ->assertJsonPath('message', 'A megadott azonosítójú (ID: 999) engedély nem található.');
    }

    #[Test]
    public function test_permission_with_roles_cannot_be_deleted()
    {
        // Jogosultság létrehozása
        $permission = Permission::factory()->create();

        // Szerepkör létrehozása
        $role = Role::factory()->create();

        // Jogosultság hozzárendelése a szerepkörhöz
        $role->permissions()->attach($permission->id, ['is_active' => true]);

        // API hívás
        $response = $this->deleteJson('/api/permissions/' . $permission->id);

        // Ellenőrzés - mivel van szerepkör a jogosultsághoz, nem törölhető
        $response->assertStatus(403)
            ->assertJsonPath('message', 'Ez a jogosultság szerepkörökhöz van rendelve, ezért nem törölhető.');
    }

    #[Test]
    public function test_unique_key_validation()
    {
        // Jogosultság létrehozása
        $permission = Permission::factory()->create([
            'key' => 'existing.permission'
        ]);

        // Adatok előkészítése
        $data = [
            'key' => 'existing.permission',  // Már létező kulcs
            'module' => 'test-module',
            'description' => 'This is a test permission',
        ];

        // API hívás
        $response = $this->postJson('/api/permissions', $data);

        // Ellenőrzés
        $response->assertStatus(422)
            ->assertJsonValidationErrors(['key']);
    }
}
