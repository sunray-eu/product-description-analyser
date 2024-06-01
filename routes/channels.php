<?php

use Illuminate\Support\Facades\Broadcast;
use SunrayEu\ProductDescriptionAnalyser\App\Broadcasting\ProductChannel;

// Broadcast::channel('product.updates', ProductChannel::class);
Broadcast::channel('product.updates', fn($user) => true);
