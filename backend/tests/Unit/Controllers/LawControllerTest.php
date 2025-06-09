<?php

namespace Tests\Unit\Controllers;

use App\Http\Controllers\Api\Shared\LawController;
use App\Models\Law;
use App\Models\LawCategory;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Mockery;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class LawControllerTest extends TestCase
{
    use RefreshDatabase;

    protected $lawController;

    public function setUp(): void
    {
        parent::setUp();
        if (\DB::transactionLevel() > 0) {
            \DB::rollBack();
        }
        $this->lawController = new LawController();
    }

    #[Test]
    public function index_returns_all_laws()
    {
        // Create laws
        $laws = Law::factory()->count(3)->create();

        // Create a request
        $request = new Request();

        // Call the index method
        $response = $this->lawController->index($request);

        // Assert the response has HTTP 200 status
        $this->assertEquals(Response::HTTP_OK, $response->getStatusCode());

        // Assert the response contains all laws
        $responseData = json_decode($response->getContent(), true);
        $this->assertCount(3, $responseData);
    }

    #[Test]
    public function index_filters_by_category_id()
    {
        // Create categories
        $category1 = LawCategory::factory()->create();
        $category2 = LawCategory::factory()->create();

        // Create laws for each category
        Law::factory()->create(['category_id' => $category1->id]);
        Law::factory()->count(2)->create(['category_id' => $category2->id]);

        // Create a request with category filter
        $request = new Request(['category_id' => $category2->id]);

        // Call the index method
        $response = $this->lawController->index($request);

        // Assert the response has HTTP 200 status
        $this->assertEquals(Response::HTTP_OK, $response->getStatusCode());

        // Assert the response contains only laws from category2
        $responseData = json_decode($response->getContent(), true);
        $this->assertCount(2, $responseData);
        $this->assertEquals($category2->id, $responseData[0]['category_id']);
        $this->assertEquals($category2->id, $responseData[1]['category_id']);
    }

    #[Test]
    public function index_filters_by_is_active()
    {
        // Create active and inactive laws
        Law::factory()->create(['is_active' => true]);
        Law::factory()->create(['is_active' => true]);
        Law::factory()->create(['is_active' => false]);

        // Create a request with is_active filter
        $request = new Request(['is_active' => true]);

        // Call the index method
        $response = $this->lawController->index($request);

        // Assert the response has HTTP 200 status
        $this->assertEquals(Response::HTTP_OK, $response->getStatusCode());

        // Assert the response contains only active laws
        $responseData = json_decode($response->getContent(), true);
        $this->assertCount(2, $responseData);
        $this->assertTrue($responseData[0]['is_active']);
        $this->assertTrue($responseData[1]['is_active']);
    }

    #[Test]
    public function store_creates_new_law_and_returns_success()
    {
        // Create a category
        $category = LawCategory::factory()->create();

        // Create law data
        $lawData = [
            'category_id' => $category->id,
            'title' => 'New Law',
            'official_ref' => '2024. évi TEST. tv',
            'date_of_enactment' => '2024-01-01',
            'is_active' => true,
            'link' => 'https://example.com/law'
        ];

        // Create a request
        $request = new Request($lawData);
        $request->setMethod('POST');

        // Call the store method
        $response = $this->lawController->store($request);

        // Assert the response has HTTP 201 status
        $this->assertEquals(Response::HTTP_CREATED, $response->getStatusCode());

        // Assert the response contains the success message
        $responseData = json_decode($response->getContent(), true);
        $this->assertEquals('A jogszabály sikeresen létrehozva.', $responseData['message']);

        // Assert the law was created in the database
        $this->assertDatabaseHas('laws', [
            'category_id' => $category->id,
            'title' => 'New Law',
            'official_ref' => '2024. évi TEST. tv',
            'is_active' => 1,
            'link' => 'https://example.com/law'
        ]);
    }

    #[Test]
    public function show_returns_law_by_id()
    {
        // Create a law
        $law = Law::factory()->create([
            'title' => 'Test Law',
            'official_ref' => '2024. évi TEST. tv',
            'is_active' => true
        ]);

        // Create a request
        $request = new Request();

        // Call the show method
        $response = $this->lawController->show($request, $law->id);

        // Assert the response has HTTP 200 status
        $this->assertEquals(Response::HTTP_OK, $response->getStatusCode());

        // Assert the response contains the law data
        $responseData = json_decode($response->getContent(), true);
        $this->assertEquals($law->id, $responseData['id']);
        $this->assertEquals('Test Law', $responseData['title']);
        $this->assertEquals('2024. évi TEST. tv', $responseData['official_ref']);
        $this->assertTrue($responseData['is_active']);
    }

    #[Test]
    public function update_modifies_law_and_returns_success()
    {
        // Create a law
        $law = Law::factory()->create([
            'title' => 'Old Law',
            'official_ref' => '2024. évi OLD. tv',
            'is_active' => true
        ]);

        // Create update data
        $updateData = [
            'title' => 'Updated Law',
            'is_active' => false
        ];

        // Create a request
        $request = new Request($updateData);
        $request->setMethod('PUT');

        // Call the update method
        $response = $this->lawController->update($request, $law->id);

        // Assert the response has HTTP 200 status
        $this->assertEquals(Response::HTTP_OK, $response->getStatusCode());

        // Assert the response contains the success message
        $responseData = json_decode($response->getContent(), true);
        $this->assertEquals('A jogszabály adatai sikeresen frissítve lettek.', $responseData['message']);

        // Assert the law was updated in the database
        $this->assertDatabaseHas('laws', [
            'id' => $law->id,
            'title' => 'Updated Law',
            'official_ref' => '2024. évi OLD. tv', // Unchanged
            'is_active' => 0
        ]);
    }

    #[Test]
    public function destroy_deletes_law_and_returns_success()
    {
        // Create a law
        $law = Law::factory()->create([
            'title' => 'Deletable Law',
            'official_ref' => '2024. évi DEL. tv'
        ]);

        // Call the destroy method
        $response = $this->lawController->destroy($law->id);

        // Assert the response has HTTP 200 status
        $this->assertEquals(Response::HTTP_OK, $response->getStatusCode());

        // Assert the response contains the success message
        $responseData = json_decode($response->getContent(), true);
        $this->assertEquals('2024. évi DEL. tv (Deletable Law) jogszabály sikeresen törölve.', $responseData['message']);

        // Assert the law was deleted from the database
        $this->assertDatabaseMissing('laws', ['id' => $law->id]);
    }

    public function tearDown(): void
    {
        if (\DB::transactionLevel() > 0) {
            \DB::rollBack();
        }
        Mockery::close();
        parent::tearDown();
    }
}
