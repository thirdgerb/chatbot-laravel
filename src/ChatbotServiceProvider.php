<?php

/**
 * Class ChatbotServiceProvider
 * @package Commune\Chatbot\Laravel
 */

namespace Commune\Chatbot\Laravel;


use Commune\Chatbot\Contracts\ChatbotApp;
use Commune\Chatbot\Contracts\ChatbotKernel;
use Commune\Chatbot\Contracts\ChatDriver;
use Commune\Chatbot\Contracts\ExceptionHandler;
use Commune\Chatbot\Contracts\IdGenerator;
use Commune\Chatbot\Contracts\SessionDriver;
use Commune\Chatbot\Framework\Kernel;
use Commune\Chatbot\Laravel\Console\ConsoleTest;
use Commune\Chatbot\Laravel\Impl\ChatbotAppImpl;
use Commune\Chatbot\Laravel\Impl\ChatDriverImpl;
use Commune\Chatbot\Laravel\Impl\ExceptionHandlerImpl;
use Commune\Chatbot\Laravel\Impl\SessionDriverImpl;
use Commune\Chatbot\Laravel\Impl\IdGeneratorImpl;
use Illuminate\Support\ServiceProvider;

class ChatbotServiceProvider extends ServiceProvider
{
    /**
     * @var ChatbotApp
     */
    protected $chatbotApp;


    protected $contracts = [
        ChatbotKernel::class => Kernel::class,
        ChatDriver::class => ChatDriverImpl::class,
        ExceptionHandler::class => ExceptionHandlerImpl::class,
        IdGenerator::class => IdGeneratorImpl::class,
        SessionDriver::class => SessionDriverImpl::class
    ];


    public function boot()
    {
        $this->loadMigrationsFrom(__DIR__.'/../migrations');

        $this->publishes([
            __DIR__ .'/../config/chatbot.php' => config_path('chatbot.php'),
        ]);

        if ($this->app->runningInConsole()) {
            $this->commands([
                ConsoleTest::class
            ]);
        }
    }


    public function register()
    {
        $this->registerContracts();

        $this->registerConstants();
    }

    protected function registerContracts()
    {
        $this->chatbotApp = new ChatbotAppImpl($this->app);
        $this->app->instance(ChatbotApp::class, $this->chatbotApp);

        foreach ($this->contracts as $contract => $implement) {
            $this->app->singleton($contract, $implement);
        }

    }

    protected function registerConstants()
    {
        define('CHATBOT_REDIS_DRIVER', $this->chatbotApp->getConfig(ChatbotAppImpl::REDIS_DRIVER, 'cache'));

        define('CHATBOT_MODEL_DRIVER', $this->chatbotApp->getConfig(ChatbotAppImpl::MODEL_DRIVER, 'mysql'));

    }

}