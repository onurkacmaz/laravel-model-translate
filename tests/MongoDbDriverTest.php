<?php

namespace Onurkacmaz\LaravelModelTranslate\Tests;

use MongoDB\Client;
use Throwable;

class MongoDbDriverTest extends DriverTestCase
{
    protected function driverName(): string
    {
        return 'mongodb';
    }

    protected function setUp(): void
    {
        if (! class_exists(Client::class)) {
            $this->markTestSkipped('The mongodb/mongodb package is not installed.');
        }

        parent::setUp();

        try {
            (new Client($this->app['config']->get('laravel-model-translate.drivers.mongodb.dsn'), [], ['serverSelectionTimeoutMS' => 1000]))
                ->selectDatabase($this->app['config']->get('laravel-model-translate.drivers.mongodb.database'))
                ->drop();
        } catch (Throwable $e) {
            $this->markTestSkipped('MongoDB is not available: '.$e->getMessage());
        }
    }

    protected function defineEnvironment($app): void
    {
        parent::defineEnvironment($app);

        $app['config']->set('laravel-model-translate.drivers.mongodb.dsn', env('TRANSLATION_MONGO_DSN', 'mongodb://127.0.0.1:27017'));
        $app['config']->set('laravel-model-translate.drivers.mongodb.database', 'laravel_model_translate_test');
    }
}
