<?php

namespace SunrayEu\ProductDescriptionAnalyser\App\Utils;

use Google\Cloud\Language\LanguageClient;

class LanguageClientInstance
{
    private LanguageClient $gcpLangClient;
    private static LanguageClientInstance|null $instance = null;
    /**
     * Load client.
     */
    public function __construct()
    {
        $this->gcpLangClient = new LanguageClient([
            'keyFilePath' => $_ENV['GCP_LANG_CLIENT_SA_KEY_FILE_PATH'],
            'keyFile' => $_ENV['GCP_LANG_CLIENT_SA_KEY']
        ]);
    }

    public static function getInstance()
    {
        if (!self::$instance) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    public static function getClient()
    {
        return self::getInstance()->gcpLangClient;
    }



}
