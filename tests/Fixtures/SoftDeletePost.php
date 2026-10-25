<?php

namespace Onurkacmaz\LaravelModelTranslate\Tests\Fixtures;

use Illuminate\Database\Eloquent\SoftDeletes;

class SoftDeletePost extends Post
{
    use SoftDeletes;
}
