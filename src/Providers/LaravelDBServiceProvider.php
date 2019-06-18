<?php

/**
 * Class LaravelDatabaseServiceProvider
 * @package Commune\Chatbot\Laravel\Providers
 * @author BrightRed
 */

namespace Commune\Chatbot\Laravel\Providers;

use Commune\Chatbot\Config\ChatbotConfig;
use Commune\Chatbot\Contracts\CacheAdapter;
use Commune\Chatbot\Contracts\EventDispatcher;
use Commune\Chatbot\Laravel\Drivers\LaravelCacheAdapter;
use Commune\Chatbot\Laravel\Drivers\LaravelSessionDriver;
use Commune\Chatbot\OOHost\Session\Driver as SessionDriver;
use Commune\Chatbot\Blueprint\ServiceProvider;
use Psr\Log\LoggerInterface;


class LaravelDBServiceProvider extends ServiceProvider
{
    public function boot($app)
    {
    }

    public function register()
    {
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
                $cache = $app[CacheAdapter::class];
                $logger = $app[LoggerInterface::class];
                $dispatcher = $app[EventDispatcher::class];

                return new LaravelSessionDriver(
                    $cache,
                    $config->host,
                    $logger,
                    $dispatcher
                );
            }
        );
    }


}