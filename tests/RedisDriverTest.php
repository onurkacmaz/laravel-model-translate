<?php

namespace Onurkacmaz\LaravelModelTranslate\Tests;

use Illuminate\Support\Facades\Redis;
use Throwable;

class RedisDriverTest extends DriverTestCase
{
    protected function driverName(): string
    {
        return 'redis';
    }

    protected function setUp(): void
    {
        parent::setUp();

        try {
            Redis::connection()->flushdb();
        } catch (Throwable $e) {
            $this->markTestSkipped('Redis is not available: '.$e->getMessage());
        }
    }

    protected function defineEnvironment($app): void
    {
        parent::defineEnvironment($app);

        $app['config']->set('database.redis.client', extension_loaded('redis') ? 'phpredis' : 'predis');
        $app['config']->set('database.redis.options.prefix', 'laravel_model_translate_test:');
        $app['config']->set('database.redis.default.database', env('REDIS_DB', 15));
    }

    public function test_translations_are_stored_in_one_hash_per_locale(): void
    {
        $post = Fixtures\Post::create(['title' => 'Merhaba']);

        $this->assertSame(
            ['title' => '"Merhaba"'],
            Redis::connection()->hgetall('translations:'.Fixtures\Post::class.':'.$post->id.':en'),
        );
    }
}
