<?php

/**
 * Class ChatbotServiceProvider
 * @package Commune\Chatbot\Laravel
 * @author BrightRed
 */

namespace Commune\Chatbot\Laravel;


use Commune\Chatbot\Laravel\Commands\Tinker;
use Illuminate\Support\ServiceProvider;

class ChatbotServiceProvider extends ServiceProvider
{

    public function boot()
    {
        $this->bootCommands();
        $this->publishConfigs();

    }

    public function register()
    {
    }


    protected function bootCommands() : void
    {
        if ($this->app->runningInConsole()) {
            $this->commands([
                Tinker::class,
            ]);
        }

    }

    protected function publishConfigs() : void
    {
        $this->publishes([
            __DIR__. '/../configs/chatbot.php' => config_path('chatbot.php'),
        ]);
    }
}