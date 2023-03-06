<?php

namespace Onurkacmaz\LaravelModelTranslate\Drivers;

use MongoClient;
use MongoCollection;
use MongoDB as MongoBase;

class MongoDb extends AbstractDriver
{
    protected MongoClient $mongoClient;
    private MongoBase $mongoDb;
    private MongoCollection $collection;

    public function __construct()
    {
        $this->mongoClient = new MongoClient(config('laravel-model-translate.drivers.mongodb.dsn'));
        $this->mongoDb = $this->mongoClient->selectDB(config('laravel-model-translate.drivers.mongodb.database'));
        $this->collection = new MongoCollection($this->mongoDb, 'translations');
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
            if ($result->count() > 0) {
                $values[] = (object)[
                    "key" => $column,
                    "value" => $result->getNext()['value']
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

            $this->collection->insert($data);
        }
    }

    public function update(): void {
        $query = [
            'namespace' => $this->getModel()::class,
            'locale' => $this->getLocale(),
            'foreignId' => $this->getModel()->getKey()
        ];

        $translation = $this->collection->find($query);

        if ($translation->count() > 0) {
            foreach ($translation->getNext() as $translation) {
                if (in_array($translation["key"], $this->getColumns())) {
                    $this->collection->update($query, [
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