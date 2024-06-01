<?php

namespace SunrayEu\ProductDescriptionAnalyser\App\Jobs;
use SunrayEu\ProductDescriptionAnalyser\App\Events\ProductDescriptionProcessed;
use SunrayEu\ProductDescriptionAnalyser\App\Models\Product;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

use Google\Cloud\Language\LanguageClient;

class AnalyzeProductDescription implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected Product $product;

    /**
     * Create a new job instance.
     */
    public function __construct(Product $product)
    {
        $this->product = $product;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        // TODO: use google LanguageClient, and more alternatives, later also local
        $client = new LanguageClient(['keyFilePath' => storage_path('app/google-cloud-key.json')]);
        $response = $client->analyzeSentiment($this->product->description);
        $score = $response->sentiment()['score'];
        $this->product->update(['score' => $score]);
        ProductDescriptionProcessed::dispatch($this->product);
    }
}
