<?php

namespace Onurkacmaz\LaravelModelTranslate\Drivers;

use Illuminate\Database\Eloquent\Model;
use MongoDB\BSON\UTCDateTime;
use MongoDB\Client;
use MongoDB\Collection;
use RuntimeException;

/**
 * Stores every translated column as a document of the form
 * { namespace, foreign_id, locale, key, value, created_at, updated_at }.
 */
class MongoDb implements DriverInterface
{
    private ?Collection $collection = null;

    public function __construct(private array $config = [])
    {
        if (! class_exists(Client::class)) {
            throw new RuntimeException('The mongodb driver requires the "mongodb/mongodb" package. Run: composer require mongodb/mongodb');
        }
    }

    public function get(Model $model, array $columns, string $locale): array
    {
        if ($columns === []) {
            return [];
        }

        $cursor = $this->collection()->find(
            $this->filter($model) + ['locale' => $locale, 'key' => ['$in' => array_values($columns)]],
            ['projection' => ['key' => 1, 'value' => 1], 'typeMap' => ['root' => 'array', 'document' => 'array', 'array' => 'array']],
        );

        $translations = [];

        foreach ($cursor as $document) {
            $translations[$document['key']] = $document['value'] ?? null;
        }

        return $translations;
    }

    public function create(Model $model, array $values, array $locales): void
    {
        if ($values === [] || $locales === []) {
            return;
        }

        if ($this->collection()->countDocuments($this->filter($model) + ['locale' => ['$in' => array_values($locales)]], ['limit' => 1]) > 0) {
            return;
        }

        $now = new UTCDateTime;
        $documents = [];

        foreach ($locales as $locale) {
            foreach ($values as $key => $value) {
                $documents[] = $this->filter($model) + [
                    'locale' => $locale,
                    'key' => $key,
                    'value' => $value,
                    'created_at' => $now,
                    'updated_at' => $now,
                ];
            }
        }

        $this->collection()->insertMany($documents);
    }

    public function update(Model $model, array $values, string $locale): bool
    {
        $filter = $this->filter($model) + ['locale' => $locale];

        if ($this->collection()->countDocuments($filter, ['limit' => 1]) === 0) {
            return false;
        }

        $now = new UTCDateTime;

        foreach ($values as $key => $value) {
            $this->collection()->updateOne(
                $filter + ['key' => $key],
                [
                    '$set' => ['value' => $value, 'updated_at' => $now],
                    '$setOnInsert' => ['created_at' => $now],
                ],
                ['upsert' => true],
            );
        }

        return true;
    }

    public function delete(Model $model, array $locales): void
    {
        $this->collection()->deleteMany($this->filter($model) + ['locale' => ['$in' => array_values($locales)]]);
    }

    protected function collection(): Collection
    {
        return $this->collection ??= (new Client($this->config['dsn'] ?? 'mongodb://localhost:27017'))
            ->selectCollection($this->config['database'] ?? 'translations', $this->config['collection'] ?? 'translations');
    }

    /**
     * @return array{namespace: string, foreign_id: string}
     */
    protected function filter(Model $model): array
    {
        return [
            'namespace' => $model->getMorphClass(),
            'foreign_id' => (string) $model->getKey(),
        ];
    }
}
