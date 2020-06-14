<?php

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
            'channels' => ['daily'],
            'ignore_exceptions' => false,
        ],

        'daily' => [
            'driver' => 'daily',
            'path' => storage_path('logs/code6.log'),
            'level' => 'info',
            'days' => 7,
        ],

        'code6:job-add' => [
            'driver' => 'daily',
            'path' => storage_path('logs/job-add.log'),
            'level' => 'info',
            'days' => 7,
        ],

        'code6:token-check' => [
            'driver' => 'daily',
            'path' => storage_path('logs/token-check.log'),
            'level' => 'info',
            'days' => 7,
        ],

        'code6:job-run' => [
            'driver' => 'daily',
            'path' => storage_path('logs/job-run.log'),
            'level' => 'info',
            'days' => 7,
        ],
    ],

];
