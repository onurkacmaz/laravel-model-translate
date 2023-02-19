<?php

namespace Onurkacmaz\LaravelModelTranslate\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Translation extends Model
{
    use HasFactory;

    protected $table = 'translations';
    protected $guarded = [];
}