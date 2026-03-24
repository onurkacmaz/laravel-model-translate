<?php

namespace Onurkacmaz\LaravelModelTranslate\Drivers;

use Illuminate\Support\Collection;
use Redis as RedisBase;

class Redis extends AbstractDriver
{
    private RedisBase $redis;

    public function __construct()
    {
        $this->redis = new RedisBase();
        $this->redis->connect(
            config('laravel-model-translate.drivers.redis.host'),
            config('laravel-model-translate.drivers.redis.port')
        );

        $username = config('laravel-model-translate.drivers.redis.user');
        $password = config('laravel-model-translate.drivers.redis.password');

        if (!empty($password)) {
            $this->redis->auth(
                $username
                    ? ['user' => $username, 'pass' => $password]
                    : $password
            );
        }

        $this->redis->select(config('laravel-model-translate.drivers.redis.database'));
    }

    public function get(): Collection
    {
        $values = new Collection();

        foreach ($this->getColumns() as $column) {
            $key = $this->makeKey($column, $this->getLocale());

            if ($this->redis->exists($key)) {
                $values->push((object) [
                    'key' => $column,
                    'value' => $this->redis->get($key),
                ]);
            }
        }

        return $values;
    }

    public function create(): void
    {
        $locales = array_filter(
            config('laravel-model-translate.supported_locales'),
            fn ($locale) => $locale !== $this->getLocale()
        );

        foreach ($this->getColumns() as $column) {
            foreach ($locales as $locale) {
                $key = $this->makeKey($column, $locale);

                if (!$this->redis->exists($key)) {
                    $this->redis->set($key, $this->getModel()->getAttribute($column));
                }
            }
        }
    }

    public function update(): void
    {
        $translations = $this->get();

        if ($translations->isNotEmpty()) {
            foreach ($translations as $translation) {
                if (in_array($translation->key, $this->getColumns(), true)) {
                    $this->redis->set(
                        $this->makeKey($translation->key, $this->getLocale()),
                        $this->getModel()->getAttribute($translation->key)
                    );
                }
            }

            $this->getModel()->setAttribute('isOriginal', false);
        } else {
            $this->getModel()->setAttribute('isOriginal', true);
        }
    }

    private function makeKey(string $column, string $locale): string
    {
        return sprintf('%s:%s:%s:%s', $this->getModel()::class, $locale, $column, $this->getModel()->getKey());
    }
}
