<?php

namespace Onurkacmaz\LaravelModelTranslate\Drivers;

use Onurkacmaz\LaravelModelTranslate\Models\Translation;

class Database extends AbstractDriver
{
    final public function get(): object
    {
        return (object)Translation::query()
            ->select('key', 'value')
            ->whereIn('key', $this->getColumns())
            ->where('namespace', $this->getModel()::class)
            ->where('locale', $this->getLocale())
            ->where('foreign_id', $this->getModel()->getKey())
            ->get()
            ->toArray();
    }

    final public function create(): void {
        $locales = array_filter(config('laravel-model-translate.supported_locales'), fn($locale) => $locale !== $this->getLocale());

        $translation = Translation::query()
            ->where('namespace', $this->getModel()::class)
            ->where('locale', '!=', $this->getLocale())
            ->where('foreign_id', $this->getModel()->getKey())->get();

        if ($translation->count() <= 0) {
            $data = [];
            foreach ($this->getColumns() as $column) {
                foreach ($locales as $locale) {
                    $data[] = [
                        'key' => $column,
                        'value' => $this->getModel()->getAttribute($column),
                        'namespace' => $this->getModel()::class,
                        'locale' => $locale,
                        'foreign_id' => $this->getModel()->getKey(),
                        'created_at' => now(),
                    ];
                }
            }
            Translation::query()->insert($data);
        }
    }

    final public function update(): void {
        $translation = Translation::query()
            ->where('namespace', $this->getModel()::class)
            ->where('locale', $this->getLocale())
            ->where('foreign_id', $this->getModel()->getKey())->get();

        if ($translation->count() > 0) {
            $translation->each(function ($item) {
                if (in_array($item->key, $this->getColumns())) {
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