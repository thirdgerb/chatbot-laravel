<?php

/**
 * Class Server
 * @package Commune\Chatbot\Laravel\Commands\Tinker
 */

namespace Commune\Chatbot\Laravel\Commands\Tinker;


use Commune\Chatbot\App\Platform\ConsoleConfig;
use Commune\Chatbot\Blueprint\Application;
use Commune\Chatbot\Blueprint\Conversation\Conversation;
use Commune\Chatbot\Contracts\ChatServer;
use Commune\Chatbot\Framework\Messages\Events\ConnectionEvt;
use Illuminate\Console\Command;

class Server implements ChatServer
{
    /**
     * @var Command
     */
    protected $command;

    /**
     * @var Application
     */
    protected $chatApp;

    /**
     * Server constructor.
     * @param Command $command
     * @param Application $chatApp
     */
    public function __construct(Command $command, Application $chatApp)
    {
        $this->command = $command;
        $this->chatApp = $chatApp;
    }


    public function run(): void
    {
        $this->chatApp->bootWorker();
        $config = $this->chatApp
            ->getProcessContainer()[ConsoleConfig::class];

        $kernel = $this->chatApp->getKernel();
        $kernel->onUserMessage(
            new Request(
                $this->command,
                new ConnectionEvt(),
                $config
            )
        );
        while (true) {
            $answer = $this->command->ask('请输入');
            $request = new Request(
                $this->command,
                (string)$answer,
                $config
            );
            $kernel->onUserMessage($request);
        }
    }

    public function sleep(int $millisecond): void
    {
        usleep($millisecond * 1000);
    }

    public function fail(): void
    {
        $this->command->error('failed');
        exit(255);
    }

    public function closeClient(Conversation $conversation): void
    {
        exit(0);
    }


}