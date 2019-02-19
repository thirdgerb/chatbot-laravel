<?php

use Commune\Chatbot\Command\Commands;
use Commune\Chatbot\Framework\Chat\ChatPipe;
use Commune\Chatbot\Framework\HostPipe;
use Commune\Chatbot\Framework\Bootstrap\PreloadContextConfig;
use Commune\Chatbot\Command\AnalyzerPipe;
use Commune\Chatbot\Command\UserCommandPipe;
use Commune\Chatbot\Laravel\ContextCfg;


return [
    'runtime' => [
        'direct_max_ticks' => 30,
        'bootstrappers' => [
            PreloadContextConfig::class,
        ],
        'pipes' => [
            ChatPipe::class,
            AnalyzerPipe::class,
            UserCommandPipe::class,
            HostPipe::class
        ],
        'analyzer_mark' => '#',
        'analyzers' => [
            Commands\Locate::class,
            Commands\ShowContext::class,
            Commands\History::class,
            Commands\Scoping::class,
        ],
        'command_mark' => '/',
        'commands' => [
            Commands\Quit::class,
            Commands\WhoAmI::class,
            Commands\Where::class,
            Commands\Backward::class,
            Commands\Forward::class,
            Commands\Cancel::class,
            Commands\Repeat::class,
        ],
    ],

    'contexts' => [
        'root' => ContextCfg\Root::class,
        'preload' => [
            ContextCfg\Root::class,
            ContextCfg\Test::class
        ]
    ],

    'supervisors' => [
        'test_laravel',
    ],

    'messages' => [
        'miss_match_message' => 'miss match',
        'exceptions' => [
            0 => 'unexpected exception occur',
        ],
        'ask_intent_argument' => '请输入{key} ({desc})'
    ],

    'database' => [
        'model' => [
            'driver' => 'mysql',
        ],
        'redis' => [
            'driver' => 'cache',
            'session_expire' => 3600,
            'chat_locker_expire' => 2,
        ],
    ],
];
