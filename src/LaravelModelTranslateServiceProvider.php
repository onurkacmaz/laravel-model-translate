<?php

namespace Onurkacmaz\LaravelModelTranslate;

use Illuminate\Contracts\Foundation\Application;
use Illuminate\Support\ServiceProvider;
use Onurkacmaz\LaravelModelTranslate\Drivers\DriverInterface;
use Onurkacmaz\LaravelModelTranslate\Exceptions\InvalidDriverException;

class LaravelModelTranslateServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__.'/../config/config.php', 'laravel-model-translate');

        $this->app->singleton(DriverInterface::class, function (Application $app) {
            $name = $app['config']->get('laravel-model-translate.driver');
            $config = $app['config']->get("laravel-model-translate.drivers.{$name}");

            if (! is_array($config) || empty($config['driver'])) {
                throw InvalidDriverException::notConfigured($name);
            }

            $driver = $app->make($config['driver'], ['config' => $config]);

            if (! $driver instanceof DriverInterface) {
                throw InvalidDriverException::invalidClass($config['driver']);
            }

            return $driver;
        });

        $this->app->bind('laravel-model-translate', fn () => new LaravelModelTranslate);
    }

    public function boot(): void
    {
        if (! $this->app->runningInConsole()) {
            return;
        }

        $this->publishes([
            __DIR__.'/../config/config.php' => config_path('laravel-model-translate.php'),
        ], ['laravel-model-translate-config', 'config']);

        $this->publishes([
            __DIR__.'/../database/migrations/create_translations_table.php.stub' => $this->migrationPath(),
        ], ['laravel-model-translate-migrations', 'migrations']);
    }

    /**
     * Reuse an already published migration instead of publishing a duplicate.
     */
    private function migrationPath(): string
    {
        $existing = glob(database_path('migrations/*_create_translations_table.php')) ?: [];

        return $existing[0] ?? database_path('migrations/'.date('Y_m_d_His').'_create_translations_table.php');
    }
}
