<?php

declare(strict_types=1);

namespace App\Service;

use Facebook\WebDriver\WebDriverBy;
use JsonException;

/**
 * Class TravianHandler
 *
 * @package App\Service
 */
class TravianHandler extends Handler
{
    /**
     * @return string
     */
    public function getClientId(): string
    {
        return $this->driver->executeScript('return getClientId();');
    }

    /**
     * @param int $x
     * @param int $y
     * @return int
     */
    public function xy2id(int $x, int $y): int
    {
        return intval($this->driver->executeScript("return xy2id($x, $y);"));
    }

    /**
     * @param int $id
     * @return array{x: integer, y: integer}
     */
    public function id2xy(int $id): array
    {
        return $this->driver->executeScript("return id2xy($id);");
    }

    /**
     * @return mixed[]
     * @throws JsonException
     */
    public function getTravianConfig(): array
    {
        return $this->getGlobalObj('Travian.Config');
    }

    /**
     * @return mixed[]
     * @throws JsonException
     */
    public function getPlayer(): array
    {
        return $this->getGlobalObj('player');
    }

    /**
     * @return void
     */
    public function acceptAllCookies(): void
    {
        $acceptAllButton = $this->driver->findElement(WebDriverBy::cssSelector('#cmpwelcomebtnyes a'));
        $acceptAllButton->click();
        $this->after('acceptAllCookies');
    }

    /**
     * @return void
     */
    public function login(): void
    {
        $loginButton = $this->driver->findElement(WebDriverBy::id('loginButton'));
        $loginButton->click();
        $this->wait(3);
        $this->after('loginButtonClick');

        $mellonIframe = $this->driver->findElement(WebDriverBy::xpath("//iframe[@class='mellon-iframe']"));
        $this->driver->switchTo()->frame($mellonIframe);
        $mellonIframeIframe = $this->driver->findElement(WebDriverBy::xpath("//iframe"));
        $this->driver->switchTo()->frame($mellonIframeIframe);

        $email = $this->driver->findElement(WebDriverBy::name('email'));
        $email->sendKeys($this->configProvider->getAccountEmail());
        $pass = $this->driver->findElement(WebDriverBy::name('password'));
        $pass->sendKeys($this->configProvider->getAccountPass());
        $submit = $this->driver->findElement(WebDriverBy::name('submit'));
        $submit->click();
        $this->wait(5);
        $this->after('loginSubmit');
    }

    /**
     * @return void
     */
    public function continuePlaying(): void
    {
        $continueButton = $this->driver->findElement(WebDriverBy::cssSelector('.last-active-game-world .container-footer button'));
        $continueButton->click();
        $this->wait(5);
        $this->after('continuePlaying');
    }
}