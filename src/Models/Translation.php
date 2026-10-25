<?php

namespace Onurkacmaz\LaravelModelTranslate\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property string $namespace
 * @property string $foreign_id
 * @property string $locale
 * @property string $key
 * @property string|null $value
 */
class Translation extends Model
{
    protected $table = 'translations';

    protected $guarded = [];
}
