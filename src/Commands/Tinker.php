<?php

/**
 * Class Tinker
 * @package Commune\Chatbot\Laravel\Commands
 */

namespace Commune\Chatbot\Laravel\Commands;


use Commune\Chatbot\Contracts\ChatServer;
use Commune\Chatbot\Framework\ChatApp as Chatbot;
use Commune\Chatbot\Framework\ChatApp;
use Commune\Chatbot\Laravel\Commands\Tinker\Server;
use Commune\Container\IlluminateAdapter;
use Illuminate\Console\Command;

class Tinker extends Command
{
    protected $signature = 'commune:tinker';

    protected $description = 'start commune chatbot on laravel command';

    /**
     * @var Chatbot
     */
    protected $chatApp;



    public function handle()
    {
        $this->bootstrap();
        $this->chatApp->getServer()->run();
    }

    protected function bootstrap() : void
    {
        $app = $this->getLaravel();
        $config = $app['config']['commune']['chatbot'];

        $this->chatApp = new ChatApp($config, new IlluminateAdapter($app));
        $this->chatApp
            ->getProcessContainer()
            ->instance(
                ChatServer::class,
                new Server($this, $this->chatApp)
            );
    }


}