<?php

declare(strict_types=1);

namespace App\Controller;

use App\ConfigProvider\ConfigProvider;
use App\Service\TravianHandler;
use App\Storage\RedisStorage;
use Facebook\WebDriver\Exception\NoSuchElementException;
use GuzzleHttp\Client;
use GuzzleHttp\Cookie\CookieJar;
use GuzzleHttp\Cookie\SetCookie;
use GuzzleHttp\RequestOptions;
use JsonException;

/**
 * Class Run
 *
 * @package App\Controller
 */
class Run
{
    private array $headers = [
        'accept' => 'application/json, text/plain, */*',
        'accept-encoding' => 'gzip, deflate, br',
        'accept-language' => 'en-US,en;q=0.9,be;q=0.8,ru;q=0.7',
        'content-type' => 'application/json;charset=UTF-8',
        'origin' => 'https://ru4.kingdoms.com',
        'referer' => 'https://ru4.kingdoms.com/',
        'sec-ch-ua' => '"Not_A Brand";v="99", "Google Chrome";v="109", "Chromium";v="109"',
        'sec-ch-ua-mobile' => '?0',
        'sec-ch-ua-platform' => '"Linux"',
        'sec-fetch-dest' => 'empty',
        'sec-fetch-mode' => 'cors',
        'sec-fetch-site' => 'same-origin',
    ];

    /**
     * @param ConfigProvider $configProvider
     * @param RedisStorage $storage
     * @param TravianHandler $handler
     */
    public function __construct(
        private readonly ConfigProvider $configProvider,
        private readonly RedisStorage   $storage,
        private TravianHandler          $handler
    )
    {
    }

    /**
     * @return void
     * @throws JsonException
     */
    public function __invoke(): void
    {
        //$this->handler->enableScreenshots();
        $this->login();
        //$this->saveSession();
        $this->handler->continuePlaying();


        $player = $this->handler->getPlayer();
        $clientId = $this->handler->getClientId();
        $sessionId = $this->handler->getSessionID(); // ??
        $cookies = $this->handler->getCookiesAsArray();
        $playerId = $player['data']['playerId'];
        $lastFilled = $player['lastFilled'];
        $world = 'ru4';
        $controller = 'cache';
        $action = 'get';
        $uri = "https://$world.kingdoms.com/api/?c=$controller&a=$action&p$playerId&t$lastFilled";
        $payloadArray = [
            'controller' => $controller,
            'action' => $action,
            'clientId' => $clientId,
            'session' => $sessionId,
            'params' => [
                'names' => [
                    'MapDetails:' . $this->handler->xy2id(5, -8)
                ]
            ],
        ];
        $headers = array_merge(
            $this->headers,
            [
                'user-agent' => $this->handler->getUserAgent()
            ]
        );

        $client = new Client();
        $cookieJar = new CookieJar();
        foreach ($cookies as $cookie) {
            $cookieJar->setCookie(
                new SetCookie(
                    [
                        'Name' => $cookie['name'] ?? null,
                        'Value' => $cookie['value'] ?? null,
                        'Domain' => $cookie['domain'] ?? null,
                        'Path' => $cookie['path'] ?? '/',
                        'Max-Age' => $cookie['max-Age'] ?? null,
                        'Expires' => $cookie['expires'] ?? null,
                        'Secure' => $cookie['secure'] ?? false,
                        'Discard' => $cookie['discard'] ?? false,
                        'HttpOnly' => $cookie['httpOnly'] ?? false,
                        'SameSite' => $cookie['sameSite'] ?? null,
                    ]
                )
            );
        }

        $res = $client->post($uri, [
            'headers' => $headers,
            'cookies' => $cookieJar,
            RequestOptions::JSON => $payloadArray
        ]);

    }

    /**
     * @return void
     */
    private function login(): void
    {
        $this->handler->getPage($this->configProvider->getStartPage());
        try {
            $this->handler->acceptAllCookies();
        } catch (NoSuchElementException) {
        }
        $this->handler->login();
    }

    /**
     * @return void
     * @throws JsonException
     */
    private function saveSession(): void
    {
        $data = [
            'timestamp' => time(),
            'sessionId' => $this->handler->getSessionID(),
            'cookies' => $this->handler->getCookiesAsArray()
        ];
        $this->storage->save($this->configProvider->getAccountEmail(), json_encode($data, JSON_THROW_ON_ERROR));
    }
}