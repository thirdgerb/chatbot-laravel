<?php

/**
 * Class ChatbotServiceProvider
 * @package Commune\Chatbot\Laravel
 * @author BrightRed
 */

namespace Commune\Chatbot\Laravel;


use Commune\Chatbot\Laravel\Commands\TcpServer;
use Commune\Chatbot\Laravel\Commands\Tinker;
use Illuminate\Support\ServiceProvider;
use Commune\Chatbot\App\Constants;
use Monolog\Handler\RotatingFileHandler;
use Monolog\Logger;

class ChatbotServiceProvider extends ServiceProvider
{
    const MESSAGES_LOG = 'log.messages';

    public function boot()
    {
        $this->bootCommands();
        $this->publishFiles();
        $this->loadMigrationsFrom(__DIR__.'/../migrations');
    }

    public function register()
    {
        $this->app->singleton(
            'log.messages',
            static::messagesLogConcrete()
        );
    }

    public static function messagesLogConcrete() : \Closure
    {
        return function(){
            $handler = new RotatingFileHandler(
                storage_path('logs/messages.log'),
                7
            );

            return new Logger('messages', [$handler]);
        };
    }


    protected function bootCommands() : void
    {
        if ($this->app->runningInConsole()) {
            $this->commands([
                TcpServer::class,
                Tinker::class,
            ]);
        }

    }

    protected function publishFiles() : void
    {
        $this->publishes([
            __DIR__. '/../configs/chatbot.php' => config_path('commune/chatbot.php'),
            Constants::TRANS_PATH => resource_path('lang/chatbot'),
        ]);
    }
}