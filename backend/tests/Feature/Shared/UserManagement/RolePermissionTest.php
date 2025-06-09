<?php

namespace Tests\Feature\Shared\UserManagement;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use App\Models\Role;
use App\Models\Permission;
use PHPUnit\Framework\Attributes\Test;

class RolePermissionTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function test_admin_can_view_all_roles()
    {
        $adminRole = Role::factory()->create(['slug' => 'admin']);
        $admin = User::factory()->create(['role_id' => $adminRole->id]);

        Role::factory()->count(2)->create();

        $response = $this->actingAs($admin)
            ->getJson('/api/roles');

        $response->assertStatus(200)
            ->assertJsonCount(3);
    }

    #[Test]
    public function test_admin_can_create_role()
    {
        $adminRole = Role::factory()->create(['slug' => 'admin']);
        $admin = User::factory()->create(['role_id' => $adminRole->id]);

        $roleData = [
            'slug' => 'manager',
            'title' => 'Manager',
            'description' => 'Manager role with limited permissions',
        ];

        $response = $this->actingAs($admin)
            ->postJson('/api/roles', $roleData);

        $response->assertStatus(201)
            ->assertJson([
                'message' => 'A szerepkör sikeresen létrehozva.'
            ]);

        $this->assertDatabaseHas('roles', [
            'slug' => 'manager',
            'title' => 'Manager'
        ]);
    }

    #[Test]
    public function test_admin_can_update_role()
    {
        $adminRole = Role::factory()->create(['slug' => 'admin']);
        $admin = User::factory()->create(['role_id' => $adminRole->id]);

        $role = Role::factory()->create(['slug' => 'employee']);

        $updateData = [
            'title' => 'Updated Employee',
            'description' => 'Updated description'
        ];

        $response = $this->actingAs($admin)
            ->putJson("/api/roles/{$role->id}", $updateData);

        $response->assertStatus(200)
            ->assertJson([
                'message' => 'A szerepkör adatai sikeresen frissítve lettek.'
            ]);

        $this->assertDatabaseHas('roles', [
            'id' => $role->id,
            'title' => 'Updated Employee',
            'description' => 'Updated description'
        ]);
    }

    #[Test]
    public function test_admin_can_view_all_permissions()
    {
        $adminRole = Role::factory()->create(['slug' => 'admin']);
        $admin = User::factory()->create(['role_id' => $adminRole->id]);

        Permission::factory()->count(3)->create();

        $response = $this->actingAs($admin)
            ->getJson('/api/permissions');

        $response->assertStatus(200)
            ->assertJsonCount(3);
    }

    #[Test]
    public function test_admin_can_assign_permissions_to_role()
    {
        $adminRole = Role::factory()->create(['slug' => 'admin']);
        $admin = User::factory()->create(['role_id' => $adminRole->id]);

        $role = Role::factory()->create();
        $permissions = Permission::factory()->count(2)->create();

        $response = $this->actingAs($admin)
            ->postJson("/api/roles/{$role->id}/permissions", [
                'permissions' => $permissions->pluck('id')->toArray()
            ]);

        $response->assertStatus(200)
            ->assertJson([
                'message' => 'A(z) ' . $role->title . ' szerepkörhöz sikeresen hozzárendeltük a megadott jogosultságokat.'
            ]);

        foreach ($permissions as $permission) {
            $this->assertDatabaseHas('role_permission', [
                'role_id' => $role->id,
                'permission_id' => $permission->id,
                'is_active' => true
            ]);
        }
    }

    #[Test]
    public function test_admin_can_revoke_permission_from_role()
    {
        $adminRole = Role::factory()->create(['slug' => 'admin']);
        $admin = User::factory()->create(['role_id' => $adminRole->id]);

        $role = Role::factory()->create();
        $permission = Permission::factory()->create();

        $role->permissions()->attach($permission->id, ['is_active' => true]);

        $response = $this->actingAs($admin)
            ->deleteJson("/api/roles/{$role->id}/permissions/{$permission->id}");

        $response->assertStatus(200)
            ->assertJson([
                'message' => "{$permission->key} jogosultság sikeresen eltávolítva a {$role->title} szerepkörtől."
            ]);

        $this->assertDatabaseMissing('role_permission', [
            'role_id' => $role->id,
            'permission_id' => $permission->id
        ]);
    }

    #[Test]
    public function test_admin_can_sync_permissions_to_role()
    {
        $adminRole = Role::factory()->create(['slug' => 'admin']);
        $admin = User::factory()->create(['role_id' => $adminRole->id]);

        $role = Role::factory()->create();
        $oldPermission = Permission::factory()->create();
        $newPermissions = Permission::factory()->count(2)->create();

        $role->permissions()->attach($oldPermission->id, ['is_active' => true]);

        $response = $this->actingAs($admin)
            ->postJson("/api/roles/{$role->id}/permissions/sync", [
                'permissions' => $newPermissions->pluck('id')->toArray()
            ]);

        $response->assertStatus(200)
            ->assertJson([
                'message' => "Jogosultságok sikeresen szinkronizálva a(z) {$role->title} szerepkörhöz."
            ]);

        $this->assertDatabaseMissing('role_permission', [
            'role_id' => $role->id,
            'permission_id' => $oldPermission->id
        ]);

        foreach ($newPermissions as $permission) {
            $this->assertDatabaseHas('role_permission', [
                'role_id' => $role->id,
                'permission_id' => $permission->id
            ]);
        }
    }
}
