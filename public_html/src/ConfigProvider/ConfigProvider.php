<?php

declare(strict_types=1);

namespace App\ConfigProvider;

use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class ConfigProvider
 *
 * @package App\ConfigProvider
 */
class ConfigProvider
{
    /**
     * @param ContainerInterface $container
     */
    public function __construct(
        private readonly ContainerInterface $container,
    )
    {
    }

    /**
     * @return string
     */
    public function getHost(): string
    {
        return strval($this->container->getParameter('selenium_host'));
    }

    /**
     * @return string
     */
    public function getAccountEmail(): string
    {
        return strval($this->container->getParameter('account_email'));
    }

    /**
     * @return string
     */
    public function getAccountPass(): string
    {
        return strval($this->container->getParameter('account_pass'));
    }

    /**
     * @return string
     */
    public function getStartPage(): string
    {
        return strval($this->container->getParameter('start_page'));
    }

    /**
     * @return string
     */
    public function getScreenshotPath(): string
    {
        return strval($this->container->getParameter('screenshot_path'));
    }
}