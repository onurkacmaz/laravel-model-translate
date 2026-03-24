<?php

namespace Onurkacmaz\LaravelModelTranslate\Traits;

use Illuminate\Database\Eloquent\Model;
use Onurkacmaz\LaravelModelTranslate\LaravelModelTranslate;

trait Translatable
{
    public function getTranslatable(): array
    {
        return $this->translatable ?? [];
    }

    public static function bootTranslatable(): void
    {
        static::saving(function (Model $model) {
            if ($model->exists) {
                $model->runTranslationUpdate();
            }

            $isOriginal = $model->getAttribute('isOriginal');
            $model->offsetUnset('isOriginal');

            return $isOriginal;
        });

        static::saved(function (Model $model) {
            $model->runTranslationCreate();

            return false;
        });

        static::retrieved(function (Model $model) {
            $model->runTranslationLookup();
        });
    }

    private function runTranslationLookup(): void
    {
        LaravelModelTranslate::make()
            ->setColumns($this->getTranslatable())
            ->setModel($this)
            ->translate();
    }

    private function runTranslationCreate(): void
    {
        LaravelModelTranslate::make()
            ->setColumns($this->getTranslatable())
            ->setModel($this)
            ->create();
    }

    private function runTranslationUpdate(): void
    {
        LaravelModelTranslate::make()
            ->setColumns($this->getTranslatable())
            ->setModel($this)
            ->update();
    }
}
