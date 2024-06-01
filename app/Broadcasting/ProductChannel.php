<?php

namespace SunrayEu\ProductDescriptionAnalyser\App\Broadcasting;

use SunrayEu\ProductDescriptionAnalyser\App\Models\User;

class ProductChannel
{
    /**
     * Create a new channel instance.
     */
    public function __construct()
    {
        //
    }

    /**
     * Authenticate the user's access to the channel.
     */
    public function join(User $user, string $requestedHash): array|bool
    {
        $storedHash = session('file_hash');
        return $requestedHash === $storedHash;
    }
}
