<?php

namespace Tests\Unit\Models;

use App\Models\Law;
use App\Models\LawCategory;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\TestCase;
use PHPUnit\Framework\Attributes\Test;

class LawCategoryTest extends TestCase
{
    use RefreshDatabase;

    public function setUp(): void
    {
        parent::setUp();
        // Kezdj új tranzakciót
        \DB::beginTransaction();
    }
    #[Test]
    public function it_has_many_laws()
    {
        $category = LawCategory::factory()->create();
        $law = Law::factory()->create(['category_id' => $category->id]);

        $this->assertTrue($category->laws->contains($law));
        $this->assertInstanceOf(Law::class, $category->laws->first());
    }

    public function tearDown(): void
    {
        // Görgess vissza minden tranzakciót
        \DB::rollBack();
        parent::tearDown();
    }
}
