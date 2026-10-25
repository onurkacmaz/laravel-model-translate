<?php

namespace Onurkacmaz\LaravelModelTranslate\Drivers;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Onurkacmaz\LaravelModelTranslate\Models\Translation;

class Database implements DriverInterface
{
    public function __construct(private array $config = [])
    {
    }

    public function get(Model $model, array $columns, string $locale): array
    {
        if ($columns === []) {
            return [];
        }

        return $this->query($model)
            ->where('locale', $locale)
            ->whereIn('key', $columns)
            ->pluck('value', 'key')
            ->all();
    }

    public function create(Model $model, array $values, array $locales): void
    {
        if ($values === [] || $locales === []) {
            return;
        }

        if ($this->query($model)->whereIn('locale', $locales)->exists()) {
            return;
        }

        $now = now();
        $rows = [];

        foreach ($locales as $locale) {
            foreach ($values as $key => $value) {
                $rows[] = [
                    'namespace' => $model->getMorphClass(),
                    'foreign_id' => (string) $model->getKey(),
                    'locale' => $locale,
                    'key' => $key,
                    'value' => $value,
                    'created_at' => $now,
                    'updated_at' => $now,
                ];
            }
        }

        $this->newModel()->newQuery()->insert($rows);
    }

    public function update(Model $model, array $values, string $locale): bool
    {
        if (! $this->query($model)->where('locale', $locale)->exists()) {
            return false;
        }

        foreach ($values as $key => $value) {
            $this->newModel()->newQuery()->updateOrCreate([
                'namespace' => $model->getMorphClass(),
                'foreign_id' => (string) $model->getKey(),
                'locale' => $locale,
                'key' => $key,
            ], ['value' => $value]);
        }

        return true;
    }

    public function delete(Model $model, array $locales): void
    {
        $this->query($model)->whereIn('locale', $locales)->delete();
    }

    protected function query(Model $model): Builder
    {
        return $this->newModel()->newQuery()
            ->where('namespace', $model->getMorphClass())
            ->where('foreign_id', (string) $model->getKey());
    }

    protected function newModel(): Translation
    {
        return (new Translation())
            ->setConnection($this->config['connection'] ?? null)
            ->setTable($this->config['table'] ?? 'translations');
    }
}
