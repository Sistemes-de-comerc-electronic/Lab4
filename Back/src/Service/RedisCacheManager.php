<?php

namespace App\Service;

use Symfony\Component\Cache\Adapter\RedisAdapter;

class RedisCacheManager
{
    private $cache;

    public function __construct()
    {
        $this->cache = RedisAdapter::createConnection($_ENV['REDIS_DSN']);
    }

    public function get(string $key): ?string
    {
        return $this->cache->get($key);
    }

    public function set(string $key, string $value, int $ttl = 3600): void
    {
        $this->cache->setex($key, $ttl, $value);
    }

    public function queue(string $key, string $value): void
    {
        $this->cache->rpush($key, $value);
    }

    public function dequeue(string $key): ?string
    {
        return $this->cache->lpop($key);
    }
}
