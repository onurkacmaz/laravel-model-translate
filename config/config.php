<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Supported Locales
    |--------------------------------------------------------------------------
    |
    | The locales a translatable model can be translated into. When a model is
    | saved for the first time, a translation is created for every locale in
    | this list except the current application locale, which is kept in the
    | model's own table.
    |
    */

    'supported_locales' => [
        'tr',
        'en',
        'de',
        'fr',
    ],

    /*
    |--------------------------------------------------------------------------
    | Default Driver
    |--------------------------------------------------------------------------
    |
    | The storage used for translations. Supported: "database", "redis",
    | "mongodb". Any class implementing the DriverInterface may be registered
    | under the "drivers" key below.
    |
    */

    'driver' => env('TRANSLATION_DRIVER', 'database'),

    'drivers' => [

        'database' => [
            'driver' => \Onurkacmaz\LaravelModelTranslate\Drivers\Database::class,
            // null uses the default database connection.
            'connection' => env('TRANSLATION_DB_CONNECTION'),
            'table' => env('TRANSLATION_DB_TABLE', 'translations'),
        ],

        'redis' => [
            'driver' => \Onurkacmaz\LaravelModelTranslate\Drivers\Redis::class,
            // A connection name defined in config/database.php "redis" section.
            'connection' => env('TRANSLATION_REDIS_CONNECTION', 'default'),
            'prefix' => env('TRANSLATION_REDIS_PREFIX', 'translations'),
        ],

        'mongodb' => [
            'driver' => \Onurkacmaz\LaravelModelTranslate\Drivers\MongoDb::class,
            'dsn' => env('TRANSLATION_MONGO_DSN', 'mongodb://localhost:27017'),
            'database' => env('TRANSLATION_MONGO_DB', 'translations'),
            'collection' => env('TRANSLATION_MONGO_COLLECTION', 'translations'),
        ],

    ],

];
