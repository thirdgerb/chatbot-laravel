<?php

/**
 * Class ChatbotApp
 * @package Commune\Chatbot\Laravel\Impl
 */

namespace Commune\Chatbot\Laravel\Impl;

use Commune\Chatbot\Contracts\ChatbotApp as Contract;
use Commune\Chatbot\Framework\Character\User;
use Commune\Chatbot\Framework\Context\Context;
use Commune\Chatbot\Framework\Intent\Intent;
use Illuminate\Contracts\Container\Container;
use Illuminate\Contracts\Foundation\Application;
use Commune\Chatbot\Contracts\ServerDriver;
use Commune\Chatbot\Contracts\ExceptionHandler;
use Commune\Chatbot\Framework\Routing\Router;
use Commune\Chatbot\Framework\Routing\IntentRoute;
use Illuminate\Support\Arr;

class ChatbotAppImpl implements Contract
{

    /*----- config.database -----*/

    const SUPERVISORS = 'supervisors';
    const MODEL_DRIVER = 'database.model.driver';
    const REDIS_DRIVER = 'database.redis.driver';

    const REDIS_SESSION_EXPIRE = 'database.redis.session_expire';
    const REDIS_CHAT_LOCKER_EXPIRE = 'database.redis.chat_locker_expire';
    
    
    protected $app;

    protected $config;

    public function __construct(Application $app)
    {
        $this->app = $app;
        $this->config = $app['config']['chatbot'];
    }

    public function make($abstract, array $parameters = [])
    {
        return $this->app->make($abstract, $parameters);
    }

    public function getChatbotConfig(): array
    {
        return $this->config;
    }

    public function getServerDriver(): ServerDriver
    {
        return $this->make(ServerDriver::class);
    }

    public function getExceptionHandler(): ExceptionHandler
    {
        return $this->make(ExceptionHandler::class);
    }

    public function getIntentDefaultRoute(Router $router): IntentRoute
    {
        if (!isset($this->defIntentRoute)) {
            $this->defIntentRoute = new IntentRoute($this, $router, static::class);
            $this->defIntentRoute
                ->action()
                ->call(function (Context $context, Intent $intent) {
                    $context->error('miss match intent : ' . $intent);
                })
                ->redirect()
                ->home();
        }

        return $this->defIntentRoute;
    }

    public function get($id)
    {
        return $this->app->get($id);
    }

    public function has($id)
    {
        return $this->app->has($id);
    }

    public function getContainer(): Container
    {
        return $this->app;
    }

    public function isSupervisor(User $sender): bool
    {
        $supervisors = $this->getConfig(self::SUPERVISORS);
        return in_array($sender->getId(), $supervisors);
    }

    public function getConfig(string $configConstantName, $default = null)
    {
        return Arr::get($this->config, $configConstantName, $default);
    }


}