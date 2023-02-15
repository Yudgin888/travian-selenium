<?php

declare(strict_types=1);

namespace App\Storage;

use Predis\Client as RedisClient;

/**
 * Class RedisStorage
 *
 * @package App\Storage
 */
class RedisStorage
{
    /**
     * @param RedisClient $redisClient
     */
    public function __construct(
        private readonly RedisClient $redisClient
    )
    {
    }

    /**
     * @param string $key
     * @param mixed $value
     * @param int|null $expireTTL
     * @return void
     */
    public function save(string $key, mixed $value, int $expireTTL = null): void
    {
        $params = [];
        if ($expireTTL) {
            $params = ['PX', $expireTTL];
        }
        $this->redisClient->set($key, $value, ...$params);
    }

    /**
     * @param string $key
     * @return string|null
     */
    public function load(string $key): ?string
    {
        return $this->redisClient->get($key);
    }
}