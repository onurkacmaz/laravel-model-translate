<?php

namespace Onurkacmaz\LaravelModelTranslate;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Schema;
use Onurkacmaz\LaravelModelTranslate\Drivers\AbstractDriver;

class LaravelModelTranslate
{
    private array $columns = [];

    private Model|null $model;

    private string|null $locale;

    private AbstractDriver $driver;

    public function __construct(Model|null $model = null, array $columns = [], string|null $locale = null)
    {
        $this->setColumns($columns);
        $this->setModel($model);
        $this->setLocale($locale ?? App::getLocale());
        /** @var AbstractDriver $class */
        $class = config(sprintf("laravel-model-translate.drivers.%s.driver", config('laravel-model-translate.driver')));
        $this->driver = new $class;
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

    public function getDriver(): AbstractDriver
    {
        return $this->driver;
    }

    public function translate(): Model
    {
        $translations = $this->getDriver()
            ->setColumns($this->getTranslatableColumns())
            ->setModel($this->getModel())
            ->setLocale($this->getLocale())
            ->get();

        foreach ($translations as $item) {
            $this->getModel()->setAttribute($item->key, $item->value);
        }

        return $this->getModel();
    }

    public function create(): void {
        $this->getDriver()
            ->setColumns($this->getTranslatableColumns())
            ->setLocale($this->getLocale())
            ->setModel($this->getModel())
            ->create();
    }

    public function update(): void {
        $this->driver
            ->setColumns($this->getTranslatableColumns())
            ->setLocale($this->getLocale())
            ->setModel($this->getModel())
            ->update();
    }
}
