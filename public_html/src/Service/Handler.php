<?php

declare(strict_types=1);

namespace App\Service;

use App\ConfigProvider\ConfigProvider;
use DateTime;
use Facebook\WebDriver\Chrome\ChromeOptions;
use Facebook\WebDriver\Cookie;
use Facebook\WebDriver\Remote\DesiredCapabilities;
use Facebook\WebDriver\Remote\RemoteWebDriver;
use Facebook\WebDriver\WebDriverPlatform;
use JsonException;

/**
 * Class Handler
 *
 * @package App\Service
 */
class Handler
{
    private const ARGUMENTS = [
        //"--headless",
        //"--disable-dev-shm-usage",
        "--disable-gpu",
        "--no-sandbox",
        "--window-size=1920,1080"
    ];

    private const CONN_TIMEOUT_MS = 20000;

    protected bool $isTakeScreens = false;

    protected RemoteWebDriver $driver;

    /**
     * @param ConfigProvider $configProvider
     */
    public function __construct(
        protected readonly ConfigProvider $configProvider
    )
    {
        $options = new ChromeOptions();
        $options->addArguments(self::ARGUMENTS);
        $capabilities = DesiredCapabilities::chrome();
        $capabilities->setCapability(ChromeOptions::CAPABILITY_W3C, $options);
        $capabilities->setPlatform(WebDriverPlatform::LINUX);
        //$capabilities->setJavascriptEnabled(true); // htmlunit-only option

        $this->driver = RemoteWebDriver::create($this->configProvider->getHost(), $capabilities, self::CONN_TIMEOUT_MS);
        $this->driver->manage()->window()->maximize();
    }

    /**
     * @return void
     */
    public function enableScreenshots(): void
    {
        $this->isTakeScreens = true;
    }

    /**
     * @return void
     */
    public function disableScreenshots(): void
    {
        $this->isTakeScreens = false;
    }

    /**
     * @param string $name
     * @return string|null
     */
    public function takeScreenshot(string $name): ?string
    {
        if ($this->isTakeScreens) {
            return $this->driver->takeScreenshot(
                $this->configProvider->getScreenshotPath() . '/'
                . DateTime::createFromFormat('U.u', (string)microtime(true))->format('Y-m-d_H:i:s.u') . '-'
                . $name . '.png'
            );
        }

        return null;
    }

    /**
     * @param string $url
     * @return RemoteWebDriver
     */
    public function getPage(string $url): RemoteWebDriver
    {
        $page = $this->driver->get($url);
        $this->wait(3);
        $this->after('getPage');
        return $page;
    }

    /**
     * @return string
     */
    public function getUserAgent(): string
    {
        return strval($this->driver->executeScript('return navigator.userAgent;'));
    }

    /**
     * @param string $objName
     * @return mixed[]
     * @throws JsonException
     */
    public function getGlobalObj(string $objName): array
    {
        $stringifyObj = strval($this->driver->executeScript("return JSON.stringify($objName);"));
        return json_decode($stringifyObj, true, 512, JSON_THROW_ON_ERROR);
    }

    /**
     * @param string $objName
     * @return mixed[]
     * @throws JsonException
     */
    public function getGlobalCircularObj(string $objName): array
    {
        $stringifyObj = strval($this->driver->executeScript("
        function cleanStringify(object) {
            if (object && typeof object === 'object') {
                object = copyWithoutCircularReferences([object], object);
            }
            return JSON.stringify(object);

            function copyWithoutCircularReferences(references, object) {
                var cleanObject = {};
                Object.keys(object).forEach(function(key) {
                    var value = object[key];
                    if (value && typeof value === 'object') {
                        if (references.indexOf(value) < 0) {
                            references.push(value);
                            cleanObject[key] = copyWithoutCircularReferences(references, value);
                            references.pop();
                        } else {
                            cleanObject[key] = '###_Circular_###';
                        }
                    } else if (typeof value !== 'function') {
                        cleanObject[key] = value;
                    }
                });
                return cleanObject;
            }
        };
        return cleanStringify($objName);"
        ));
        return json_decode($stringifyObj, true, 512, JSON_THROW_ON_ERROR);
    }

    /**
     * @return string[]
     */
    public function getKeys(): array
    {
        return $this->driver->executeScript('return Object.keys(window);');
    }

    /**
     * @return string
     */
    public function getSessionID(): string
    {
        return $this->driver->getSessionID();
    }

    /**
     * @return Cookie[]
     */
    public function getCookies(): array
    {
        return $this->driver->manage()->getCookies();
    }

    /**
     * @return array{int, array{string, mixed}}
     */
    public function getCookiesAsArray(): array
    {
        return array_map(static function (Cookie $cookie) {
            return $cookie->toArray();
        }, $this->getCookies());
    }

    /**
     * @param string $screenName
     * @return void
     */
    protected function after(string $screenName = ''): void
    {
        if ($screenName !== '') {
            $this->takeScreenshot($screenName);
        }
    }

    /**
     * @param int $seconds
     * @return void
     */
    protected function wait(int $seconds): void
    {
        $this->driver->manage()->timeouts()->implicitlyWait($seconds);
        sleep($seconds);
    }

    /**
     * @return void
     */
    public function quit(): void
    {
        // Make sure to always call quit() at the end to terminate the browser session
        $this->driver->quit();
    }

    public function __destruct()
    {
        $this->quit();
    }
}