<?php

namespace Onurkacmaz\LaravelModelTranslate;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\App;
use LogicException;
use Onurkacmaz\LaravelModelTranslate\Drivers\DriverInterface;

class LaravelModelTranslate
{
    private array $columns = [];

    private ?Model $model = null;

    private ?string $locale = null;

    private ?DriverInterface $driver;

    public function __construct(?Model $model = null, array $columns = [], ?string $locale = null, ?DriverInterface $driver = null)
    {
        $this->setColumns($columns);
        $this->setModel($model);
        $this->locale = $locale;
        $this->driver = $driver;
    }

    public static function make(?Model $model = null, array $columns = [], ?string $locale = null): self
    {
        return new self($model, $columns, $locale);
    }

    public function getColumns(): array
    {
        return $this->columns;
    }

    public function setColumns(array $columns): self
    {
        $this->columns = array_values(array_unique($columns));

        return $this;
    }

    public function getModel(): ?Model
    {
        return $this->model;
    }

    public function setModel(?Model $model = null): self
    {
        $this->model = $model;

        return $this;
    }

    /**
     * The locale being worked on. Defaults to the current application locale.
     */
    public function getLocale(): string
    {
        return $this->locale ?? App::getLocale();
    }

    public function setLocale(?string $locale): self
    {
        $this->locale = $locale;

        return $this;
    }

    public function getDriver(): DriverInterface
    {
        return $this->driver ??= App::make(DriverInterface::class);
    }

    public function setDriver(DriverInterface $driver): self
    {
        $this->driver = $driver;

        return $this;
    }

    /**
     * The configured columns that are present on the model.
     */
    public function getTranslatableColumns(): array
    {
        $attributes = $this->requireModel()->getAttributes();

        return array_values(array_filter($this->getColumns(), fn (string $column) => array_key_exists($column, $attributes)));
    }

    /**
     * The supported locales other than the current one.
     */
    public function getOtherLocales(): array
    {
        $locale = $this->getLocale();

        return array_values(array_filter(
            (array) config('laravel-model-translate.supported_locales', []),
            fn (string $supported) => $supported !== $locale,
        ));
    }

    /**
     * Get the stored translations of the current locale, keyed by column.
     */
    public function getTranslations(): array
    {
        if (! $this->isPersisted()) {
            return [];
        }

        return $this->getDriver()->get($this->model, $this->getTranslatableColumns(), $this->getLocale());
    }

    /**
     * Fill the model with its translations in the current locale. The filled
     * columns are marked as clean, so saving the model will not write the
     * translated values into the model's own table.
     */
    public function translate(): Model
    {
        $model = $this->requireModel();
        $translations = $this->getTranslations();

        if ($translations !== []) {
            $model->setRawAttributes(array_merge($model->getAttributes(), $translations));
            $model->syncOriginalAttributes(array_keys($translations));
        }

        return $model;
    }

    /**
     * Create the initial translations for every supported locale except the
     * current one, using the model's current values.
     */
    public function create(): void
    {
        if (! $this->isPersisted()) {
            return;
        }

        $this->getDriver()->create($this->model, $this->values(), $this->getOtherLocales());
    }

    /**
     * Store the given values, or the model's current values, as the
     * translation of the current locale. Returns false when the current
     * locale has no translation for this model, i.e. the values belong in the
     * model's own table.
     */
    public function update(?array $values = null): bool
    {
        if (! $this->isPersisted()) {
            return false;
        }

        $values = $values === null ? $this->values() : array_intersect_key($values, array_flip($this->getColumns()));

        return $this->getDriver()->update($this->model, $values, $this->getLocale());
    }

    /**
     * Delete the model's translations in every supported locale.
     */
    public function delete(): void
    {
        if (! $this->isPersisted()) {
            return;
        }

        $this->getDriver()->delete($this->model, (array) config('laravel-model-translate.supported_locales', []));
    }

    /**
     * The raw values of the translatable columns, keyed by column.
     */
    private function values(): array
    {
        return array_intersect_key($this->model->getAttributes(), array_flip($this->getTranslatableColumns()));
    }

    private function isPersisted(): bool
    {
        return $this->requireModel()->getKey() !== null;
    }

    private function requireModel(): Model
    {
        if ($this->model === null) {
            throw new LogicException('A model must be set before translating. Call setModel() first.');
        }

        return $this->model;
    }
}
