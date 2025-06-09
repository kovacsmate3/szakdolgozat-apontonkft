<?php

namespace Tests\Unit\Models;

use App\Models\Role;
use App\Models\User;
use App\Models\Permission;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class RoleModelTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function it_can_create_a_role()
    {
        $role = Role::factory()->create([
            'slug' => 'test-role',
            'title' => 'Test Role',
            'description' => 'A test role for unit testing'
        ]);

        $this->assertDatabaseHas('roles', [
            'slug' => 'test-role',
            'title' => 'Test Role',
            'description' => 'A test role for unit testing'
        ]);
    }

    #[Test]
    public function it_has_many_users()
    {
        $role = Role::factory()->create();

        // Hozzunk létre néhány felhasználót ehhez a szerepkörhöz
        $users = User::factory()->count(3)->create([
            'role_id' => $role->id
        ]);

        $this->assertCount(3, $role->users);
        $this->assertTrue($role->users->contains($users->first()));
    }

    #[Test]
    public function it_has_many_permissions()
    {
        $role = Role::factory()->create();

        // Hozzunk létre néhány engedélyt és kapcsoljuk a szerepkörhöz
        $permissions = Permission::factory()->count(2)->create();
        $role->permissions()->attach($permissions, [
            'is_active' => true,
            'created_at' => now(),
            'updated_at' => now()
        ]);

        $this->assertCount(2, $role->permissions);
    }

    #[Test]
    public function it_cannot_create_role_with_duplicate_slug()
    {
        // Először hozzunk létre egy szerepkört
        Role::factory()->create([
            'slug' => 'duplicate-role'
        ]);

        // Próbáljuk meg létrehozni ugyanazzal a slug-gal
        $this->expectException(\Illuminate\Database\QueryException::class);

        Role::factory()->create([
            'slug' => 'duplicate-role'
        ]);
    }

    #[Test]
    public function it_fills_all_attributes()
    {
        $roleData = [
            'slug' => 'test-fill',
            'title' => 'Test Fill Role',
            'description' => 'A role to test filling attributes'
        ];

        $role = Role::create($roleData);

        $this->assertEquals('test-fill', $role->slug);
        $this->assertEquals('Test Fill Role', $role->title);
        $this->assertEquals('A role to test filling attributes', $role->description);
    }
}
