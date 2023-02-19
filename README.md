# Laravel Model Translate

[![Latest Version on Packagist](https://img.shields.io/packagist/v/onurkacmaz/laravel-model-translate.svg?style=flat-square)](https://packagist.org/packages/onurkacmaz/laravel-model-translate)
[![Total Downloads](https://img.shields.io/packagist/dt/onurkacmaz/laravel-model-translate.svg?style=flat-square)](https://packagist.org/packages/onurkacmaz/laravel-model-translate)

This package allows model translation. It is like Symfony's gedmo translation package. It is very easy to use. You can use it in your models or controllers.
Basically, it is a trait that you can use in your models. It will automatically create translation records of the fields you specify in the model. It will show the translation according to the registered locale. When creating or updating it will use the registered locale and will process that record. The main table pairs with "foreign_id" and "model namespace" to the translations table.
## Installation

You can install the package via composer:

```bash
composer require onurkacmaz/laravel-model-translate
```

```bash
php artisan vendor:publish --provider="Onurkacmaz\LaravelModelTranslate\LaravelModelTranslateServiceProvider" --tag=config
php artisan vendor:publish --provider="Onurkacmaz\LaravelModelTranslate\LaravelModelTranslateServiceProvider" --tag=migrations
```

```bash
php artisan migrate
```

## Usage

### Trait

```php
use Onurkacmaz\LaravelModelTranslate\Traits\Translatable;

class Blog extends Model
{
    use Translatable;

    // You can define which fields will be translated
    public function getTranslatable(): array
    {
        return ['title', 'content'];
    }
}
```

### Class Based Usage

```php
use Onurkacmaz\LaravelModelTranslate\Traits\Translatable;

class TestController extends Controller
{
    public function index() {
        $translate = new LaravelModelTranslate();
        $translate->setColumns(['title', 'content']);
        $translate->setModel($blog);
        $translate->setLocale('en');
        $translate->translate();
        
        // or
        
        $translate = new LaravelModelTranslate($blog, ['title', 'content'], 'en');
        $translate->translate();
    }
}
```

### Static Usage

```php
use Onurkacmaz\LaravelModelTranslate\Traits\Translatable;

class TestController extends Controller
{
    public function index() {
        $translate = LaravelModelTranslate::make()
            ->setModel($account)
            ->setLocale('en')
            ->setColumns(['title', 'content'])
            ->translate();
    }
}
```

### Testing

```bash
composer test
```

## Contributing

Please see [CONTRIBUTING](CONTRIBUTING.md) for details.

### Security

If you discover any security related issues, please email kacmaz.onur@hotmail.com instead of using the issue tracker.

## Credits

-   [Onur Kaçmaz](https://github.com/onurkacmaz)
-   [All Contributors](../../contributors)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.

## Laravel Package Boilerplate

This package was generated using the [Laravel Package Boilerplate](https://laravelpackageboilerplate.com).
