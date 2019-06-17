<?php

/**
 * Class TinkerServer
 * @package Commune\Chatbot\Laravel\Servers
 * @author BrightRed
 */

namespace Commune\Chatbot\Laravel\Servers;


use Commune\Chatbot\Blueprint\Conversation\Conversation;
use Commune\Chatbot\Contracts\ChatServer;
use Illuminate\Console\Command;

class TinkerServer implements ChatServer
{
    /**
     * @var Command
     */
    protected $command;

    public function run(): void
    {
    }

    public function sleep(int $millisecond): void
    {
        // TODO: Implement sleep() method.
    }

    public function fail(): void
    {
        // TODO: Implement fail() method.
    }

    public function closeClient(Conversation $conversation): void
    {
        // TODO: Implement closeClient() method.
    }


}