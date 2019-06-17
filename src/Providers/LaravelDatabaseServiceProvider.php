<?php

/**
 * Class LaravelDatabaseServiceProvider
 * @package Commune\Chatbot\Laravel\Providers
 * @author BrightRed
 */

namespace Commune\Chatbot\Laravel\Providers;

use Commune\Container\ContainerContract;
use Commune\Container\IlluminateAdapter;
use Illuminate\Contracts\Container\Container;
use Illuminate\Database\DatabaseManager;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Contracts\Queue\EntityResolver;
use Illuminate\Database\Connectors\ConnectionFactory;
use Illuminate\Database\Eloquent\QueueEntityResolver;
use Illuminate\Database\Eloquent\Factory as EloquentFactory;
use Commune\Chatbot\Blueprint\ServiceProvider;
use Commune\Chatbot\Blueprint\Conversation\ConversationContainer;


class LaravelDatabaseServiceProvider extends ServiceProvider
{
    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        $this->registerConnectionServices();

        $this->registerEloquentFactory();

        $this->registerQueueableEntityResolver();
    }

    /**
     * Register the primary database bindings.
     *
     * @return void
     */
    protected function registerConnectionServices()
    {
        // The connection factory is used to create the actual connection instances on
        // the database. We will inject the factory into the manager so that it may
        // make the connections while they are actually needed and not of before.
        $this->app->singleton(ConnectionFactory::class, function ($app) {
            /**
             * @var ContainerContract $app
             * @var IlluminateAdapter $illuminateContainer
             * @var Container
             */
            $illuminateContainer = $app[IlluminateAdapter::class];
            $container = $illuminateContainer->getIlluminateContainer();
            return new ConnectionFactory($container);
        });

        // The database manager is used to resolve various connections, since multiple
        // connections might be managed. It also implements the connection resolver
        // interface which may be used by other components requiring connections.
        $this->app->singleton('db', function ($app) {
            /**
             * @var ContainerContract $app
             * @var IlluminateAdapter $illuminateContainer
             * @var Container
             */
            $illuminateContainer = $app[IlluminateAdapter::class];
            $container = $illuminateContainer->getIlluminateContainer();

            return new DatabaseManager($container, $container['db.factory']);
        });

        $this->app->bind('db.connection', function ($app) {
            return $app['db']->connection();
        });
    }

    /**
     * Register the queueable entity resolver implementation.
     *
     * @return void
     */
    protected function registerQueueableEntityResolver()
    {
        $this->app->singleton(EntityResolver::class, function () {
            return new QueueEntityResolver;
        });
    }

}