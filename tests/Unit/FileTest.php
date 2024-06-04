<?php
namespace Tests\Unit;

use Tests\TestCase;
use SunrayEu\ProductDescriptionAnalyser\App\Models\File;
use Illuminate\Foundation\Testing\RefreshDatabase;

class FileTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_has_a_name()
    {
        $file = File::factory()->create(['name' => 'Sample File']);

        $this->assertEquals('Sample File', $file->name);
    }

    public function test_it_has_a_hash()
    {
        $file = File::factory()->create(['hash' => 'samplefilehash']);

        $this->assertEquals('samplefilehash', $file->hash);
    }

    public function test_it_has_many_products()
    {
        $file = File::factory()->hasProducts(3)->create();

        $this->assertCount(3, $file->products);
    }
}
