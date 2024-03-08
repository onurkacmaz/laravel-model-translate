<?php

namespace Onurkacmaz\LaravelModelTranslate\Drivers;

use MongoDB\Client;
use MongoDB\Collection;

class MongoDb extends AbstractDriver
{
    private \MongoDB\Database $mongoDb;
    private Collection $collection;

    public function __construct()
    {
        $client = new Client(config('laravel-model-translate.drivers.mongodb.dsn'));
        $this->mongoDb = $client->selectDatabase(config('laravel-model-translate.drivers.mongodb.database'));
        $this->collection = new Collection($client->getManager(), $this->mongoDb->getDatabaseName(), "translations");
    }

    public function get(): object
    {
        $values = [];

        $query = [
            'namespace' => $this->getModel()::class,
            'locale' => $this->getLocale(),
            'foreignId' => $this->getModel()->getKey()
        ];

        foreach ($this->getColumns() as $column) {
            $result = $this->collection->find($query);
            if (count($result->toArray()) > 0) {
                $values[] = (object)[
                    "key" => $column,
                    "value" => $result->current()['value']
                ];
            }
        }

        if (count($values) <= 0) {
            return (object)[];
        }

        return (object)$values;
    }

    public function create(): void {
        $locales = array_filter(config('laravel-model-translate.supported_locales'), fn($locale) => $locale !== $this->getLocale());

        $query = [
            'namespace' => $this->getModel()::class,
            'locale' => [
                '$not' => [
                    '$eq' => $this->getLocale()
                ]
            ],
            'foreignId' => $this->getModel()->getKey()
        ];

        $translation = $this->collection->find($query);

        if (count($translation->toArray()) <= 0) {
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

            $this->collection->insertOne($data);
        }
    }

    public function update(): void {
        $query = [
            'namespace' => $this->getModel()::class,
            'locale' => $this->getLocale(),
            'foreignId' => $this->getModel()->getKey()
        ];

        $translations = $this->collection->find($query);

        if (count($translations->toArray()) > 0) {
            foreach ($translations as $translation) {
                if (in_array($translation["key"], $this->getColumns())) {
                    $this->collection->updateOne($query, [
                        'value' => $this->getModel()->getAttribute($translation["key"])
                    ]);
                }
            }
            $this->getModel()->setAttribute('isOriginal', false);
        }else {
            $this->getModel()->setAttribute('isOriginal', true);
        }
    }
}