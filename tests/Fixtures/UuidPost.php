<?php

namespace Onurkacmaz\LaravelModelTranslate\Tests\Fixtures;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Onurkacmaz\LaravelModelTranslate\Traits\Translatable;

class UuidPost extends Model
{
    use HasUuids;
    use Translatable;

    protected $table = 'uuid_posts';

    protected $guarded = [];

    protected array $translatable = ['title'];
}
