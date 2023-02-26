<?php

/*
 * You can place your custom package configuration in here.
 */
return [
    'supported_locales' => [
        'tr',
        'en',
        'de',
        'fr'
    ],
    'driver' => env('TRANSLATION_DRIVER', 'database'),
    'drivers' => [
        'database' => [
            'driver' => \Onurkacmaz\LaravelModelTranslate\Drivers\Database::class,
        ],
        'mongodb' => [
            'driver' => \Onurkacmaz\LaravelModelTranslate\Drivers\MongoDb::class,
            'dsn' => env('TRANSLATION_MONGO_DSN', 'mongodb://localhost:27017'),
            'database' => env('TRANSLATION_MONGO_DB', 'translations'),
        ],
        'redis' => [
            'driver' => \Onurkacmaz\LaravelModelTranslate\Drivers\Redis::class,
            'host' => env('TRANSLATION_REDIS_HOST', '127.0.0.1'),
            'port' => env('TRANSLATION_REDIS_PORT', 6379),
            'user' => env('TRANSLATION_REDIS_USER'),
            'password' => env('TRANSLATION_REDIS_PASSWORD'),
            'database' => env('TRANSLATION_REDIS_DB', 0),
        ],
    ],
];