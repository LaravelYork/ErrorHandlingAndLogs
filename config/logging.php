<?php

use Monolog\Handler\StreamHandler;
use Monolog\Handler\SyslogUdpHandler;

return [

    /*
    |--------------------------------------------------------------------------
    | Default Log Channel
    |--------------------------------------------------------------------------
    |
    | This option defines the default log channel that gets used when writing
    | messages to the logs. The name specified in this option should match
    | one of the channels defined in the "channels" configuration array.
    |
    */

    'default' => env('LOG_CHANNEL', 'stack'),

    /*
    |--------------------------------------------------------------------------
    | Log Channels
    |--------------------------------------------------------------------------
    |
    | Here you may configure the log channels for your application. Out of
    | the box, Laravel uses the Monolog PHP logging library. This gives
    | you a variety of powerful log handlers / formatters to utilize.
    |
    | Available Drivers: "single", "daily", "slack", "syslog",
    |                    "errorlog", "monolog",
    |                    "custom", "stack"
    |
    */

    'channels' => [
        'stack' => [
            'driver' => 'stack',
            'channels' => ['daily','slack'],

            //customSlack
           // 'channels' => ['db'],
        ],

        'daily' => [
            'driver' => 'daily',
            'path' => storage_path('logs'. DIRECTORY_SEPARATOR . php_sapi_name() . '-'. getenv('APP_NAME').'-'.getenv('APP_ENV').'.log'),
            'level' => 'debug',
            'days' => 7,
        ],

        'slack' => [
            'driver' => 'slack',
            'url' => env('LOG_SLACK_WEBHOOK_URL'),
            'username' => 'Laravel Log',
            'emoji' => ':boom:',
            'level' => 'error',
        ],

        'papertrail' => [
            'driver' => 'monolog',
            'level' => 'debug',
            'handler' => SyslogUdpHandler::class,
            'handler_with' => [
                'host' => env('PAPERTRAIL_URL'),
                'port' => env('PAPERTRAIL_PORT'),
            ],
        ],

        'stderr' => [
            'driver' => 'monolog',
            'handler' => StreamHandler::class,
            'formatter' => env('LOG_STDERR_FORMATTER'),
            'with' => [
                'stream' => 'php://stderr',
            ],
        ],

        'syslog' => [
            'driver' => 'syslog',
            'level' => 'debug',
        ],

        'errorlog' => [
            'driver' => 'errorlog',
            'level' => 'debug',
        ],

        'db' => [
            'driver' => 'custom',
            'via' => \App\Logging\CustomDBLogger::class,
            'level' => 'debug',
           // 'level' => env('LOG_DB_LEVEL', Monolog\Logger::NOTICE)
        ],

        'customSlack' => [
            'driver' => 'custom',
            'via' => \App\Logging\CustomSlackLogger::class,
            'level' => 'debug',
            //'level' => env('LOG_SLACK_LEVEL', Monolog\Logger::ERROR)
        ]
    ],

    'slack_api_token' => env('SLACK_TOKEN', ''),
    'slack_api_channel' => env('SLACK_CHANNEL', ''),

];
