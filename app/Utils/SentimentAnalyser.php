<?php

namespace SunrayEu\ProductDescriptionAnalyser\App\Utils;

use Illuminate\Support\Facades\Log;
use Google\Cloud\Language\LanguageClient;

class SentimentAnalyser
{
    private LanguageClient|null $gcpLangClient = null;
    private static SentimentAnalyser|null $instance = null;

    // Here you set default value of processor, normally it is 'nlp.js', but it can be 'gcp' for google cloud platform
    private static string $processor = 'nlp.js';

    /**
     * Load client if GCP environment variables are set.
     */
    public function __construct()
    {
        if (!empty($_ENV['SENTIMENT_PROCESSOR_TYPE'])) {
            $this->processor = $_ENV['SENTIMENT_PROCESSOR_TYPE'];
        }

        switch ($this->processor) {
            case 'gcp':
                $keyFilePath = $_ENV['GCP_LANG_CLIENT_SA_KEY_FILE_PATH'] ?? null;
                if ((!empty($keyFilePath) && file_exists($keyFilePath)) || !empty($_ENV['GCP_LANG_CLIENT_SA_KEY'])) {
                    $this->gcpLangClient = new LanguageClient([
                        'keyFilePath' => $_ENV['GCP_LANG_CLIENT_SA_KEY_FILE_PATH'] ?? null,
                        'keyFile' => $_ENV['GCP_LANG_CLIENT_SA_KEY'] ?? null
                    ]);
                }
                break;

            default:
                break;
        }

    }

    public static function getInstance()
    {
        if (!self::$instance) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    protected static function getGcpClient()
    {
        return self::getInstance()->gcpLangClient;
    }

    public static function getSentimentScore(string $text): float|null
    {
        switch (self::$processor) {
            case 'gcp':
                $gcpClient = self::getGcpClient();
                if ($gcpClient) {
                    $response = $gcpClient->analyzeSentiment($text);
                    return $response->sentiment()['score'];
                } else
                    return null;

            case 'nlp.js':
                return self::getLocalSentimentScore($text);

            default:

                break;
        }
    }

    private static function getLocalSentimentScore(string $text): float
    {
        $escapedText = escapeshellarg($text);
        $output = [];
        $returnVar = 0;
        exec("npm run analyse-sentiment $escapedText", $output, $returnVar);
        $output = end($output);

        Log::debug("Got output from npm analyse-sentiment: " . json_encode($output));

        if ($returnVar !== 0) {
            throw new \RuntimeException('Error running local sentiment analysis');
        }

        return (float) trim($output);
    }
}
