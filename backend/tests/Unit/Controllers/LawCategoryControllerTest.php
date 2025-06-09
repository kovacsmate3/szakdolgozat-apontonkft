<?php

namespace Tests\Unit\Controllers;

use App\Http\Controllers\Api\Shared\LawCategoryController;
use App\Models\Law;
use App\Models\LawCategory;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class LawCategoryControllerTest extends TestCase
{
    use RefreshDatabase;

    protected $lawCategoryController;

    public function setUp(): void
    {
        parent::setUp();
        if (\DB::transactionLevel() > 0) {
            \DB::rollBack();
        }
        $this->lawCategoryController = new LawCategoryController();
    }

    #[Test]
    public function index_returns_all_law_categories()
    {
        // Create law categories
        $categories = LawCategory::factory()->count(3)->create();

        // Create a request
        $request = new Request();

        // Call the index method
        $response = $this->lawCategoryController->index($request);

        // Assert the response has HTTP 200 status
        $this->assertEquals(Response::HTTP_OK, $response->getStatusCode());

        // Assert the response contains all categories
        $responseData = json_decode($response->getContent(), true);
        $this->assertCount(3, $responseData);
    }

    #[Test]
    public function store_creates_new_law_category_and_returns_success()
    {
        // Create category data
        $categoryData = [
            'name' => 'New Category',
            'description' => 'Test description'
        ];

        // Create a request
        $request = new Request($categoryData);
        $request->setMethod('POST');

        // Call the store method
        $response = $this->lawCategoryController->store($request);

        // Assert the response has HTTP 201 status
        $this->assertEquals(Response::HTTP_CREATED, $response->getStatusCode());

        // Assert the response contains the success message
        $responseData = json_decode($response->getContent(), true);
        $this->assertEquals('A jogszabály kategória sikeresen létrehozva.', $responseData['message']);

        // Assert the category was created in the database
        $this->assertDatabaseHas('law_categories', [
            'name' => 'New Category',
            'description' => 'Test description'
        ]);
    }

    #[Test]
    public function show_returns_law_category_by_id()
    {
        // Create a law category
        $category = LawCategory::factory()->create([
            'name' => 'Test Category',
            'description' => 'Test description'
        ]);

        // Create a request
        $request = new Request();

        // Call the show method
        $response = $this->lawCategoryController->show($request, $category->id);

        // Assert the response has HTTP 200 status
        $this->assertEquals(Response::HTTP_OK, $response->getStatusCode());

        // Assert the response contains the category data
        $responseData = json_decode($response->getContent(), true);
        $this->assertEquals($category->id, $responseData['id']);
        $this->assertEquals('Test Category', $responseData['name']);
        $this->assertEquals('Test description', $responseData['description']);
    }

    #[Test]
    public function show_includes_related_laws_when_requested()
    {
        // Create a law category
        $category = LawCategory::factory()->create();

        // Create laws for this category
        $laws = Law::factory()->count(2)->create(['category_id' => $category->id]);

        // Create a request with include=laws parameter
        $request = new Request(['include' => 'laws']);

        // Call the show method
        $response = $this->lawCategoryController->show($request, $category->id);

        // Assert the response has HTTP 200 status
        $this->assertEquals(Response::HTTP_OK, $response->getStatusCode());

        // Assert the response includes the related laws
        $responseData = json_decode($response->getContent(), true);
        $this->assertArrayHasKey('laws', $responseData);
        $this->assertCount(2, $responseData['laws']);
    }

    #[Test]
    public function update_modifies_law_category_and_returns_success()
    {
        // Create a law category
        $category = LawCategory::factory()->create([
            'name' => 'Old Category',
            'description' => 'Old description'
        ]);

        // Create update data
        $updateData = [
            'name' => 'Updated Category',
            'description' => 'Updated description'
        ];

        // Create a request
        $request = new Request($updateData);
        $request->setMethod('PUT');

        // Call the update method
        $response = $this->lawCategoryController->update($request, $category->id);

        // Assert the response has HTTP 200 status
        $this->assertEquals(Response::HTTP_OK, $response->getStatusCode());

        // Assert the response contains the success message
        $responseData = json_decode($response->getContent(), true);
        $this->assertEquals('A jogszabály kategória adatai sikeresen frissítve lettek.', $responseData['message']);

        // Assert the category was updated in the database
        $this->assertDatabaseHas('law_categories', [
            'id' => $category->id,
            'name' => 'Updated Category',
            'description' => 'Updated description'
        ]);
    }

    #[Test]
    public function destroy_deletes_law_category_and_returns_success()
    {
        // Create a law category
        $category = LawCategory::factory()->create([
            'name' => 'Deletable Category'
        ]);

        // Call the destroy method
        $response = $this->lawCategoryController->destroy($category->id);

        // Assert the response has HTTP 200 status
        $this->assertEquals(Response::HTTP_OK, $response->getStatusCode());

        // Assert the response contains the success message
        $responseData = json_decode($response->getContent(), true);
        $this->assertEquals('Deletable Category jogszabály kategória sikeresen törölve.', $responseData['message']);

        // Assert the category was deleted from the database
        $this->assertDatabaseMissing('law_categories', ['id' => $category->id]);
    }

    #[Test]
    public function destroy_returns_forbidden_if_category_has_laws()
    {
        // Create a law category
        $category = LawCategory::factory()->create([
            'name' => 'Category With Laws'
        ]);

        // Create a law associated with this category
        $law = Law::factory()->create(['category_id' => $category->id]);

        // Call the destroy method
        $response = $this->lawCategoryController->destroy($category->id);

        // Assert the response has HTTP 403 status
        $this->assertEquals(Response::HTTP_FORBIDDEN, $response->getStatusCode());

        // Assert the response contains the error message
        $responseData = json_decode($response->getContent(), true);
        $this->assertEquals('Ez a kategória jogszabályokhoz van rendelve, ezért nem törölhető.', $responseData['message']);

        // Assert the category was not deleted
        $this->assertDatabaseHas('law_categories', ['id' => $category->id]);
    }

    public function tearDown(): void
    {
        if (\DB::transactionLevel() > 0) {
            \DB::rollBack();
        }
        parent::tearDown();
    }
}
