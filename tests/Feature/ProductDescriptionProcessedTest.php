<?php
namespace Tests\Feature;

use Tests\TestCase;
use SunrayEu\ProductDescriptionAnalyser\App\Events\ProductDescriptionProcessed;
use SunrayEu\ProductDescriptionAnalyser\App\Models\Product;
use Illuminate\Support\Facades\Event;
use Illuminate\Foundation\Testing\RefreshDatabase;

class ProductDescriptionProcessedTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_dispatches_product_description_processed_event()
    {
        Event::fake();

        $product = Product::factory()->create();

        ProductDescriptionProcessed::dispatch($product);

        Event::assertDispatched(ProductDescriptionProcessed::class, function ($event) use ($product) {
            return $event->product->id === $product->id;
        });
    }

    public function test_it_broadcasts_on_correct_channel()
    {
        $product = Product::factory()->create();
        $event = new ProductDescriptionProcessed($product);

        $this->assertEquals('product.updates', $event->broadcastOn()->name);
    }

    public function test_it_has_correct_broadcast_name()
    {
        $product = Product::factory()->create();
        $event = new ProductDescriptionProcessed($product);

        $this->assertEquals('updated', $event->broadcastAs());
    }
}
