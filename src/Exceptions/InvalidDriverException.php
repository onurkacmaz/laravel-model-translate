<?php

namespace Onurkacmaz\LaravelModelTranslate\Exceptions;

use InvalidArgumentException;
use Onurkacmaz\LaravelModelTranslate\Drivers\DriverInterface;

class InvalidDriverException extends InvalidArgumentException
{
    public static function notConfigured(?string $name): self
    {
        return new self(sprintf('Translation driver [%s] is not configured in config/laravel-model-translate.php.', $name));
    }

    public static function invalidClass(string $class): self
    {
        return new self(sprintf('Translation driver [%s] must implement %s.', $class, DriverInterface::class));
    }
}
