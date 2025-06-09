<?php

namespace Tests\Feature\Shared;

use App\Models\Law;
use App\Models\LawCategory;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;
use PHPUnit\Framework\Attributes\Test;

class LawCategoryTest extends TestCase
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
    public function test_user_can_get_all_law_categories()
    {
        // Kategóriák létrehozása
        $categories = LawCategory::factory()->count(3)->create();

        // API hívás
        $response = $this->getJson('/api/law-categories');

        // Ellenőrzés
        $response->assertStatus(200);
        $this->assertCount(3, $response->json());
    }

    #[Test]
    public function test_user_can_search_law_categories()
    {
        // Kategóriák létrehozása
        LawCategory::factory()->create(['name' => 'Építési jog']);
        LawCategory::factory()->create(['name' => 'Földmérési jog']);
        LawCategory::factory()->create(['name' => 'Ingatlan jog']);

        // API hívás keresési paraméterrel
        $response = $this->getJson('/api/law-categories?search=Földmérési');

        // Ellenőrzés
        $response->assertStatus(200);
        $this->assertCount(1, $response->json());
        $this->assertEquals('Földmérési jog', $response->json()[0]['name']);
    }

    #[Test]
    public function test_user_can_sort_law_categories()
    {
        // Kategóriák létrehozása
        LawCategory::factory()->create(['name' => 'Z kategória']);
        LawCategory::factory()->create(['name' => 'A kategória']);
        LawCategory::factory()->create(['name' => 'M kategória']);

        // API hívás rendezési paraméterekkel (descending)
        $response = $this->getJson('/api/law-categories?sort_by=name&sort_dir=desc');

        // Ellenőrzés
        $response->assertStatus(200);
        $categories = $response->json();
        $this->assertEquals('Z kategória', $categories[0]['name']);
        $this->assertEquals('M kategória', $categories[1]['name']);
        $this->assertEquals('A kategória', $categories[2]['name']);
    }

    #[Test]
    public function test_user_can_paginate_law_categories()
    {
        // 10 kategória létrehozása
        LawCategory::factory()->count(10)->create();

        // API hívás lapozási paraméterekkel
        $response = $this->getJson('/api/law-categories?per_page=5');

        // Ellenőrzés
        $response->assertStatus(200)
            ->assertJsonStructure([
                'current_page',
                'data',
                'first_page_url',
                'from',
                'last_page',
                'last_page_url',
                'links',
                'next_page_url',
                'path',
                'per_page',
                'prev_page_url',
                'to',
                'total'
            ]);

        // Ellenőrizzük, hogy 5 elem van az oldalon
        $this->assertCount(5, $response->json('data'));
        // Ellenőrizzük, hogy a teljes elemszám 10
        $this->assertEquals(10, $response->json('total'));
    }

    #[Test]
    public function test_user_can_create_law_category()
    {
        // Adatok előkészítése
        $data = [
            'name' => 'Teszt kategória',
            'description' => 'Teszt leírás'
        ];

        // API hívás
        $response = $this->postJson('/api/law-categories', $data);

        // Ellenőrzés
        $response->assertStatus(201)
            ->assertJsonPath('message', 'A jogszabály kategória sikeresen létrehozva.')
            ->assertJsonPath('category.name', 'Teszt kategória')
            ->assertJsonPath('category.description', 'Teszt leírás');

        // Ellenőrizzük, hogy az adatbázisban is megvan-e
        $this->assertDatabaseHas('law_categories', [
            'name' => 'Teszt kategória',
            'description' => 'Teszt leírás'
        ]);
    }

    #[Test]
    public function test_user_can_show_law_category()
    {
        // Kategória létrehozása
        $category = LawCategory::factory()->create();

        // API hívás
        $response = $this->getJson('/api/law-categories/' . $category->id);

        // Ellenőrzés
        $response->assertStatus(200)
            ->assertJsonPath('id', $category->id)
            ->assertJsonPath('name', $category->name)
            ->assertJsonPath('description', $category->description);
    }

    #[Test]
    public function test_user_can_show_law_category_with_laws()
    {
        // Kategória létrehozása
        $category = LawCategory::factory()->create();

        // Jogszabályok létrehozása a kategóriához
        $laws = Law::factory()->count(3)->create(['category_id' => $category->id]);

        // API hívás include paraméterrel
        $response = $this->getJson('/api/law-categories/' . $category->id . '?include=laws');

        // Ellenőrzés
        $response->assertStatus(200)
            ->assertJsonPath('id', $category->id)
            ->assertJsonPath('name', $category->name);
        $this->assertCount(3, $response->json('laws'));
    }

    #[Test]
    public function test_user_can_update_law_category()
    {
        // Kategória létrehozása
        $category = LawCategory::factory()->create();

        // Frissítési adatok
        $data = [
            'name' => 'Frissített kategória',
            'description' => 'Frissített leírás'
        ];

        // API hívás
        $response = $this->putJson('/api/law-categories/' . $category->id, $data);

        // Ellenőrzés
        $response->assertStatus(200)
            ->assertJsonPath('message', 'A jogszabály kategória adatai sikeresen frissítve lettek.')
            ->assertJsonPath('category.id', $category->id)
            ->assertJsonPath('category.name', 'Frissített kategória')
            ->assertJsonPath('category.description', 'Frissített leírás');

        // Ellenőrizzük, hogy az adatbázisban is frissült-e
        $this->assertDatabaseHas('law_categories', [
            'id' => $category->id,
            'name' => 'Frissített kategória',
            'description' => 'Frissített leírás'
        ]);
    }

    #[Test]
    public function test_user_can_delete_law_category()
    {
        // Kategória létrehozása
        $category = LawCategory::factory()->create();

        // API hívás
        $response = $this->deleteJson('/api/law-categories/' . $category->id);

        // Ellenőrzés
        $response->assertStatus(200)
            ->assertJsonPath('message', "{$category->name} jogszabály kategória sikeresen törölve.");

        // Ellenőrizzük, hogy az adatbázisból valóban törlődött-e
        $this->assertDatabaseMissing('law_categories', ['id' => $category->id]);
    }

    #[Test]
    public function test_user_cannot_delete_category_with_laws()
    {
        // Kategória létrehozása
        $category = LawCategory::factory()->create();

        // Jogszabály létrehozása a kategóriához
        $law = Law::factory()->create(['category_id' => $category->id]);

        // API hívás
        $response = $this->deleteJson('/api/law-categories/' . $category->id);

        // Ellenőrzés
        $response->assertStatus(403)
            ->assertJsonPath('message', 'Ez a kategória jogszabályokhoz van rendelve, ezért nem törölhető.');

        // Ellenőrizzük, hogy az adatbázisban még mindig megvan-e
        $this->assertDatabaseHas('law_categories', ['id' => $category->id]);
    }

    #[Test]
    public function test_validate_law_category_creation()
    {
        // Hiányos adatok
        $data = [
            'name' => '',
            'description' => []
        ];

        // API hívás
        $response = $this->postJson('/api/law-categories', $data);

        // Ellenőrzés
        $response->assertStatus(422)
            ->assertJsonValidationErrors(['name', 'description']);
    }

    #[Test]
    public function test_user_cannot_create_duplicate_name()
    {
        // Létező kategória
        $category = LawCategory::factory()->create([
            'name' => 'Teszt kategória'
        ]);

        // Ugyanazzal a névvel próbálunk létrehozni egy másikat
        $data = [
            'name' => 'Teszt kategória',
            'description' => 'Másik leírás'
        ];

        // API hívás
        $response = $this->postJson('/api/law-categories', $data);

        // Ellenőrzés
        $response->assertStatus(422)
            ->assertJsonValidationErrors(['name']);
    }

    #[Test]
    public function test_law_category_name_cannot_exceed_100_characters()
    {
        // Túl hosszú név
        $data = [
            'name' => str_repeat('a', 101),
            'description' => 'Leírás'
        ];

        // API hívás
        $response = $this->postJson('/api/law-categories', $data);

        // Ellenőrzés
        $response->assertStatus(422)
            ->assertJsonValidationErrors(['name']);
    }

    #[Test]
    public function test_not_found_returns_proper_message()
    {
        // Nem létező azonosító
        $response = $this->getJson('/api/law-categories/999');

        // Ellenőrzés
        $response->assertStatus(404)
            ->assertJsonPath('message', 'A megadott azonosítójú (ID: 999) jogszabály kategória nem található.');
    }
}
