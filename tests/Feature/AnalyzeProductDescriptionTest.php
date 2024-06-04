<?php
namespace Tests\Feature;

use Illuminate\Support\Facades\Event;
use Google\Cloud\Language\LanguageClient;
use Illuminate\Support\Facades\Facade;
use Mockery;
use SunrayEu\ProductDescriptionAnalyser\App\Events\ProductDescriptionProcessed;
use SunrayEu\ProductDescriptionAnalyser\App\Utils\LanguageClientInstance;
use Tests\Mocks\MockLanguageClientInstance;
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

    protected function clearStaticProperties()
    {
        // Reset any static properties that may hold state between tests
        $reflectionClass = new \ReflectionClass(LanguageClientInstance::class);
        $instanceProperty = $reflectionClass->getProperty('instance');
        $instanceProperty->setAccessible(true);
        $instanceProperty->setValue(null);
    }

    /** @test */
    public function it_dispatches_analyze_product_description_job()
    {
        Queue::fake();

        $product = Product::factory()->create(['score' => null]);

        AnalyzeProductDescription::dispatch($product);

        Queue::assertPushed(AnalyzeProductDescription::class, function ($job) use ($product) {
            return $job->product->id === $product->id;
        });
    }

    // TODO: fix
    /** @test */
    public function it_processes_analyze_product_description_job()
    {
        $product = Product::factory()->create([
            'score' => null,
            'description' => 'Test description'
        ]);

        // Create a mock LanguageClient
        // $languageClientMock = Mockery::mock(LanguageClient::class);
        // $languageClientMock->shouldReceive('analyzeSentiment')
        //     ->with('Test description')
        //     ->andReturn(new class {
        //         public function sentiment() {
        //             return ['score' => 0.8];
        //         }
        //     });

        // Mock the LanguageClientInstance
        // $languageClientInstanceMock = Mockery::mock('alias:' . LanguageClientInstance::class);
        // $languageClientInstanceMock->shouldReceive('getClient')->andReturn($languageClientMock);


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
