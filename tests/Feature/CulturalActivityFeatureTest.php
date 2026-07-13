<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\Eatery;
use App\Models\Category;
use App\Models\Commune;
use App\Models\CulturalActivity;

class CulturalActivityFeatureTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test eatery can have cultural activities relationship.
     */
    public function test_eatery_has_cultural_activities_relation(): void
    {
        $category = Category::create([
            'name' => 'Hành trình di sản',
            'slug' => 'hanh-trinh-di-san',
            'icon' => '🏛️'
        ]);

        $commune = Commune::create([
            'name' => 'Cổ Loa',
            'slug' => 'co-loa'
        ]);

        $eatery = Eatery::create([
            'name' => 'Khu di tích Cổ Loa',
            'slug' => 'khu-di-tich-co-loa',
            'category_id' => $category->id,
            'commune_id' => $commune->id,
            'address' => 'Đông Anh, Hà Nội',
            'phone' => '123',
            'latitude' => 21.0,
            'longitude' => 105.0,
            'status' => 'active',
            'is_featured' => true
        ]);

        $activity = CulturalActivity::create([
            'eatery_id' => $eatery->id,
            'name' => 'Bắn nỏ liên châu',
            'type' => 'experience',
            'price' => 1000000,
            'unit' => 'đoàn',
            'description' => 'Trải nghiệm bắn nỏ cổ loa',
            'discount_note' => 'Học sinh giảm 50%'
        ]);

        $this->assertCount(1, $eatery->culturalActivities);
        $this->assertEquals('Bắn nỏ liên châu', $eatery->culturalActivities->first()->name);
    }
}
