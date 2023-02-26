<?php

namespace Onurkacmaz\LaravelModelTranslate\Drivers;

use Redis as RedisBase;

class Redis extends AbstractDriver
{
    private RedisBase $redis;

    public function __construct()
    {
        $this->redis = new RedisBase();
        $this->redis->connect(config('laravel-model-translate.drivers.redis.host'), config('laravel-model-translate.drivers.redis.port'));
        $this->redis->auth([
            'user' => config('laravel-model-translate.drivers.redis.user'),
            'password' => config('laravel-model-translate.drivers.redis.password')
        ]);
        $this->redis->select(config('laravel-model-translate.drivers.redis.database'));
    }

    public function get(): object
    {
        $values = [];

        foreach ($this->getColumns() as $column) {
            $key = sprintf('%s:%s:%s:%s', $this->getModel()::class, $this->getLocale(), $column, $this->getModel()->getKey());
            if ($this->redis->exists($key)) {
                $values[] = (object)[
                    "key" => $column,
                    "value" => $this->redis->get($key)
                ];
            }
        }

        if (count($values) <= 0) {
            return (object)[];
        }

        return (object)$values;
    }

    public function create(): void {
        $values = [];

        $locales = array_filter(config('laravel-model-translate.supported_locales'), fn($locale) => $locale !== $this->getLocale());

        foreach ($this->getColumns() as $column) {
            $key = sprintf('%s:%s:%s:%s', $this->getModel()::class, $this->getLocale(), $column, $this->getModel()->getKey());
            if ($this->redis->exists($key)) {
                $values[] = $this->redis->get($key);
            }
        }

        if (count($values) <= 0) {
            foreach ($this->getColumns() as $column) {
                foreach ($locales as $locale) {
                    $this->redis->set(sprintf('%s:%s:%s:%s', $this->getModel()::class, $locale, $column, $this->getModel()->getKey()), $this->getModel()->getAttribute($column));
                }
            }
        }
    }

    public function update(): void {
        $translations = (array)$this->get();

        if (count($translations) > 0) {
            foreach ($translations as $translation) {
                if (in_array($translation->key, $this->getColumns())) {
                    $translation->value = $this->getModel()->getAttribute($translation->key);
                    $this->redis->set(sprintf('%s:%s:%s:%s', $this->getModel()::class, $this->getLocale(), $translation->key, $this->getModel()->getKey()), $translation->value);
                }
            }
            $this->getModel()->setAttribute('isOriginal', false);
        }else {
            $this->getModel()->setAttribute('isOriginal', true);
        }
    }
}