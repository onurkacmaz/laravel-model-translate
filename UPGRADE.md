# Upgrade Guide

## From 1.x to 2.0

### Requirements

PHP 8.2 and Laravel 12 or 13 are now required.

### Configuration

`supported_locales` moved out of the `translatable` key to the top level of the config file. Republish the config file
and copy over your locales:

```bash
php artisan vendor:publish --tag=laravel-model-translate-config --force
```

```php
// 1.x
'translatable' => [
    'supported_locales' => ['tr', 'en'],
],

// 2.0
'supported_locales' => ['tr', 'en'],
```

The new file also adds the `driver` option and a `drivers` section. The default `database` driver keeps using the
`translations` table, so no change is needed to keep the 1.x behaviour.

### Database schema

The 2.0 migration stores `foreign_id` as a string (so models with UUID/ULID keys are supported) and adds a unique
index on `namespace`, `foreign_id`, `locale` and `key`. Existing tables keep working with integer keys. To get the new
schema, create a migration in your application:

```php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('translations', function (Blueprint $table) {
            $table->string('foreign_id', 64)->change();
            $table->string('locale', 16)->change();
            $table->dropIndex(['key', 'namespace']);
            $table->unique(['namespace', 'foreign_id', 'locale', 'key']);
        });
    }
};
```

The unique index fails if the table holds duplicate rows; remove them first.

### Models

- The trait now boots with `bootTranslatable()`. If your model overrides `boot()`, keep calling `parent::boot()`.
- Translatable columns can be listed in a `protected array $translatable` property instead of overriding
  `getTranslatable()`. Overriding the method still works.
- Updating a model in a translated locale now saves its non-translatable columns. In 1.x the whole save was skipped.
- Deleting a model now deletes its translations (soft deleted models keep them until force deleted).
- Models are identified by `getMorphClass()`. This only changes the stored `namespace` if you use a morph map; in that
  case update the `namespace` column of existing rows to the morph alias.

### Custom code

- `LaravelModelTranslate::update()` now returns a `bool`, and accepts an optional array of values.
- `LaravelModelTranslate::getTranslatableColumns()` no longer queries the schema; it returns the configured columns
  that are present on the model.
- The facade resolves a new instance for every call instead of sharing one.
