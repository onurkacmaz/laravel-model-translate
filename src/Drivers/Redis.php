<?php

namespace Onurkacmaz\LaravelModelTranslate\Drivers;

use Illuminate\Contracts\Redis\Factory as RedisFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Redis\Connections\Connection;

/**
 * Stores the translations of a model in one Redis hash per locale. Values
 * are JSON encoded so that null and scalar types survive the round trip.
 */
class Redis implements DriverInterface
{
    public function __construct(private RedisFactory $redis, private array $config = []) {}

    public function get(Model $model, array $columns, string $locale): array
    {
        if ($columns === []) {
            return [];
        }

        $hash = $this->connection()->hgetall($this->key($model, $locale)) ?: [];

        $translations = [];

        foreach ($columns as $column) {
            if (array_key_exists($column, $hash)) {
                $translations[$column] = json_decode($hash[$column], true);
            }
        }

        return $translations;
    }

    public function create(Model $model, array $values, array $locales): void
    {
        if ($values === [] || $locales === []) {
            return;
        }

        foreach ($locales as $locale) {
            if ($this->connection()->exists($this->key($model, $locale))) {
                return;
            }
        }

        foreach ($locales as $locale) {
            $this->connection()->hmset($this->key($model, $locale), $this->encode($values));
        }
    }

    public function update(Model $model, array $values, string $locale): bool
    {
        $key = $this->key($model, $locale);

        if (! $this->connection()->exists($key)) {
            return false;
        }

        if ($values !== []) {
            $this->connection()->hmset($key, $this->encode($values));
        }

        return true;
    }

    public function delete(Model $model, array $locales): void
    {
        if ($locales === []) {
            return;
        }

        $this->connection()->del(...array_map(fn (string $locale) => $this->key($model, $locale), $locales));
    }

    protected function connection(): Connection
    {
        return $this->redis->connection($this->config['connection'] ?? null);
    }

    protected function key(Model $model, string $locale): string
    {
        return implode(':', array_filter([
            $this->config['prefix'] ?? 'translations',
            $model->getMorphClass(),
            $model->getKey(),
            $locale,
        ], fn ($part) => $part !== null && $part !== ''));
    }

    /**
     * @param  array<string, mixed>  $values
     * @return array<string, string>
     */
    protected function encode(array $values): array
    {
        return array_map(fn ($value) => json_encode($value), $values);
    }
}
