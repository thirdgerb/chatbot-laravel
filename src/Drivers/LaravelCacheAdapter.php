<?php

/**
 * Class LaravelCacheAdapter
 * @package Commune\Chatbot\Laravel\Drivers
 * @author BrightRed
 */

namespace Commune\Chatbot\Laravel\Drivers;

use Commune\Chatbot\Contracts\CacheAdapter;
use Illuminate\Redis\Connections\Connection;
use Illuminate\Support\Facades\Redis;
use Predis\Client;

class LaravelCacheAdapter implements CacheAdapter
{
    /**
     * @var \Illuminate\Redis\Connections\Connection
     */
    protected $connection;

    /**
     * @return Client
     */
    protected function getRedis() : Connection
    {
        return $this->connection ?? $this->connection = Redis::connection();
    }

    public function set(string $key, string $value, int $ttl): bool
    {
        return (bool) $this->getRedis()->setex($key, $ttl, $value);
    }

    public function has(string $key): bool
    {
        return $this->getRedis()->exists($key);
    }

    public function get(string $key): ? string
    {
        $value = $this->getRedis()->get($key);
        return is_string($value) ? $value : null;
    }

    public function lock(string $key, int $ttl = null): bool
    {
        $redis = $this->getRedis();
        $locked = $redis->setnx($key, 1);

        if ($locked && $ttl > 0) {
            $redis->expire($key, $ttl);
        }
        return $locked;
    }

    public function unlock(string $key): bool
    {
        return $this->forget($key);
    }


    public function forget(string $key): bool
    {
        return $this->getRedis()->del($key) > 0;
    }


}