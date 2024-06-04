<?php

namespace SunrayEu\ProductDescriptionAnalyser\App\Utils;

use Google\Cloud\Language\LanguageClient;

class SentimentAnalyser
{
    private LanguageClient|null $gcpLangClient = null;
    private static SentimentAnalyser|null $instance = null;

    /**
     * Load client if GCP environment variables are set.
     */
    public function __construct()
    {
        $keyFilePath = $_ENV['GCP_LANG_CLIENT_SA_KEY_FILE_PATH'] ?? null;
        if ((!empty($keyFilePath) && file_exists($keyFilePath)) || !empty($_ENV['GCP_LANG_CLIENT_SA_KEY'])) {
            $this->gcpLangClient = new LanguageClient([
                'keyFilePath' => $_ENV['GCP_LANG_CLIENT_SA_KEY_FILE_PATH'] ?? null,
                'keyFile' => $_ENV['GCP_LANG_CLIENT_SA_KEY'] ?? null
            ]);
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

    public static function getSentimentScore(string $text): float
    {
        $gcpClient = self::getGcpClient();

        if ($gcpClient) {
            $response = $gcpClient->analyzeSentiment($text);
            return $response->sentiment()['score'];
        } else {
            return self::getLocalSentimentScore($text);
        }
    }

    private static function getLocalSentimentScore(string $text): float
    {
        $escapedText = escapeshellarg($text);
        $output = [];
        $returnVar = 0;
        exec("npm run analyse-sentiment $escapedText", $output, $returnVar);

        if ($returnVar !== 0) {
            throw new \RuntimeException('Error running local sentiment analysis');
        }

        return (float)trim($output[0]);
    }
}
