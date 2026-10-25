# Changelog

All notable changes to `laravel-model-translate` will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.1.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [Unreleased]

### Added
- Laravel 12 and 13 support; the Laravel components are now declared dependencies. PHP 8.2 or newer is required.
- `deleted` handling: a model's translations are removed when it is deleted (or force deleted when it uses soft deletes).
- `saveTranslation()`, `getTranslations()` and `translateTo()` helpers on the `Translatable` trait.
- `getTranslations()` and `delete()` on `LaravelModelTranslate`; `update()` accepts explicit values.
- `$translatable` property as an alternative to overriding `getTranslatable()`.
- Configurable database connection and table, Redis connection and key prefix, MongoDB collection.
- Support for models with UUID/ULID keys (`foreign_id` is now a string column).
- `InvalidDriverException` for a missing or invalid driver configuration.
- Test suite, GitHub Actions workflow, Laravel Pint, and the usual open source community files.

### Changed
- Drivers are stateless services implementing the new `DriverInterface` (`get`, `create`, `update`, `delete`), resolved
  once from the container. `AbstractDriver` has been removed.
- Updating a model in a translated locale no longer aborts the save: non-translatable columns are saved, only the
  translatable ones are kept out of the model's table.
- The Redis driver uses Laravel's Redis connections (phpredis or predis) and stores one hash per model and locale.
- `ext-redis` and `mongodb/mongodb` are no longer required; they are suggested for their drivers.
- Models are identified by `getMorphClass()`, which respects the morph map.
- The trait boots with `bootTranslatable()` instead of overriding the model's `boot()` method.
- The migration stub is an anonymous class with a unique index instead of a plain index.

### Fixed
- Translations loaded by the database driver were arrays and could not be applied to the model.
- The Redis driver overwrote every translation with the original values whenever the model was saved.
- The Redis driver failed to authenticate when no username/password was configured.
- The MongoDB driver ignored the column when reading or updating, used `foreign_id` and `foreignId` interchangeably,
  inserted all translations as a single document and sent updates without `$set`.
- Retrieving a model no longer runs a schema query, and translated models are no longer reported as dirty.
- The facade no longer shares a single stateful instance between calls.
