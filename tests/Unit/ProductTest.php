<?php
namespace Tests\Unit;

use Tests\TestCase;
use SunrayEu\ProductDescriptionAnalyser\App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;

class ProductTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_has_a_name()
    {
        $product = Product::factory()->create(['name' => 'Sample Product']);

        $this->assertEquals('Sample Product', $product->name);
    }

    /** @test */
    public function it_has_a_description()
    {
        $product = Product::factory()->create(['description' => 'Sample Description']);

        $this->assertEquals('Sample Description', $product->description);
    }

    /** @test */
    public function it_has_a_hash()
    {
        $product = Product::factory()->create(['hash' => 'samplehash']);

        $this->assertEquals('samplehash', $product->hash);
    }

    /** @test */
    public function it_has_a_score()
    {
        $product = Product::factory()->create(['score' => 95]);

        $this->assertEquals(95, $product->score);
    }

    /** @test */
    public function it_belongs_to_a_file_or_files()
    {
        $product = Product::factory()->hasFiles(1)->create();
        $files = $product->files;

        $this->assertNotNull($files);
    }
}
