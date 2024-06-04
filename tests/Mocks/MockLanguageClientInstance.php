<?php

namespace Tests\Mocks;

use SunrayEu\ProductDescriptionAnalyser\App\Utils\LanguageClientInstance;
use Google\Cloud\Language\LanguageClient;

class MockLanguageClientInstance extends LanguageClientInstance
{
    public function __construct()
    {
        // You can simulate the LanguageClient with a mock or a stub
        $this->gcpLangClient = $this->createMock(LanguageClient::class);

        // Mock the analyzeSentiment method
        $this->gcpLangClient->method('analyzeSentiment')->willReturn([
            'sentiment' => [
                'score' => 0.8 // You can return any score you want for testing purposes
            ]
        ]);
    }

    // Create a mock instance getter
    public static function getInstance()
    {
        if (!self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }
}
