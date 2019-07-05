<?php

/**
 * Class LaravelDatabaseDirver
 * @package Commune\Chatbot\Laravel\Drivers
 */

namespace Commune\Chatbot\Laravel\Drivers;


use Commune\Chatbot\Blueprint\Conversation\RunningSpy;
use Illuminate\Redis\Connections\Connection as RedisConnection;
use Illuminate\Database\Connection as DBConnection;

interface LaravelDBDriver extends RunningSpy
{
    public function getTraceId() : string;

    public function getRedis() : RedisConnection;

    public function getDB() : DBConnection;

}