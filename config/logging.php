<?php

use Monolog\Handler\NullHandler;
use Monolog\Handler\StreamHandler;
use Monolog\Handler\SyslogUdpHandler;
use Monolog\Processor\PsrLogMessageProcessor;

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
    | Deprecations Log Channel
    |--------------------------------------------------------------------------
    |
    | This option controls the log channel that should be used to log warnings
    | regarding deprecated PHP and library features. This allows you to get
    | your application ready for upcoming major versions of dependencies.
    |
    */

    'deprecations' => [
        'channel' => env('LOG_DEPRECATIONS_CHANNEL', 'null'),
        'trace' => false,
    ],

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
            'channels' => ['daily'],
            'ignore_exceptions' => false,
        ],

        'single' => [
            'driver' => 'single',
            'path' => storage_path('logs/laravel.log'),
            'level' => env('LOG_LEVEL', 'debug'),
            'replace_placeholders' => true,
        ],

        'daily' => [
            'driver' => 'daily',
            'path' => storage_path('logs/laravel.log'),
            'level' => env('LOG_LEVEL', 'debug'),
            'replace_placeholders' => true,
            'days' => 90,
        ],

        'proxylookup' => [
            'driver' => 'daily',
            'path' => storage_path('logs/proxylookup/laravel.log'),
            'level' => env('LOG_LEVEL', 'debug'),
            'replace_placeholders' => true,
            'days' => 90,
        ],


        'pl' => [
            'driver' => 'daily',
            'path' => storage_path('logs/pl/laravel.log'),
            'level' => env('LOG_LEVEL', 'debug'),
            'replace_placeholders' => true,
            'days' => 90,
        ],

        'pl-expired' => [
            'driver' => 'daily',
            'path' => storage_path('logs/pl-expired/laravel.log'),
            'level' => env('LOG_LEVEL', 'debug'),
            'replace_placeholders' => true,
            'days' => 90,
        ],

        'pl-removed' => [
            'driver' => 'daily',
            'path' => storage_path('logs/pl-removed/laravel.log'),
            'level' => env('LOG_LEVEL', 'debug'),
            'replace_placeholders' => true,
            'days' => 90,
        ],
        
        'health' => [
            'driver' => 'daily',
            'path' => storage_path('logs/health/laravel.log'),
            'level' => env('LOG_LEVEL', 'debug'),
            // 'days' => 14,
            'replace_placeholders' => true,
            'days' => 90,
        ],

        'health_cop' => [
            'driver' => 'daily',
            'path' => storage_path('logs/health/cop/laravel.log'),
            'level' => env('LOG_LEVEL', 'debug'),
            //'days' => 14,
            'replace_placeholders' => true,
            'days' => 90,
        ],

        'health_vop' => [
            'driver' => 'daily',
            'path' => storage_path('logs/health/vop/laravel.log'),
            'level' => env('LOG_LEVEL', 'debug'),
            //'days' => 14,
            'replace_placeholders' => true,
            'days' => 90,
        ],

        'health_pl' => [
            'driver' => 'daily',
            'path' => storage_path('logs/health/pl/laravel.log'),
            'level' => env('LOG_LEVEL', 'debug'),
            //'days' => 14,
            'replace_placeholders' => true,
            'days' => 90,
        ],

        'hbp' => [
            'driver' => 'daily',
            'path' => storage_path('logs/hbp/laravel.log'),
            'level' => env('LOG_LEVEL', 'debug'),
            'replace_placeholders' => true,
            'days' => 90,
        ],

        'carga' => [
            'driver' => 'daily',
            'path' => storage_path('logs/carga/laravel.log'),
            'level' => env('LOG_LEVEL', 'debug'),
            'replace_placeholders' => true,
            'days' => 90,
        ],
        
        'jwt' => [
            'driver' => 'daily',
            'path' => storage_path('logs/jwt/laravel.log'),
            'level' => env('LOG_LEVEL', 'debug'),
            'replace_placeholders' => true,
            'days' => 90,
        ],

  
        'domain' => [
            'driver' => 'daily',
            'path' => storage_path('logs/domain/laravel.log'),
            'level' => env('LOG_LEVEL', 'debug'),
            'replace_placeholders' => true,
            'days' => 90,
        ],

        'cop' => [
            'driver' => 'daily',
            'path' => storage_path('logs/cop/laravel.log'),
            'level' => env('LOG_LEVEL', 'debug'),
            'replace_placeholders' => true,
            'days' => 90,
        ],

        'cops' => [
            'driver' => 'daily',
            'path' => storage_path('logs/cops/laravel.log'),
            'level' => env('LOG_LEVEL', 'debug'),
            'replace_placeholders' => true,
            'days' => 90,
        ],

        'copb' => [
            'driver' => 'daily',
            'path' => storage_path('logs/copb/laravel.log'),
            'level' => env('LOG_LEVEL', 'debug'),
            'replace_placeholders' => true,
            'days' => 90,
        ],

        'bancoportugal' => [
            'driver' => 'daily',
            'path' => storage_path('logs/bancoportugal/laravel.log'),
            'level' => env('LOG_LEVEL', 'debug'),
            'replace_placeholders' => true,
            'days' => 90,
        ],

        'testes' => [
            'driver' => 'daily',
            'path' => storage_path('logs/testes/laravel.log'),
            'level' => env('LOG_LEVEL', 'debug'),
            'replace_placeholders' => true,
            'days' => 90,
        ],

        'exceptions' => [
            'driver' => 'daily',
            'path' => storage_path('logs/exceptions/laravel.log'),
            'level' => env('LOG_LEVEL', 'debug'),
            'replace_placeholders' => true,
            'days' => 90,
        ],

        'commands' => [
            'driver' => 'daily',
            'path' => storage_path('logs/commands/laravel.log'),
            'level' => env('LOG_LEVEL', 'debug'),
            'replace_placeholders' => true,
            'days' => 90,
        ],

        'monitoring' => [
            'driver' => 'daily',
            'path' => storage_path('logs/monitoring/laravel.log'),
            'level' => env('LOG_LEVEL', 'debug'),
            'replace_placeholders' => true,
            'days' => 90,
        ],
        
        'uploads' => [
            'driver' => 'daily',
            'path' => storage_path('logs/uploads/uploads.log'),
            'level' => env('LOG_LEVEL', 'debug'),
            'replace_placeholders' => true,
            'days' => 90,
        ],

        'slack' => [
            'driver' => 'slack',
            'url' => env('LOG_SLACK_WEBHOOK_URL'),
            'username' => 'Laravel Log',
            'emoji' => ':boom:',
            'level' => env('LOG_LEVEL', 'critical'),
            'replace_placeholders' => true,
        ],

        'papertrail' => [
            'driver' => 'monolog',
            'level' => env('LOG_LEVEL', 'debug'),
            'handler' => env('LOG_PAPERTRAIL_HANDLER', SyslogUdpHandler::class),
            'handler_with' => [
                'host' => env('PAPERTRAIL_URL'),
                'port' => env('PAPERTRAIL_PORT'),
                'connectionString' => 'tls://'.env('PAPERTRAIL_URL').':'.env('PAPERTRAIL_PORT'),
            ],
            'processors' => [PsrLogMessageProcessor::class],
        ],

        'stderr' => [
            'driver' => 'monolog',
            'level' => env('LOG_LEVEL', 'debug'),
            'handler' => StreamHandler::class,
            'formatter' => env('LOG_STDERR_FORMATTER'),
            'with' => [
                'stream' => 'php://stderr',
            ],
            'processors' => [PsrLogMessageProcessor::class],
        ],

        'syslog' => [
            'driver' => 'syslog',
            'level' => env('LOG_LEVEL', 'debug'),
            'facility' => LOG_USER,
            'replace_placeholders' => true,
        ],

        'errorlog' => [
            'driver' => 'errorlog',
            'level' => env('LOG_LEVEL', 'debug'),
            'replace_placeholders' => true,
        ],

        'null' => [
            'driver' => 'monolog',
            'handler' => NullHandler::class,
        ],

        'emergency' => [
            'path' => storage_path('logs/laravel.log'),
        ],
    ],

];
