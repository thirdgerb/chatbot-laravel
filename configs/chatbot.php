<?php

use Commune\Chatbot\OOHost\Session\Scope;

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
    'chatbotPipes' => [
        'onUserMessage' => [
            \Commune\Chatbot\App\ChatPipe\MessengerPipe::class,
            \Commune\Chatbot\App\ChatPipe\ChattingPipe::class,
            \Commune\Chatbot\OOHost\OOHostPipe::class,
        ],
    ],
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
    'defaultMessages' => [
        'platformNotAvailable' => 'system.platformNotAvailable',
        'chatIsTooBusy' => 'system.chatIsTooBusy',
        'systemError' => 'system.systemError',
        'farewell' => 'dialog.farewell',
        'messageMissMatched' => 'dialog.missMatched',
    ],

    // 在对话系统中注册的事件机制.
    'eventRegister' =>[

    ],

    'host' => [
        // 默认的对话语境.
        'rootContextName' => \Commune\Demo\App\Contexts\TestCase::class,
        // 可回溯的对话断点数量.
        'maxBreakpointHistory' => 10,
        // 运行对话逻辑时, 语境变化的最大次数. 超过可能出现了重定向.
        'maxRedirectTimes' => 20,
        // session 过期时间.
        'sessionExpireSeconds' => 3600,
        // session 用到的数据缓存的时间.
        'sessionCacheSeconds' => 60,
        // session 中经历的管道.
        'sessionPipes' => [

            \Commune\Chatbot\App\SessionPipe\EventMsgPipe::class,
            // 用户可用的命令.
            Commune\Chatbot\App\Commands\UserCommandsPipe::class,
            // 系统可用的命令.
            Commune\Chatbot\App\Commands\AnalyserPipe::class,
            // 本组件, 可以使用 #intentName# 直接命中某个意图, 主要用于测试.
            \Commune\Chatbot\App\SessionPipe\MarkedIntentPipe::class,
            // 优先级最高, 用于导航的意图中间件.
            \Commune\Chatbot\App\SessionPipe\NavigationPipe::class,
            // 使用rasa 匹配意图的中间件.
            \Commune\Chatbot\App\Components\Rasa\RasaNLUPipe::class,
        ],
        // 这里的intent会对每一个请求进行强制的意图识别
        // 命中的话优先执行.
        'navigatorIntents' => [
        ],
        // session 预定义的记忆.
        'memories' => [
            [
                'name' => 'sandbox',
                'desc' => 'sandbox only used for test',
                'scopes' => [Scope::SESSION_ID],
                'entities' => [
                    'test'
                ]
            ],
        ],
    ],

];