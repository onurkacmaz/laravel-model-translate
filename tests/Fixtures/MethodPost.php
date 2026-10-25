<?php

namespace Onurkacmaz\LaravelModelTranslate\Tests\Fixtures;

use Illuminate\Database\Eloquent\Model;
use Onurkacmaz\LaravelModelTranslate\Traits\Translatable;

class MethodPost extends Model
{
    use Translatable;

    protected $table = 'posts';

    protected $guarded = [];

    public function getTranslatable(): array
    {
        return ['title'];
    }
}
