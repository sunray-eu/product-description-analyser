<?php

namespace SunrayEu\ProductDescriptionAnalyser\App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use SunrayEu\ProductDescriptionAnalyser\App\Models\Product;

class ProductDescriptionProcessed implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;
    public $product;

    /**
     * The name of the queue on which to place the broadcasting job.
     *
     * @var string
     */
    public $queue = 'high';

    /**
     * Create a new event instance.
     */
    public function __construct(Product $product)
    {
        $this->product = $product;
        //
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return array<int, \Illuminate\Broadcasting\Channel>
     */
    public function broadcastOn(): array
    {
        return [
            new Channel('product.updates'),
        ];
    }

    /**
     * The event's broadcast name.
     */
    public function broadcastAs(): string
    {
        return 'updated';
    }
}
