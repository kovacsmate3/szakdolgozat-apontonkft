<?php

namespace Tests\Feature\Shared;

use App\Models\Law;
use App\Models\LawCategory;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Laravel\Sanctum\Sanctum;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class LawTest extends TestCase
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
    public function test_user_can_get_all_laws()
    {
        // Jogszabályok létrehozása
        $laws = Law::factory()->count(3)->create();

        // API hívás
        $response = $this->getJson('/api/laws');

        // Ellenőrzés
        $response->assertStatus(200);
        $this->assertCount(3, $response->json());
    }

    #[Test]
    public function test_user_can_filter_laws_by_category()
    {
        // Kategória létrehozása
        $category = LawCategory::factory()->create();

        // Jogszabályok létrehozása különböző kategóriákkal
        Law::factory()->create(['category_id' => $category->id]);
        Law::factory()->create(['category_id' => $category->id]);
        Law::factory()->create(); // Kategória nélkül

        // API hívás kategória szűréssel
        $response = $this->getJson('/api/laws?category_id=' . $category->id);

        // Ellenőrzés
        $response->assertStatus(200);
        $this->assertCount(2, $response->json());
    }

    #[Test]
    public function test_user_can_filter_laws_by_active_status()
    {
        // Jogszabályok létrehozása különböző státuszokkal
        Law::factory()->create(['is_active' => true]);
        Law::factory()->create(['is_active' => true]);
        Law::factory()->create(['is_active' => false]);

        // API hívás aktív státusz szűréssel
        $response = $this->getJson('/api/laws?is_active=1');

        // Ellenőrzés
        $response->assertStatus(200);
        $this->assertCount(2, $response->json());
    }

    #[Test]
    public function test_user_can_search_laws()
    {
        // Jogszabályok létrehozása
        Law::factory()->create(['title' => 'A földmérési és térképészeti tevékenységről']);
        Law::factory()->create(['title' => 'Az ingatlan-nyilvántartásról']);
        Law::factory()->create(['official_ref' => '2012. évi XLVI. törvény']);

        // API hívás keresési paraméterrel (címben keres)
        $response = $this->getJson('/api/laws?search=földmérési');

        // Ellenőrzés
        $response->assertStatus(200);
        $this->assertCount(1, $response->json());
        $this->assertEquals('A földmérési és térképészeti tevékenységről', $response->json()[0]['title']);

        // API hívás keresési paraméterrel (hivatalos referenciában keres)
        $response = $this->getJson('/api/laws?search=XLVI');

        // Ellenőrzés
        $response->assertStatus(200);
        $this->assertCount(1, $response->json());
        $this->assertEquals('2012. évi XLVI. törvény', $response->json()[0]['official_ref']);
    }

    #[Test]
    public function test_user_can_sort_laws()
    {
        // Jogszabályok létrehozása
        Law::factory()->create([
            'title' => 'Z törvény',
            'date_of_enactment' => '2023-01-01'
        ]);
        Law::factory()->create([
            'title' => 'A törvény',
            'date_of_enactment' => '2022-01-01'
        ]);
        Law::factory()->create([
            'title' => 'M törvény',
            'date_of_enactment' => '2021-01-01'
        ]);

        // API hívás rendezési paraméterekkel (cím szerint, növekvő)
        $response = $this->getJson('/api/laws?sort_by=title&sort_dir=asc');

        // Ellenőrzés
        $response->assertStatus(200);
        $laws = $response->json();
        $this->assertEquals('A törvény', $laws[0]['title']);
        $this->assertEquals('M törvény', $laws[1]['title']);
        $this->assertEquals('Z törvény', $laws[2]['title']);

        // API hívás rendezési paraméterekkel (hatálybalépés szerint, csökkenő)
        $response = $this->getJson('/api/laws?sort_by=date_of_enactment&sort_dir=desc');

        // Ellenőrzés
        $response->assertStatus(200);
        $laws = $response->json();
        $this->assertEquals('Z törvény', $laws[0]['title']);
        $this->assertEquals('A törvény', $laws[1]['title']);
        $this->assertEquals('M törvény', $laws[2]['title']);
    }

    #[Test]
    public function test_user_can_paginate_laws()
    {
        // 10 jogszabály létrehozása
        Law::factory()->count(10)->create();

        // API hívás lapozási paraméterekkel
        $response = $this->getJson('/api/laws?per_page=5');

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
    public function test_user_can_create_law()
    {
        // Kategória létrehozása
        $category = LawCategory::factory()->create();

        // Adatok előkészítése
        $data = [
            'category_id' => $category->id,
            'title' => 'Teszt jogszabály',
            'official_ref' => '2025. évi XTESZT. tv',
            'date_of_enactment' => '2025-01-01',
            'is_active' => true,
            'link' => 'https://example.com/law',
        ];

        // API hívás
        $response = $this->postJson('/api/laws', $data);

        // Ellenőrzés - a dátum formátumát ne ellenőrizzük, mert az időzónák miatt eltérhet
        $response->assertStatus(201)
            ->assertJsonPath('message', 'A jogszabály sikeresen létrehozva.')
            ->assertJsonPath('law.category_id', $category->id)
            ->assertJsonPath('law.title', 'Teszt jogszabály')
            ->assertJsonPath('law.official_ref', '2025. évi XTESZT. tv')
            ->assertJsonPath('law.is_active', true)
            ->assertJsonPath('law.link', 'https://example.com/law');

        // Ellenőrizzük, hogy az adatbázisban is megvan-e
        $this->assertDatabaseHas('laws', [
            'category_id' => $category->id,
            'title' => 'Teszt jogszabály',
            'official_ref' => '2025. évi XTESZT. tv',
            'is_active' => 1,
            'link' => 'https://example.com/law',
        ]);
    }

    #[Test]
    public function test_user_can_show_law()
    {
        // Jogszabály létrehozása
        $law = Law::factory()->create();

        // API hívás
        $response = $this->getJson('/api/laws/' . $law->id);

        // Ellenőrzés
        $response->assertStatus(200)
            ->assertJsonPath('id', $law->id)
            ->assertJsonPath('title', $law->title)
            ->assertJsonPath('official_ref', $law->official_ref);
    }

    #[Test]
    public function test_user_can_show_law_with_category()
    {
        // Kategória létrehozása
        $category = LawCategory::factory()->create();

        // Jogszabály létrehozása kategóriával
        $law = Law::factory()->create(['category_id' => $category->id]);

        // API hívás include paraméterrel
        $response = $this->getJson('/api/laws/' . $law->id . '?include=category');

        // Ellenőrzés
        $response->assertStatus(200)
            ->assertJsonPath('id', $law->id)
            ->assertJsonPath('title', $law->title)
            ->assertJsonPath('category.id', $category->id)
            ->assertJsonPath('category.name', $category->name);
    }

    #[Test]
    public function test_user_can_update_law()
    {
        // Jogszabály létrehozása
        $law = Law::factory()->create();

        // Új kategória létrehozása
        $newCategory = LawCategory::factory()->create();

        // Frissítési adatok
        $data = [
            'category_id' => $newCategory->id,
            'title' => 'Frissített jogszabály',
            'is_active' => false,
        ];

        // API hívás
        $response = $this->putJson('/api/laws/' . $law->id, $data);

        // Ellenőrzés
        $response->assertStatus(200)
            ->assertJsonPath('message', 'A jogszabály adatai sikeresen frissítve lettek.')
            ->assertJsonPath('law.id', $law->id)
            ->assertJsonPath('law.category_id', $newCategory->id)
            ->assertJsonPath('law.title', 'Frissített jogszabály')
            ->assertJsonPath('law.is_active', false);

        // Ellenőrizzük, hogy az adatbázisban is frissült-e
        $this->assertDatabaseHas('laws', [
            'id' => $law->id,
            'category_id' => $newCategory->id,
            'title' => 'Frissített jogszabály',
            'is_active' => 0,
        ]);
    }

    #[Test]
    public function test_user_can_delete_law()
    {
        // Jogszabály létrehozása
        $law = Law::factory()->create();

        // API hívás
        $response = $this->deleteJson('/api/laws/' . $law->id);

        // Ellenőrzés
        $response->assertStatus(200)
            ->assertJsonPath('message', "{$law->official_ref} ({$law->title}) jogszabály sikeresen törölve.");

        // Ellenőrizzük, hogy az adatbázisból valóban törlődött-e
        $this->assertDatabaseMissing('laws', ['id' => $law->id]);
    }

    #[Test]
    public function test_validate_law_creation()
    {
        // Hiányos adatok
        $data = [
            'title' => '',
            'official_ref' => '',
            'date_of_enactment' => 'invalid-date',
            'is_active' => 'not-a-boolean',
            'link' => 'not-a-url',
        ];

        // API hívás
        $response = $this->postJson('/api/laws', $data);

        // Ellenőrzés
        $response->assertStatus(422)
            ->assertJsonValidationErrors(['title', 'official_ref', 'date_of_enactment', 'is_active', 'link']);
    }

    #[Test]
    public function test_user_cannot_create_law_with_nonexistent_category()
    {
        // Adatok előkészítése nem létező kategóriával
        $data = [
            'category_id' => 999,
            'title' => 'Teszt jogszabály',
            'official_ref' => '2025. évi XTESZT. tv',
            'date_of_enactment' => '2025-01-01',
            'is_active' => true,
        ];

        // API hívás
        $response = $this->postJson('/api/laws', $data);

        // Ellenőrzés
        $response->assertStatus(422)
            ->assertJsonValidationErrors(['category_id']);
    }

    #[Test]
    public function test_user_cannot_create_duplicate_title_and_ref()
    {
        // Létező jogszabály
        $law = Law::factory()->create([
            'title' => 'Teszt törvény',
            'official_ref' => '2025. évi TESZT. tv'
        ]);

        // Ugyanazzal a címmel próbálunk létrehozni egy másikat
        $data = [
            'title' => 'Teszt törvény',
            'official_ref' => '2026. évi MASIK. tv',
        ];

        // API hívás
        $response = $this->postJson('/api/laws', $data);

        // Ellenőrzés
        $response->assertStatus(422)
            ->assertJsonValidationErrors(['title']);

        // Ugyanazzal a hivatalos hivatkozással próbálunk létrehozni egy másikat
        $data = [
            'title' => 'Másik teszt törvény',
            'official_ref' => '2025. évi TESZT. tv',
        ];

        // API hívás
        $response = $this->postJson('/api/laws', $data);

        // Ellenőrzés
        $response->assertStatus(422)
            ->assertJsonValidationErrors(['official_ref']);
    }

    #[Test]
    public function test_law_title_and_ref_cannot_exceed_255_characters()
    {
        // Túl hosszú cím és hivatkozás
        $data = [
            'title' => str_repeat('a', 256),
            'official_ref' => str_repeat('b', 256),
        ];

        // API hívás
        $response = $this->postJson('/api/laws', $data);

        // Ellenőrzés
        $response->assertStatus(422)
            ->assertJsonValidationErrors(['title', 'official_ref']);
    }

    #[Test]
    public function test_not_found_returns_proper_message()
    {
        // Nem létező azonosító
        $response = $this->getJson('/api/laws/999');

        // Ellenőrzés
        $response->assertStatus(404)
            ->assertJsonPath('message', 'A megadott azonosítójú (ID: 999) jogszabály nem található.');
    }
}
