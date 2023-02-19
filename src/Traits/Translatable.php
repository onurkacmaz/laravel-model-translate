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

    public static function boot(): void
    {
        parent::boot();

        self::saving(function (Model $model) {
            if ($model->exists) {
                $model->updateTranslation($model);
                return false;
            }

            return true;
        });

        self::saved(function (Model $model) {
            $model->createTranslation($model);
        });

        self::retrieved(function (Model $model) {
            $model->translate($model);
        });
    }

    private function translate(Model $model): void
    {
       LaravelModelTranslate::make()
           ->setColumns($this->getTranslatable())
           ->setModel($model)
           ->translate();
    }

    private function createTranslation(Model $model): void
    {
        LaravelModelTranslate::make()
            ->setColumns($this->getTranslatable())
            ->setModel($model)
            ->create();
    }

    private function updateTranslation(Model $model): void
    {
        LaravelModelTranslate::make()
            ->setColumns($this->getTranslatable())
            ->setModel($model)
            ->update();
    }
}