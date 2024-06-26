<?php

namespace SunrayEu\ProductDescriptionAnalyser\App\Jobs;

use SunrayEu\ProductDescriptionAnalyser\App\Events\ProductDescriptionProcessed;
use SunrayEu\ProductDescriptionAnalyser\App\Models\Product;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

use Illuminate\Contracts\Queue\ShouldBeUnique;
use SunrayEu\ProductDescriptionAnalyser\App\Utils\SentimentAnalyser;

class AnalyzeProductDescription implements ShouldQueue, ShouldBeUnique
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public Product $product;

    /**
     * Create a new job instance.
     */
    public function __construct(Product $product)
    {
        $this->product = $product;
    }

    /**
     * Get the unique ID for the job.
     */
    public function uniqueId(): string
    {
        return $this->product->id;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $score = SentimentAnalyser::getSentimentScore($this->product->description);
        $this->product->update(['score' => $score]);

        ProductDescriptionProcessed::dispatch($this->product);
    }

}
