<?php
namespace Tests\Unit;

use Tests\TestCase;
use SunrayEu\ProductDescriptionAnalyser\App\Models\File;
use Illuminate\Foundation\Testing\RefreshDatabase;

class FileTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_has_a_name()
    {
        $file = File::factory()->create(['name' => 'Sample File']);

        $this->assertEquals('Sample File', $file->name);
    }

    /** @test */
    public function it_has_a_hash()
    {
        $file = File::factory()->create(['hash' => 'samplefilehash']);

        $this->assertEquals('samplefilehash', $file->hash);
    }

    /** @test */
    public function it_has_many_products()
    {
        $file = File::factory()->hasProducts(3)->create();

        $this->assertCount(3, $file->products);
    }
}
