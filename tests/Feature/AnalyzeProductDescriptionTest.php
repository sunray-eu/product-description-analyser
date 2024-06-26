<?php
namespace Tests\Feature;

use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Facade;
use Mockery;
use SunrayEu\ProductDescriptionAnalyser\App\Utils\SentimentAnalyser;
use Tests\TestCase;
use SunrayEu\ProductDescriptionAnalyser\App\Jobs\AnalyzeProductDescription;
use SunrayEu\ProductDescriptionAnalyser\App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;

class AnalyzeProductDescriptionTest extends TestCase
{
    use RefreshDatabase;

    protected function tearDown(): void
    {
        // Close and verify all Mockery mocks
        Mockery::close();

        // Clear resolved instances of Laravel facades
        Facade::clearResolvedInstances();

        // Clear any event fakes
        Event::clearResolvedInstances();

        // Additional cleanup if necessary
        // $this->clearStaticProperties();

        parent::tearDown();
    }

    public function test_it_dispatches_analyze_product_description_job()
    {
        Queue::fake();

        $product = Product::factory()->create(['score' => null]);

        AnalyzeProductDescription::dispatch($product);

        Queue::assertPushed(AnalyzeProductDescription::class, function ($job) use ($product) {
            return $job->product->id === $product->id;
        });
    }

    public function test_it_processes_analyze_product_description_job()
    {
        $product = Product::factory()->create([
            'score' => null,
            'description' => 'Test description'
        ]);

       // Create a mock SentimentAnalyser
       $sentimentAnalyserMock = Mockery::mock(SentimentAnalyser::class);
       $sentimentAnalyserMock->shouldReceive('getSentimentScore')
           ->with('Test description')
           ->andReturn(0.8);

       // Bind the mock to the container
       $this->app->instance(SentimentAnalyser::class, $sentimentAnalyserMock);

        $job = new AnalyzeProductDescription($product);
        $job->handle();

        // Assert that the product score was updated
        $this->assertNotNull($product->score);

        // Assert that the event was dispatched
        // Event::assertDispatched(ProductDescriptionProcessed::class, function ($event) use ($product) {
        //     return $event->product === $product;
        // });
    }
}
