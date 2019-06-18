<?php

use Commune\Chatbot\OOHost\Session\Scope;

return [
    'debug' => true,
    'configBindings' => [
        \Commune\Chatbot\App\Platform\ConsoleConfig::class,
    ],
    'components' => [
        \Commune\Demo\App\DemoOption::class,
        \Commune\Chatbot\App\Components\ConfigurableComponent::class,
    ],
    'reactorProviders' => [
        \Commune\Chatbot\App\Drivers\Demo\ExpHandlerServiceProvider::class,
    ],
    'conversationProviders' => [
        \Commune\Chatbot\Laravel\Providers\LaravelDBServiceProvider::class,
    ],
    'chatbotPipes' =>
        [
            'onUserMessage' => [
                \Commune\Chatbot\App\ChatPipe\MessengerPipe::class,
                \Commune\Chatbot\App\ChatPipe\ChattingPipe::class,
                \Commune\Chatbot\OOHost\OOHostPipe::class,
            ],
        ],
    'translation' =>
        [
            'loader' => 'php',
            'resourcesPath' => resource_path('/lang/chatbot'),
            'defaultLocale' => 'zh',
            'cacheDir' => NULL,
        ],
    'logger' =>
        [
            'name' => 'chatbot',
            'path' => storage_path('/logs/chatbot.log'),
            'days' => 0,
            'level' => 'debug',
            'bubble' => true,
            'permission' => NULL,
            'locking' => false,
        ],
    'defaultMessages' =>
        [
            'platformNotAvailable' => 'system.platformNotAvailable',
            'chatIsTooBusy' => 'system.chatIsTooBusy',
            'systemError' => 'system.systemError',
            'farewell' => 'dialog.farewell',
            'messageMissMatched' => 'dialog.missMatched',
        ],
    'eventRegister' =>[

    ],

    'host' => [
        'rootContextName' => \Commune\Demo\App\Contexts\Welcome::class,
        'maxBreakpointHistory' => 10,
        'maxRedirectTimes' => 20,
        'sessionExpireSeconds' => 3600,
        'sessionCacheSeconds' => 60,
        'sessionPipes' => [
            \Commune\Chatbot\App\Commands\UserCommandsPipe::class,
            \Commune\Chatbot\App\Commands\AnalyserPipe::class,
            \Commune\Chatbot\App\SessionPipe\MarkedIntentPipe::class,
            \Commune\Chatbot\App\SessionPipe\NavigationPipe::class,
        ],
        'navigatorIntents' => [
            \Commune\Demo\App\Intents\QuitInt::class,
        ],
        'memories' => [
            [
                'name' => 'sandbox',
                'desc' => 'description',
                'scopes' => [Scope::SESSION_ID],
                'entities' => [
                    'test'
                ]
            ],
        ],
    ],

];