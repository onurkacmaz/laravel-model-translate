<?php

namespace Onurkacmaz\LaravelModelTranslate;

use Illuminate\Support\Facades\Facade;

/**
 * @method static LaravelModelTranslate setModel(\Illuminate\Database\Eloquent\Model|null $model = null)
 * @method static LaravelModelTranslate setColumns(array $columns)
 * @method static LaravelModelTranslate setLocale(string|null $locale)
 * @method static LaravelModelTranslate setDriver(\Onurkacmaz\LaravelModelTranslate\Drivers\DriverInterface $driver)
 *
 * @see \Onurkacmaz\LaravelModelTranslate\LaravelModelTranslate
 */
class LaravelModelTranslateFacade extends Facade
{
    /**
     * A new, stateful instance is resolved for every call.
     */
    protected static $cached = false;

    protected static function getFacadeAccessor(): string
    {
        return 'laravel-model-translate';
    }
}
