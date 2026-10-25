<?php

namespace Onurkacmaz\LaravelModelTranslate\Drivers;

use Illuminate\Database\Eloquent\Model;

interface DriverInterface
{
    /**
     * Get the stored translations of the given columns for a locale.
     *
     * @param  array<int, string>  $columns
     * @return array<string, mixed> Translated values keyed by column name.
     */
    public function get(Model $model, array $columns, string $locale): array;

    /**
     * Seed the translations of a model for the given locales. Nothing is
     * written when the model already has a translation in any of them.
     *
     * @param  array<string, mixed>  $values  Initial values keyed by column name.
     * @param  array<int, string>  $locales
     */
    public function create(Model $model, array $values, array $locales): void;

    /**
     * Store the given values as the translation of a locale. Returns false,
     * without writing anything, when the model has no translation in that
     * locale (meaning the locale is the one kept in the model's own table).
     *
     * @param  array<string, mixed>  $values  Translated values keyed by column name.
     */
    public function update(Model $model, array $values, string $locale): bool;

    /**
     * Delete every translation of a model in the given locales.
     *
     * @param  array<int, string>  $locales
     */
    public function delete(Model $model, array $locales): void;
}
