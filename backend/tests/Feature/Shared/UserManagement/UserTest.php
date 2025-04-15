<?php

namespace Tests\Feature\Shared\UserManagement;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use App\Models\Role;
use PHPUnit\Framework\Attributes\Test;

class UserTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function test_admin_can_view_all_users()
    {
        $adminRole = Role::factory()->create(['slug' => 'admin']);
        $admin = User::factory()->create(['role_id' => $adminRole->id]);

        $users = User::factory()->count(3)->create();

        $response = $this->actingAs($admin)
            ->getJson('/api/users');

        $response->assertStatus(200)
            ->assertJsonCount(4);
    }

    #[Test]
    public function test_non_admin_cannot_view_all_users()
    {
        $userRole = Role::factory()->create(['slug' => 'employee']);
        $user = User::factory()->create(['role_id' => $userRole->id]);

        $response = $this->actingAs($user)
            ->getJson('/api/users');

        $response->assertStatus(403);
    }

    #[Test]
    public function test_admin_can_create_user()
    {
        $adminRole = Role::factory()->create(['slug' => 'admin']);
        $employeeRole = Role::factory()->create(['slug' => 'employee']);
        $admin = User::factory()->create(['role_id' => $adminRole->id]);

        $userData = [
            'username' => 'newuser',
            'firstname' => 'New',
            'lastname' => 'User',
            'birthdate' => '1990-01-01',
            'phonenumber' => '+36201234567',
            'email' => 'newuser@example.com',
            'password' => 'Password1!',
            'role_id' => $employeeRole->id,
        ];

        $response = $this->actingAs($admin)
            ->postJson('/api/users', $userData);

        $response->assertStatus(201)
            ->assertJson([
                'message' => 'A felhasználó sikeresen létrehozva.'
            ]);

        $this->assertDatabaseHas('users', [
            'email' => 'newuser@example.com',
            'username' => 'newuser'
        ]);
    }

    #[Test]
    public function test_admin_can_view_user()
    {
        $adminRole = Role::factory()->create(['slug' => 'admin']);
        $admin = User::factory()->create(['role_id' => $adminRole->id]);

        $user = User::factory()->create();

        $response = $this->actingAs($admin)
            ->getJson("/api/users/{$user->id}");

        $response->assertStatus(200)
            ->assertJsonFragment([
                'id' => $user->id,
                'username' => $user->username
            ]);
    }

    #[Test]
    public function test_admin_can_update_user()
    {
        $adminRole = Role::factory()->create(['slug' => 'admin']);
        $admin = User::factory()->create(['role_id' => $adminRole->id]);

        $user = User::factory()->create();

        $updateData = [
            'firstname' => 'Updated',
            'lastname' => 'Name'
        ];

        $response = $this->actingAs($admin)
            ->putJson("/api/users/{$user->id}", $updateData);

        $response->assertStatus(200)
            ->assertJson([
                'message' => 'A felhasználó adatai sikeresen frissítve lettek.'
            ]);

        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'firstname' => 'Updated',
            'lastname' => 'Name'
        ]);
    }

    #[Test]
    public function test_admin_can_delete_user()
    {
        $adminRole = Role::factory()->create(['slug' => 'admin']);
        $admin = User::factory()->create(['role_id' => $adminRole->id]);

        $user = User::factory()->create();

        $response = $this->actingAs($admin)
            ->deleteJson("/api/users/{$user->id}");

        $response->assertStatus(200)
            ->assertJson([
                'message' => $user->firstname . ' ' . $user->lastname . ' felhasználó sikeresen törölve.'
            ]);

        $this->assertDatabaseMissing('users', [
            'id' => $user->id
        ]);
    }

    #[Test]
    public function test_admin_cannot_delete_last_admin_user()
    {
        $adminRole = Role::factory()->create(['slug' => 'admin']);
        $admin = User::factory()->create(['role_id' => $adminRole->id]);

        $response = $this->actingAs($admin)
            ->deleteJson("/api/users/{$admin->id}");

        $response->assertStatus(403)
            ->assertJson([
                'message' => 'Az utolsó admin felhasználót nem lehet törölni.'
            ]);

        $this->assertDatabaseHas('users', [
            'id' => $admin->id
        ]);
    }
}
