<?php

namespace Onurkacmaz\LaravelModelTranslate;

use Illuminate\Support\Facades\Facade;

/**
 * @see \Onurkacmaz\LaravelModelTranslate\Skeleton\SkeletonClass
 */
class LaravelModelTranslateFacade extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return 'laravel-model-translate';
    }
}
