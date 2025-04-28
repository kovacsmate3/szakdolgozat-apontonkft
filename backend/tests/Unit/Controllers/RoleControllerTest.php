<?php

namespace Tests\Unit\Controllers;

use App\Http\Controllers\Api\Shared\UserManagement\RoleController;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class RoleControllerTest extends TestCase
{
    use RefreshDatabase;

    protected $roleController;

    public function setUp(): void
    {
        parent::setUp();
        if (\DB::transactionLevel() > 0) {
            \DB::rollBack();
        }
        $this->roleController = new RoleController();
    }

    #[Test]
    public function index_returns_all_roles()
    {
        // Hozzunk létre néhány szerepkört
        Role::factory()->count(3)->create();

        // Hívjuk meg az index metódust
        $response = $this->roleController->index();

        // Ellenőrizzük a választ
        $this->assertEquals(Response::HTTP_OK, $response->getStatusCode());

        // Ellenőrizzük a válasz adatokat
        $responseData = json_decode($response->getContent(), true);
        $this->assertCount(3, $responseData);
    }

    #[Test]
    public function store_creates_new_role_successfully()
    {
        // Létrehozunk egy kérést érvényes adatokkal
        $roleData = [
            'slug' => 'new-role',
            'title' => 'New Role',
            'description' => 'A brand new role'
        ];

        $request = new Request($roleData);
        $request->setMethod('POST');

        // Meghívjuk a store metódust
        $response = $this->roleController->store($request);

        // Ellenőrizzük a választ
        $this->assertEquals(Response::HTTP_CREATED, $response->getStatusCode());

        // Ellenőrizzük a választ tartalmazó üzenetet
        $responseData = json_decode($response->getContent(), true);
        $this->assertEquals('A szerepkör sikeresen létrehozva.', $responseData['message']);

        // Ellenőrizzük, hogy az adatbázisba mentődött-e
        $this->assertDatabaseHas('roles', [
            'slug' => 'new-role',
            'title' => 'New Role',
            'description' => 'A brand new role'
        ]);
    }

    #[Test]
    public function store_rejects_duplicate_slug()
    {
        // Először hozzunk létre egy szerepkört
        Role::factory()->create([
            'slug' => 'existing-role'
        ]);

        // Próbáljuk meg létrehozni ugyanazzal a slug-gal
        $roleData = [
            'slug' => 'existing-role',
            'title' => 'Duplicate Role',
            'description' => 'A duplicate role'
        ];

        $request = new Request($roleData);
        $request->setMethod('POST');

        // Elvárt kivétel
        $this->expectException(\Illuminate\Validation\ValidationException::class);

        // Meghívjuk a store metódust
        $this->roleController->store($request);
    }

    #[Test]
    public function show_returns_specific_role()
    {
        // Hozzunk létre egy szerepkört
        $role = Role::factory()->create([
            'slug' => 'test-role',
            'title' => 'Test Role',
            'description' => 'A test role description'
        ]);

        // Hívjuk meg a show metódust
        $response = $this->roleController->show(new Request(), $role->id);

        // Ellenőrizzük a választ
        $this->assertEquals(Response::HTTP_OK, $response->getStatusCode());

        // Ellenőrizzük a válasz adatokat
        $responseData = json_decode($response->getContent(), true);
        $this->assertEquals($role->id, $responseData['id']);
        $this->assertEquals('test-role', $responseData['slug']);
        $this->assertEquals('Test Role', $responseData['title']);
    }

    #[Test]
    public function show_returns_not_found_for_invalid_role()
    {
        // Hívjuk meg a show metódust egy nem létező azonosítóval
        $response = $this->roleController->show(new Request(), 9999);

        // Ellenőrizzük a választ
        $this->assertEquals(Response::HTTP_NOT_FOUND, $response->getStatusCode());

        // Ellenőrizzük a hibaüzenetet
        $responseData = json_decode($response->getContent(), true);
        $this->assertEquals('A megadott azonosítójú (ID: 9999) szerepkör nem található.', $responseData['message']);
    }

    #[Test]
    public function update_modifies_role_successfully()
    {
        // Hozzunk létre egy szerepkört
        $role = Role::factory()->create([
            'slug' => 'old-role',
            'title' => 'Old Role',
            'description' => 'An old role description'
        ]);

        // Módosító adatok
        $updateData = [
            'title' => 'Updated Role',
            'description' => 'An updated role description'
        ];

        $request = new Request($updateData);
        $request->setMethod('PUT');

        // Meghívjuk az update metódust
        $response = $this->roleController->update($request, $role->id);

        // Ellenőrizzük a választ
        $this->assertEquals(Response::HTTP_OK, $response->getStatusCode());

        // Ellenőrizzük a válasz üzenetet
        $responseData = json_decode($response->getContent(), true);
        $this->assertEquals('A szerepkör adatai sikeresen frissítve lettek.', $responseData['message']);

        // Frissítsük a role adatokat
        $updatedRole = Role::find($role->id);

        // Ellenőrizzük a módosításokat
        $this->assertEquals('Updated Role', $updatedRole->title);
        $this->assertEquals('An updated role description', $updatedRole->description);
        $this->assertEquals('old-role', $updatedRole->slug); // A slug nem változhat
    }

    #[Test]
    public function destroy_deletes_role_successfully()
    {
        // Hozzunk létre egy szerepkört
        $role = Role::factory()->create([
            'slug' => 'deletable-role'
        ]);

        // Meghívjuk a destroy metódust
        $response = $this->roleController->destroy($role->id);

        // Ellenőrizzük a választ
        $this->assertEquals(Response::HTTP_OK, $response->getStatusCode());

        // Ellenőrizzük a válasz üzenetet
        $responseData = json_decode($response->getContent(), true);
        $this->assertEquals('deletable-role szerepkör sikeresen törölve.', $responseData['message']);

        // Ellenőrizzük, hogy a szerepkör törlésre került
        $this->assertDatabaseMissing('roles', ['id' => $role->id]);
    }

    #[Test]
    public function destroy_prevents_deleting_role_with_users()
    {
        // Hozzunk létre egy szerepkört
        $role = Role::factory()->create();

        // Hozzunk létre egy felhasználót ehhez a szerepkörhöz
        User::factory()->create([
            'role_id' => $role->id
        ]);

        // Meghívjuk a destroy metódust
        $response = $this->roleController->destroy($role->id);

        // Ellenőrizzük a választ
        $this->assertEquals(Response::HTTP_FORBIDDEN, $response->getStatusCode());

        // Ellenőrizzük a válasz üzenetet
        $responseData = json_decode($response->getContent(), true);
        $this->assertEquals('Ez a szerepkör felhasználókhoz van rendelve, ezért nem törölhető.', $responseData['message']);

        // Ellenőrizzük, hogy a szerepkör nem lett törölve
        $this->assertDatabaseHas('roles', ['id' => $role->id]);
    }

    public function tearDown(): void
    {
        if (\DB::transactionLevel() > 0) {
            \DB::rollBack();
        }
        parent::tearDown();
    }
}
