<?php

/**
 * Class LaravelCacheAdapter
 * @package Commune\Chatbot\Laravel\Drivers
 * @author BrightRed
 */

namespace Commune\Chatbot\Laravel\Drivers;

use Commune\Chatbot\Blueprint\Conversation\ConversationLogger;
use Commune\Chatbot\Contracts\CacheAdapter;
use Commune\Chatbot\Framework\Conversation\RunningSpyTrait;
use Illuminate\Redis\Connections\Connection;

class LaravelCacheAdapter implements CacheAdapter
{
    use RunningSpyTrait;

    /**
     * @var \Illuminate\Redis\Connections\Connection
     */
    protected $connection;

    /**
     * @var ConversationLogger
     */
    protected $logger;

    /**
     * @var LaravelDBDriver
     */
    protected $driver;

    /**
     * @var Connection
     */
    protected $redis;

    /**
     * @var string
     */
    protected $traceId;

    /**
     * LaravelCacheAdapter constructor.
     * @param ConversationLogger $logger
     * @param LaravelDBDriver $driver
     */
    public function __construct(ConversationLogger $logger, LaravelDBDriver $driver)
    {
        $this->logger = $logger;
        $this->driver = $driver;
        $this->traceId = $driver->getTraceId();
        self::addRunningTrace($this->traceId, $this->traceId);
    }


    public function set(string $key, string $value, int $ttl): bool
    {
        return (bool) $this->driver->getRedis()->setex($key, $ttl, $value);
    }

    public function has(string $key): bool
    {
        return $this->driver->getRedis()->exists($key);
    }

    public function get(string $key): ? string
    {
        $value = $this->driver->getRedis()->get($key);
        return is_string($value) ? $value : null;
    }

    public function lock(string $key, int $ttl = null): bool
    {
        $redis = $this->driver->getRedis();
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
        return $this->driver->getRedis()->del([$key]) > 0;
    }

    public function __destruct()
    {
        static::removeRunningTrace($this->traceId);
    }

}