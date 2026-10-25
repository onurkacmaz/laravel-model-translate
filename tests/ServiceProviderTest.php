<?php

namespace Onurkacmaz\LaravelModelTranslate\Tests;

use Onurkacmaz\LaravelModelTranslate\Drivers\Database;
use Onurkacmaz\LaravelModelTranslate\Drivers\DriverInterface;
use Onurkacmaz\LaravelModelTranslate\Exceptions\InvalidDriverException;
use Onurkacmaz\LaravelModelTranslate\LaravelModelTranslate;
use stdClass;

class ServiceProviderTest extends TestCase
{
    public function test_the_configured_driver_is_resolved(): void
    {
        $this->assertInstanceOf(Database::class, $this->app->make(DriverInterface::class));
        $this->assertInstanceOf(Database::class, LaravelModelTranslate::make()->getDriver());
    }

    public function test_an_unknown_driver_throws(): void
    {
        $this->useDriver('unknown');

        $this->expectException(InvalidDriverException::class);

        $this->app->make(DriverInterface::class);
    }

    public function test_a_driver_must_implement_the_interface(): void
    {
        $this->app['config']->set('laravel-model-translate.drivers.invalid', ['driver' => stdClass::class]);
        $this->useDriver('invalid');

        $this->expectException(InvalidDriverException::class);

        $this->app->make(DriverInterface::class);
    }

    public function test_the_facade_resolves_a_fresh_instance_every_time(): void
    {
        $this->assertNotSame(
            $this->app->make('laravel-model-translate'),
            $this->app->make('laravel-model-translate'),
        );
    }
}
