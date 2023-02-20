<?php

namespace Onurkacmaz\LaravelModelTranslate;

use Illuminate\Support\ServiceProvider;

class LaravelModelTranslateServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the application services.
     */
    public function boot()
    {
        /*
         * Optional methods to load your package assets
         */
        $this->loadMigrationsFrom(__DIR__.'/../database/migrations');

        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__.'/../config/config.php' => config_path('laravel-model-translate.php'),
            ], 'config');

            if (!class_exists('CreateTranslationsTable')) {
                $this->publishes([
                    __DIR__.'/../database/migrations/create_translations_table.php.stub' => database_path('migrations/' . date('Y_m_d_His', time()) . '_create_translations_table.php'),
                ], 'migrations');
            }
        }
    }

    /**
     * Register the application services.
     */
    public function register()
    {
        // Automatically apply the package configuration
        $this->mergeConfigFrom(__DIR__.'/../config/config.php', 'laravel-model-translate');

        // Register the main class to use with the facade
        $this->app->singleton('laravel-model-translate', function () {
            return new LaravelModelTranslate;
        });
    }
}
