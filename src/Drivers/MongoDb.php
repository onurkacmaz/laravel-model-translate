<?php

namespace Onurkacmaz\LaravelModelTranslate\Drivers;

use Illuminate\Support\Collection as SupportCollection;
use MongoDB\Client;
use MongoDB\Collection;
use MongoDB\Database;

class MongoDb extends AbstractDriver
{
    private Database $mongoDb;
    private Collection $collection;

    public function __construct()
    {
        $client = new Client(config('laravel-model-translate.drivers.mongodb.dsn'));
        $this->mongoDb = $client->selectDatabase(config('laravel-model-translate.drivers.mongodb.database'));
        $this->collection = new Collection($client->getManager(), $this->mongoDb->getDatabaseName(), 'translations');
    }

    public function get(): SupportCollection
    {
        $cursor = $this->collection->find([
            'namespace' => $this->getModel()::class,
            'locale' => $this->getLocale(),
            'foreign_id' => $this->getModel()->getKey(),
            'key' => ['$in' => $this->getColumns()],
        ]);

        $values = [];

        foreach ($cursor as $document) {
            $values[] = (object) [
                'key' => $document['key'],
                'value' => $document['value'] ?? null,
            ];
        }

        return new SupportCollection($values);
    }

    public function create(): void
    {
        $locales = array_filter(
            config('laravel-model-translate.supported_locales'),
            fn ($locale) => $locale !== $this->getLocale()
        );

        $existingCount = $this->collection->countDocuments([
            'namespace' => $this->getModel()::class,
            'locale' => ['$ne' => $this->getLocale()],
            'foreign_id' => $this->getModel()->getKey(),
        ]);

        if ($existingCount > 0) {
            return;
        }

        $payload = [];

        foreach ($this->getColumns() as $column) {
            foreach ($locales as $locale) {
                $payload[] = [
                    'key' => $column,
                    'value' => $this->getModel()->getAttribute($column),
                    'namespace' => $this->getModel()::class,
                    'locale' => $locale,
                    'foreign_id' => $this->getModel()->getKey(),
                    'created_at' => now(),
                ];
            }
        }

        if ($payload !== []) {
            $this->collection->insertMany($payload);
        }
    }

    public function update(): void
    {
        $cursor = $this->collection->find([
            'namespace' => $this->getModel()::class,
            'locale' => $this->getLocale(),
            'foreign_id' => $this->getModel()->getKey(),
            'key' => ['$in' => $this->getColumns()],
        ]);

        $hasTranslations = false;

        foreach ($cursor as $translation) {
            $hasTranslations = true;

            $this->collection->updateOne(
                ['_id' => $translation['_id']],
                [
                    '$set' => [
                        'value' => $this->getModel()->getAttribute($translation['key']),
                        'updated_at' => now(),
                    ],
                ]
            );
        }

        $this->getModel()->setAttribute('isOriginal', !$hasTranslations);
    }
}