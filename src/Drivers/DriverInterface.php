<?php

namespace Onurkacmaz\LaravelModelTranslate\Drivers;

use Illuminate\Database\Eloquent\Model;

interface DriverInterface
{
    public function setColumns(array $columns):self;

    public function setLocale(string $locale): self;

    public function getColumns(): array;

    public function getLocale(): string;

    public function setModel(Model $model): self;

    public function getModel(): Model;

    public function get(): object;

    public function create(): void;

    public function update(): void;
}