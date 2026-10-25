# Changelog

All notable changes to `laravel-model-translate` will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.1.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [Unreleased]

## [2.0.0] - 2026-10-25

This release contains breaking changes, see [UPGRADE.md](UPGRADE.md).

### Added
- Pluggable storage drivers: `database` (default), `redis` and `mongodb`, selected with the `driver` config option.
  Custom drivers implement `DriverInterface`.
- `deleted` handling: a model's translations are removed when it is deleted (or force deleted when it uses soft deletes).
- `saveTranslation()`, `getTranslations()` and `translateTo()` helpers on the `Translatable` trait.
- `getTranslations()` and `delete()` on `LaravelModelTranslate`; `update()` accepts explicit values.
- `$translatable` property as an alternative to overriding `getTranslatable()`.
- Configurable database connection and table, Redis connection and key prefix, MongoDB collection.
- Support for models with UUID/ULID keys (`foreign_id` is now a string column).
- `InvalidDriverException` for a missing or invalid driver configuration.
- Test suite, GitHub Actions workflows, Laravel Pint, and the usual open source community files.

### Changed
- PHP 8.2 and Laravel 12 or 13 are required; the Laravel components are now declared dependencies.
- `supported_locales` moved from `translatable.supported_locales` to the top level of the config file.
- Updating a model in a translated locale no longer aborts the save: non-translatable columns are saved, only the
  translatable ones are kept out of the model's table.
- Models are identified by `getMorphClass()`, which respects the morph map.
- The trait boots with `bootTranslatable()` instead of overriding the model's `boot()` method.
- `LaravelModelTranslate::update()` returns whether the locale has a translation.
- The migration stub is an anonymous class with a unique index instead of a plain index.
- The facade resolves a new instance for every call instead of sharing one stateful instance.

### Fixed
- Retrieving a model no longer runs a schema query, and translated models are no longer reported as dirty.
- Attributes with casts are stored and restored as their raw values.

## [1.0.0] - 2023-02-20

- Initial release.

[Unreleased]: https://github.com/onurkacmaz/laravel-model-translate/compare/v2.0.0...HEAD
[2.0.0]: https://github.com/onurkacmaz/laravel-model-translate/compare/v1.0.0...v2.0.0
[1.0.0]: https://github.com/onurkacmaz/laravel-model-translate/releases/tag/v1.0.0
