<?php

return [
    // debug 模式会记录更多的日志.
    'debug' => env('COMMUNE_DEBUG', true),

    // 在这里可以预先绑定一些用 Option 类封装的配置.
    // 会将该配置预绑定到reactor容器上, 作为单例.
    // 有三种绑定方式:
    // 1. 只写类名, 默认使用 stub 里的配置.
    // 2. 类名 => 数组,  会用数组内的值覆盖 stub 的相关参数.
    // 3. 类名 => 子类名, 会用子类的实例来绑定父类类名.
    'configBindings' => [
        \Commune\Chatbot\App\Platform\ConsoleConfig::class => [
            'allowIPs' => [
                '127.0.0.1',
            ]
        ],
    ],

    // 预加载的组件. 使用方法类似 configBindings
    // 但component 不仅会预加载配置, 而且还能注册各种组件, 进行初始化等.
    'components' => [
        // 加载 demo 里的contexts和intents. 没啥用
        \Commune\Demo\App\DemoOption::class,
        \Commune\Chatbot\App\Components\PredefinedIntComponent::class,
    ],
    // 预定义的系统服务, 在这里可更改 service provider
    'baseServices' => \Commune\Chatbot\Config\Services\BaseServiceConfig::stub(),
    // 在reactor中注册的服务, 多个请求共享
    'reactorProviders' => [
        \Commune\Chatbot\App\Drivers\Demo\ExpHandlerServiceProvider::class,
    ],
    // 在conversation开始时才注册服务, 其单例在每个请求之间是隔离的.
    'conversationProviders' => [
        // 数据读写的组件, 用到了laravel DB 的redis 和 mysql
        \Commune\Chatbot\Laravel\Providers\LaravelDBServiceProvider::class,
    ],
    'chatbotPipes' => \Commune\Chatbot\Config\Pipes\ChatbotPipesConfig::stub(),
    'translation' => [
        'loader' => 'php',
        'resourcesPath' => resource_path('/lang/chatbot'),
        'defaultLocale' => 'zh',
        'cacheDir' => NULL,
    ],
    'logger' => [
        'name' => 'chatbot',
        'path' => storage_path('/logs/chatbot.log'),
        'days' => 7,
        'level' => 'debug',
        'bubble' => true,
        'permission' => NULL,
        'locking' => false,
    ],
    'defaultMessages' => \Commune\Chatbot\Config\Message\DefaultMessagesConfig::stub(),

    // 在对话系统中注册的事件机制.
    'eventRegister' =>[

    ],

    'host' => [
        'rootContextName' => \Commune\Demo\App\Contexts\TestCase::class,
    ] + \Commune\Chatbot\Config\Host\OOHostConfig::stub(),

];