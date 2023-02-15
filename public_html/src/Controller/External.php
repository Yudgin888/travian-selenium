<?php

declare(strict_types=1);

namespace App\Controller;

use App\ConfigProvider\ConfigProvider;

/**
 * Class External
 *
 * @package App\Controller
 */
class External
{
    /**
     * @param ConfigProvider $configProvider
     */
    public function __construct(
        private readonly ConfigProvider $configProvider
    )
    {
    }

    /**
     * @return void
     */
    public function __invoke(): void
    {
        $world = 'ru4';
        $apiUri = "https://{$world}.kingdoms.com/api/external.php";

        $email = $this->configProvider->getAccountEmail();      // Needs to be a valid email [should be one where we can reach you if we have some questions]
        $siteName = 'myBot';                                    // Name of the tool
        $siteUrl = 'https://google.com';    // Url of the tool - needs to be a valid url
        $public = false;                                        // If you set it to true, that means we maybe include your tool in a tool list.
        $apiKeyUri = "$apiUri?action=requestApiKey&email=$email&siteName=$siteName&siteUrl={$siteUrl}&public={$public}";
        $apiKey = json_decode(file_get_contents($apiKeyUri), true);

        $privateApiKey = $apiKey['response']['privateApiKey'];
        $date = date('d.m.Y');                           // String (optional) : Needs to be a date in format: d.m.Y (e.g. 27.08.2014). If no date is present, the today will be used.
        $mapDataUri = "$apiUri?action=getMapData&privateApiKey=$privateApiKey";
        $mapData = json_decode(file_get_contents($mapDataUri), true);

        foreach ($mapData['response']['map']['cells'] as $cell) {
            if($cell['x'] == 28 && $cell['y'] == 3) {

            }
        }
    }
}