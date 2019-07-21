<?php

/**
 * Class Tinker
 * @package Commune\Chatbot\Laravel\Commands
 * @author BrightRed
 */

namespace Commune\Chatbot\Laravel\Commands;


use Commune\Chatbot\Blueprint\Application as Chatbot;
use Commune\Chatbot\Contracts\ChatServer;
use Commune\Chatbot\Framework\ChatApp;
use Commune\Container\IlluminateAdapter;
use Illuminate\Console\Command;
use Commune\Chatbot\App\Platform\SwooleConsole\SwooleConsoleServer;

class TcpServer extends Command
{

    protected $signature = 'commune:tcp';

    protected $description = 'start commune chatbot server on tcp by swoole';

    /**
     * @var Chatbot
     */
    protected $chatApp;

    public function __construct()
    {
        parent::__construct();
    }

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
            ->singleton(
                ChatServer::class,
                SwooleConsoleServer::class
            );
    }

}