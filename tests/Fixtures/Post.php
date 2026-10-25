<?php

namespace Onurkacmaz\LaravelModelTranslate\Tests\Fixtures;

use Illuminate\Database\Eloquent\Model;
use Onurkacmaz\LaravelModelTranslate\Traits\Translatable;

class Post extends Model
{
    use Translatable;

    protected $table = 'posts';

    protected $guarded = [];

    protected array $translatable = ['title', 'content'];
}
