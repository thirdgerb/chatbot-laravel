<?php

/**
 * Class ChatbotServiceProvider
 * @package Commune\Chatbot\Laravel
 * @author BrightRed
 */

namespace Commune\Chatbot\Laravel;


use Commune\Chatbot\Laravel\Commands\Tinker;
use Illuminate\Support\ServiceProvider;
use Commune\Chatbot\App\Constants;

class ChatbotServiceProvider extends ServiceProvider
{

    public function boot()
    {
        $this->bootCommands();
        $this->publishFiles();
        $this->loadMigrationsFrom(__DIR__.'/../migrations');
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

    protected function publishFiles() : void
    {
        $this->publishes([
            __DIR__. '/../configs/chatbot.php' => config_path('chatbot.php'),
            Constants::TRANS_PATH => resource_path('lang/chatbot'),
        ]);
    }
}