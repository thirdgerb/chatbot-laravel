<?php

/**
 * Class Console
 * @package Commune\Chatbot\Laravel\Commands
 */

namespace Commune\Chatbot\Laravel\Console;


use Commune\Chatbot\Contracts\ChatbotApp;
use Commune\Chatbot\Contracts\ChatbotKernel;
use Commune\Chatbot\Contracts\ServerDriver;
use Commune\Chatbot\Laravel\Impl\ConsoleDriver;
use Illuminate\Console\Command;

class ConsoleTest extends Command
{
    protected $signature = 'commune:chatbot 
    {userId : 模拟的用户Id. } 
    {userName : 模拟的用户名. }
    ';

    protected $description = 'commune chatbot console for test';

    public function handle(ChatbotApp $chatbot)
    {
        $this->info('hello');
        $userId = $this->argument('userId');
        $userName = $this->argument('userName');
        $serverDriver = new ConsoleDriver($this, $userId, $userName);

        $chatbot->getContainer()->instance(ServerDriver::class, $serverDriver);

        /**
         * @var ChatbotKernel $kernel;
         */
        $kernel = $chatbot->make(ChatbotKernel::class);

        while (true) {
            $input = $this->ask('input > ');
            $kernel->handle($input);
        }
    }

}