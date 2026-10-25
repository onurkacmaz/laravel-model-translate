# Laravel Model Translate

[![Latest Version on Packagist](https://img.shields.io/packagist/v/onurkacmaz/laravel-model-translate.svg?style=flat-square)](https://packagist.org/packages/onurkacmaz/laravel-model-translate)
[![Tests](https://img.shields.io/github/actions/workflow/status/onurkacmaz/laravel-model-translate/tests.yml?branch=main&label=tests&style=flat-square)](https://github.com/onurkacmaz/laravel-model-translate/actions/workflows/tests.yml)
[![Total Downloads](https://img.shields.io/packagist/dt/onurkacmaz/laravel-model-translate.svg?style=flat-square)](https://packagist.org/packages/onurkacmaz/laravel-model-translate)
[![License](https://img.shields.io/packagist/l/onurkacmaz/laravel-model-translate.svg?style=flat-square)](LICENSE.md)

Translate the attributes of your Eloquent models into multiple locales, inspired by
[Gedmo Translatable](https://github.com/doctrine-extensions/DoctrineExtensions/blob/main/doc/translatable.md) for Symfony.

Add a trait to a model, list the columns to translate, and the package takes care of the rest:

- The values written in the locale that is active when a model is **created** stay in the model's own table.
- A copy of the translatable columns is stored for every other supported locale, in a database table, Redis or MongoDB.
- When a model is **retrieved**, its translatable columns are replaced with the translation of the current application locale.
- When a model is **updated** in a translated locale, the changed translatable columns are written to the translation
  storage and the model's own table is left untouched. Other (non-translatable) columns are saved as usual.
- When a model is **deleted**, its translations are deleted too (soft deleted models keep them until force deleted).

## Requirements

- PHP 8.2+
- Laravel 12 or 13
- For the `redis` driver: [phpredis](https://github.com/phpredis/phpredis) or [predis](https://github.com/predis/predis)
- For the `mongodb` driver: the [`mongodb`](https://www.php.net/manual/en/mongodb.installation.php) extension and `mongodb/mongodb`

## Installation

Install the package via composer:

```bash
composer require onurkacmaz/laravel-model-translate
```

Publish the config file and the migration, then run the migration:

```bash
php artisan vendor:publish --tag=laravel-model-translate-config
php artisan vendor:publish --tag=laravel-model-translate-migrations
php artisan migrate
```

The migration is only needed for the `database` driver.

## Configuration

`config/laravel-model-translate.php`:

```php
return [
    // Locales the models can be translated into.
    'supported_locales' => ['tr', 'en', 'de', 'fr'],

    // database, redis or mongodb
    'driver' => env('TRANSLATION_DRIVER', 'database'),

    'drivers' => [
        'database' => [
            'driver' => \Onurkacmaz\LaravelModelTranslate\Drivers\Database::class,
            'connection' => env('TRANSLATION_DB_CONNECTION'), // null: default connection
            'table' => env('TRANSLATION_DB_TABLE', 'translations'),
        ],
        'redis' => [
            'driver' => \Onurkacmaz\LaravelModelTranslate\Drivers\Redis::class,
            'connection' => env('TRANSLATION_REDIS_CONNECTION', 'default'), // from config/database.php
            'prefix' => env('TRANSLATION_REDIS_PREFIX', 'translations'),
        ],
        'mongodb' => [
            'driver' => \Onurkacmaz\LaravelModelTranslate\Drivers\MongoDb::class,
            'dsn' => env('TRANSLATION_MONGO_DSN', 'mongodb://localhost:27017'),
            'database' => env('TRANSLATION_MONGO_DB', 'translations'),
            'collection' => env('TRANSLATION_MONGO_COLLECTION', 'translations'),
        ],
    ],
];
```

To use the MongoDB driver, install the client library first:

```bash
composer require mongodb/mongodb
```

## Usage

### Making a model translatable

Use the `Translatable` trait and list the translatable columns in a `$translatable` property:

```php
use Illuminate\Database\Eloquent\Model;
use Onurkacmaz\LaravelModelTranslate\Traits\Translatable;

class Blog extends Model
{
    use Translatable;

    protected array $translatable = ['title', 'content'];
}
```

or, if you need to compute them, override `getTranslatable()`:

```php
public function getTranslatable(): array
{
    return ['title', 'content'];
}
```

### Reading and writing translations

```php
app()->setLocale('tr');

$blog = Blog::create(['title' => 'Merhaba Dünya', 'content' => '...']);
// "tr" is stored in the blogs table, "en", "de" and "fr" start as copies of it.

app()->setLocale('en');

$blog = Blog::find($blog->id);
$blog->title;                     // "Merhaba Dünya" (the "en" copy)

$blog->update(['title' => 'Hello World']);
// Only the "en" translation is updated, the blogs table still says "Merhaba Dünya".

Blog::find($blog->id)->title;     // "Hello World"

app()->setLocale('tr');
Blog::find($blog->id)->title;     // "Merhaba Dünya"
```

The trait also adds a few helpers:

```php
// Store a translation without changing the locale or the model's attributes.
// Returns false when the locale is the one kept in the model's own table.
$blog->saveTranslation('de', ['title' => 'Hallo Welt']);

// Get the stored translation of a locale, keyed by column.
$blog->getTranslations('de');     // ['title' => 'Hallo Welt', 'content' => '...']

// Show the model in another locale.
$blog->translateTo('de')->title;  // "Hallo Welt"
```

### Without the trait

The `LaravelModelTranslate` class can be used directly, for example in a controller:

```php
use Onurkacmaz\LaravelModelTranslate\LaravelModelTranslate;

$translate = new LaravelModelTranslate();
$translate->setColumns(['title', 'content']);
$translate->setModel($blog);
$translate->setLocale('en');
$translate->translate();

// or
(new LaravelModelTranslate($blog, ['title', 'content'], 'en'))->translate();

// or
LaravelModelTranslate::make()
    ->setModel($blog)
    ->setLocale('en')
    ->setColumns(['title', 'content'])
    ->translate();

// or, through the facade
\LaravelModelTranslate::setModel($blog)->setColumns(['title'])->setLocale('en')->translate();
```

When no locale is set, the current application locale is used. Besides `translate()`, the class has
`getTranslations()`, `create()`, `update(?array $values = null)` and `delete()`.

### Custom drivers

Any class implementing `Onurkacmaz\LaravelModelTranslate\Drivers\DriverInterface` can be used as a driver. Register it
in the config file; its constructor receives the driver's config array as `$config`:

```php
'driver' => 'dynamodb',

'drivers' => [
    'dynamodb' => [
        'driver' => App\Translation\DynamoDbDriver::class,
        'table' => 'translations',
    ],
],
```

## Things to know

- **The locale that creates a model owns its table.** A locale is treated as "translated" for a model when a
  translation exists for it. Translations are only seeded once, when the model is first saved, so a locale added to
  `supported_locales` later has no translation for existing models; updating a model in that locale writes to the
  model's own table. Use `saveTranslation()` to add the translation instead.
- **One query per model.** Translations are loaded in the `retrieved` event, so loading a collection of N models runs
  N translation lookups.
- **Morph map.** Models are identified by `getMorphClass()`, so an
  [enforced morph map](https://laravel.com/docs/eloquent-relationships#custom-polymorphic-types) is respected. Adding a
  morph map after translations were stored changes the stored identifier.

## Testing

```bash
composer test
```

The Redis tests use the server at `REDIS_HOST:REDIS_PORT` (default `127.0.0.1:6379`, database 15) and are skipped when
it is not reachable. The MongoDB tests run when `mongodb/mongodb` is installed and a server is reachable at
`TRANSLATION_MONGO_DSN`.

## Changelog

Please see [CHANGELOG](CHANGELOG.md) for what has changed recently.

## Contributing

Please see [CONTRIBUTING](CONTRIBUTING.md) for details.

## Security

Please review [our security policy](SECURITY.md) on how to report security vulnerabilities.

## Credits

- [Onur Kaçmaz](https://github.com/onurkacmaz)
- [All Contributors](../../contributors)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
