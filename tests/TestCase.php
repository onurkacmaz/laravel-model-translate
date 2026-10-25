<?php

namespace Onurkacmaz\LaravelModelTranslate\Tests;

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Onurkacmaz\LaravelModelTranslate\LaravelModelTranslateServiceProvider;
use Orchestra\Testbench\TestCase as Orchestra;

abstract class TestCase extends Orchestra
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->app->setLocale('tr');

        (require __DIR__.'/../database/migrations/create_translations_table.php.stub')->up();

        Schema::create('posts', function (Blueprint $table) {
            $table->id();
            $table->string('title')->nullable();
            $table->text('content')->nullable();
            $table->integer('views')->default(0);
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('uuid_posts', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('title')->nullable();
            $table->timestamps();
        });
    }

    protected function getPackageProviders($app): array
    {
        return [LaravelModelTranslateServiceProvider::class];
    }

    protected function defineEnvironment($app): void
    {
        $app['config']->set('database.default', 'testing');
        $app['config']->set('laravel-model-translate.supported_locales', ['tr', 'en', 'de']);
    }

    protected function useDriver(string $name): void
    {
        $this->app['config']->set('laravel-model-translate.driver', $name);
        $this->app->forgetInstance(\Onurkacmaz\LaravelModelTranslate\Drivers\DriverInterface::class);
    }
}
