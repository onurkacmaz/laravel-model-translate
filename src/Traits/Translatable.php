<?php

namespace Onurkacmaz\LaravelModelTranslate\Traits;

use Illuminate\Database\Eloquent\Model;
use Onurkacmaz\LaravelModelTranslate\LaravelModelTranslate;

/**
 * Translates the columns listed in a model's $translatable property (or
 * returned by its getTranslatable() method) into the application locale.
 *
 * The locale active when a model is first saved is kept in the model's own
 * table; every other supported locale is stored by the configured driver.
 */
trait Translatable
{
    public static function bootTranslatable(): void
    {
        static::retrieved(function (Model $model) {
            $model->modelTranslator()->translate();
        });

        static::saving(function (Model $model) {
            if (! $model->exists) {
                return;
            }

            $dirty = array_keys(array_intersect_key($model->getDirty(), array_flip($model->getTranslatable())));

            if ($dirty === []) {
                return;
            }

            // In a translated locale the changed values go to the translation
            // storage and are left out of the update of the model's own table.
            if ($model->modelTranslator()->setColumns($dirty)->update()) {
                $model->syncOriginalAttributes($dirty);
            }
        });

        static::saved(function (Model $model) {
            $model->modelTranslator()->create();
        });

        static::deleted(function (Model $model) {
            if (method_exists($model, 'isForceDeleting') && ! $model->isForceDeleting()) {
                return;
            }

            $model->modelTranslator()->delete();
        });
    }

    /**
     * The columns that are translated.
     *
     * @return array<int, string>
     */
    public function getTranslatable(): array
    {
        return property_exists($this, 'translatable') ? (array) $this->translatable : [];
    }

    /**
     * Fill the model with its translations in the given locale.
     */
    public function translateTo(string $locale): static
    {
        $columns = array_values(array_intersect($this->getTranslatable(), array_keys($this->getAttributes())));

        // Start from the values in the model's own table, so that switching to
        // the locale kept there undoes a previously applied translation.
        if ($this->exists && $columns !== []) {
            $stored = $this->newQueryWithoutScopes()->whereKey($this->getKey())->toBase()->first($columns);

            if ($stored !== null) {
                $this->setRawAttributes(array_merge($this->getAttributes(), (array) $stored));
                $this->syncOriginalAttributes($columns);
            }
        }

        $this->modelTranslator($locale)->translate();

        return $this;
    }

    /**
     * Get the stored translations of the given locale, keyed by column.
     *
     * @return array<string, mixed>
     */
    public function getTranslations(?string $locale = null): array
    {
        return $this->modelTranslator($locale)->getTranslations();
    }

    /**
     * Store the given values as the translation of a locale without touching
     * the model's attributes. Returns false, without writing anything, when
     * the model has no translation in that locale (it is the locale kept in
     * the model's own table).
     *
     * @param  array<string, mixed>  $values
     */
    public function saveTranslation(string $locale, array $values): bool
    {
        return $this->modelTranslator($locale)->update($values);
    }

    public function modelTranslator(?string $locale = null): LaravelModelTranslate
    {
        return LaravelModelTranslate::make($this, $this->getTranslatable(), $locale);
    }
}
