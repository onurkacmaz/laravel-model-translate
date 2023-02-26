<?php

namespace Onurkacmaz\LaravelModelTranslate\Drivers;

use Illuminate\Database\Eloquent\Model;

abstract class AbstractDriver implements DriverInterface
{
    private array $columns = [];

    private string $locale;

    private Model $model;

    public function setColumns(array $columns):self {
        $this->columns = $columns;
        return $this;
    }

    public function setLocale(string $locale): self {
        $this->locale = $locale;
        return $this;
    }

    public function getColumns(): array
    {
        return $this->columns;
    }

    public function getLocale(): string
    {
        return $this->locale;
    }

    public function setModel(Model $model): self
    {
        $this->model = $model;

        return $this;
    }

    public function getModel(): Model
    {
        return $this->model;
    }
}