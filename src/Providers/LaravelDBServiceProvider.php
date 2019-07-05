<?php

/**
 * Class LaravelDatabaseServiceProvider
 * @package Commune\Chatbot\Laravel\Providers
 * @author BrightRed
 */

namespace Commune\Chatbot\Laravel\Providers;

use Commune\Chatbot\Blueprint\Conversation\ConversationLogger;
use Commune\Chatbot\Config\ChatbotConfig;
use Commune\Chatbot\Contracts\CacheAdapter;
use Commune\Chatbot\Contracts\EventDispatcher;
use Commune\Chatbot\Laravel\Drivers\LaravelCacheAdapter;
use Commune\Chatbot\Laravel\Drivers\LaravelDBDriver;
use Commune\Chatbot\Laravel\Drivers\LaravelDBDriverImpl;
use Commune\Chatbot\Laravel\Drivers\LaravelSessionDriver;
use Commune\Chatbot\OOHost\Session\Driver as SessionDriver;
use Commune\Chatbot\Blueprint\ServiceProvider;


/**
 * 推荐在 conversation 里注册. 
 */
class LaravelDBServiceProvider extends ServiceProvider
{
    public function boot($app)
    {
    }

    public function register()
    {
        $this->app->singleton(
            LaravelDBDriver::class,
            LaravelDBDriverImpl::class
        );

        $this->app->singleton(
            CacheAdapter::class,
            LaravelCacheAdapter::class
        );

        $this->app->singleton(
            SessionDriver::class,
            function($app) {
                /**
                 * @var ChatbotConfig $config
                 * @var CacheAdapter $cache
                 */
                $config = $app[ChatbotConfig::class];
                $logger = $app[ConversationLogger::class];
                $dispatcher = $app[EventDispatcher::class];
                $db = $app[LaravelDBDriver::class];

                return new LaravelSessionDriver(
                    $db,
                    $config->host,
                    $logger,
                    $dispatcher
                );
            }
        );
    }


}