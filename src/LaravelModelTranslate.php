<?php

namespace Onurkacmaz\LaravelModelTranslate;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Schema;
use Onurkacmaz\LaravelModelTranslate\Models\Translation;

class LaravelModelTranslate
{
    private array $columns = [];

    private Model|null $model;

    private string|null $locale;

    public function __construct(Model|null $model = null, array $columns = [], string|null $locale = null)
    {
        $this->setColumns($columns);
        $this->setModel($model);
        $this->setLocale($locale ?? App::getLocale());
    }

    public function getColumns(): array
    {
        return $this->columns;
    }

    public function setColumns(array $columns): self
    {
        $this->columns = $columns;

        return $this;
    }

    public function getModel(): Model|null
    {
        return $this->model;
    }

    public function setModel(Model|null $model = null): self
    {
        $this->model = $model;

        return $this;
    }

    public static function make(): self
    {
        return new self();
    }

    public function getLocale(): string
    {
        return $this->locale;
    }

    public function setLocale(string $locale): self
    {
        $this->locale = $locale;

        return $this;
    }

    public function getTranslatableColumns(): array
    {
        return array_filter(Schema::getColumnListing($this->getModel()->getTable()), function ($column) {
            return in_array($column, $this->getColumns());
        });
    }

    public function translate(): Model
    {
        $translatableColumns = $this->getTranslatableColumns();

        $translations = Translation::query()
            ->select('key', 'value')
            ->whereIn('key', $translatableColumns)
            ->where('namespace', $this->getModel()::class)
            ->where('locale', $this->getLocale())
            ->where('foreign_id', $this->getModel()->getKey())
            ->get();

        foreach ($translations as $item) {
            $this->getModel()->setAttribute($item->key, $item->value);
        }

        return $this->getModel();
    }

    public function create(): void {
        $translatableColumns = $this->getTranslatableColumns();

        $locales = array_filter(config('laravel-model-translate.translatable.supported_locales'), fn($locale) => $locale !== $this->getLocale());

        $translation = Translation::query()
            ->where('namespace', $this->getModel()::class)
            ->where('locale', '!=', $this->getLocale())
            ->where('foreign_id', $this->getModel()->getKey())->get();

        if ($translation->count() <= 0) {
            $data = [];
            foreach ($translatableColumns as $column) {
                foreach ($locales as $locale) {
                    $data[] = [
                        'key' => $column,
                        'value' => $this->getModel()->getAttribute($column),
                        'namespace' => $this->getModel()::class,
                        'locale' => $locale,
                        'foreign_id' => $this->getModel()->getKey(),
                    ];
                }
            }
            Translation::query()->insert($data);
        }
    }

    public function update(): void {
        $translatableColumns = $this->getTranslatableColumns();

        $translation = Translation::query()
            ->where('namespace', $this->getModel()::class)
            ->where('locale', $this->getLocale())
            ->where('foreign_id', $this->getModel()->getKey())->get();

        if ($translation->count() > 0) {
            $translation->each(function ($item) use ($translatableColumns) {
                if (in_array($item->key, $translatableColumns)) {
                    $item->value = $this->getModel()->getAttribute($item->key);
                    $item->save();
                }
            });
            $this->getModel()->setAttribute('isOriginal', false);
        }else {
            $this->getModel()->setAttribute('isOriginal', true);
        }
    }
}
