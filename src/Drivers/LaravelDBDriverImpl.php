<?php

/**
 * Class LaravelDBDriverImpl
 * @package Commune\Chatbot\Laravel\Drivers
 */

namespace Commune\Chatbot\Laravel\Drivers;


use Commune\Chatbot\Blueprint\Conversation\Conversation;
use Commune\Chatbot\Framework\Conversation\RunningSpyTrait;
use Illuminate\Database\DatabaseManager;
use Illuminate\Redis\RedisManager;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redis;
use Illuminate\Redis\Connections\Connection as RedisConnection;
use Illuminate\Database\Connection as DBConnection;

class LaravelDBDriverImpl implements LaravelDBDriver
{
    use RunningSpyTrait;

    const RECONNECT_AFTER  = 50;

    protected static $running = [];

    protected static $count = 0;

    /**
     * @var string
     */
    protected $traceId;

    /**
     * @var DatabaseManager
     */
    protected $dbManager;

    /**
     * @var RedisManager
     */
    protected $redisManager;

    public function __construct(Conversation $conversation)
    {
        $this->traceId = $conversation->getTraceId();
        static::$running[$this->traceId] = $this->traceId;
        self::addRunningTrace($this->traceId, $this->traceId);
        $this->bootstrap();
    }

    protected function bootstrap() : void
    {
        /**
         * @var RedisManager $redis
         * @var DatabaseManager $dbManager
         */
        $this->redisManager = Redis::getFacadeRoot();
        $this->dbManager = DB::getFacadeRoot();

        static::$count++;
        if (static::$count > static::RECONNECT_AFTER) {
            $this->redisManager->disconnect();
            $this->redisManager->connection();
            $this->dbManager->reconnect();
        }
    }

    public function getRedis(): RedisConnection
    {
        return $this->redisManager->connection();
    }

    public function getDB(): DBConnection
    {
        return $this->dbManager->connection();
    }

    public function getTraceId(): string
    {
        return $this->traceId;
    }


    public function __destruct()
    {
        self::removeRunningTrace($this->traceId);
    }



}