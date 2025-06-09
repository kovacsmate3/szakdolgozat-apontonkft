<?php

namespace Tests\Unit\Models;

use App\Models\Law;
use App\Models\LawCategory;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\TestCase;
use PHPUnit\Framework\Attributes\Test;

class LawTest extends TestCase
{
    use RefreshDatabase;

    public function setUp(): void
    {
        parent::setUp();

        // Kezdj új tranzakciót
        \DB::beginTransaction();
    }

    #[Test]
    public function it_belongs_to_category()
    {
        $category = LawCategory::factory()->create();
        $law = Law::factory()->create(['category_id' => $category->id]);

        $this->assertEquals($category->id, $law->category->id);
        $this->assertInstanceOf(LawCategory::class, $law->category);
    }

    #[Test]
    public function it_casts_attributes_correctly()
    {
        $law = Law::factory()->create([
            'date_of_enactment' => '2024-01-01',
            'is_active' => 1
        ]);

        $this->assertInstanceOf(\Carbon\Carbon::class, $law->date_of_enactment);
        $this->assertIsBool($law->is_active);
        $this->assertTrue($law->is_active);
    }

    public function tearDown(): void
    {
        // Görgess vissza minden tranzakciót
        \DB::rollBack();
        parent::tearDown();
    }
}
