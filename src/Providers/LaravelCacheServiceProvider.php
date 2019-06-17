<?php

/**
 * Class LaravelCacheServiceProvider
 * @package Commune\Chatbot\Laravel\Providers
 * @author BrightRed
 */

namespace Commune\Chatbot\Laravel\Providers;

use Commune\Chatbot\Blueprint\ServiceProvider;
use Illuminate\Cache\CacheManager;
use Illuminate\Cache\MemcachedConnector;

class LaravelCacheServiceProvider extends ServiceProvider
{

    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        $this->app->singleton('cache', function ($app) {
            return new CacheManager($app);
        });

        $this->app->singleton('cache.store', function ($app) {
            return $app['cache']->driver();
        });

        $this->app->singleton('memcached.connector', function () {
            return new MemcachedConnector();
        });
    }


}