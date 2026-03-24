# Repository Guidelines

## Project Structure & Module Organization
- `src/` houses the package entry points (`LaravelModelTranslate.php`, facade, service provider) plus feature-specific folders such as `Drivers/` (Database, MongoDB, Redis connectors), `Models/`, and `Traits/Translatable.php`. Mirror this namespace layout when adding new services or helpers.
- `config/config.php` is the single source of truth for supported locales and driver wiring; update env key documentation alongside any config change. Ship new configuration defaults via this file instead of hard-coding values elsewhere.
- `database/migrations/create_translations_table.php.stub` defines the publishable schema that consumer apps copy into their projects. When altering the stub, provide upgrade notes in `README.md` and guard against breaking existing translation tables.
- `tests/` contains Testbench-powered suites—create directories that match the production namespace (`Onurkacmaz\LaravelModelTranslate\Tests\...`) to keep autoloading consistent.

## Build, Test, and Development Commands
- `composer install` (once) and `composer dump-autoload` (after namespace changes) keep dependencies synced.
- `composer test` runs `vendor/bin/phpunit` against the Testbench harness; use it before every push.
- `composer test-coverage` produces an HTML report in `coverage/`; review translation-driver diff coverage before opening a PR.
- For manual package validation inside a Laravel sandbox, publish assets with `php artisan vendor:publish --provider="Onurkacmaz\LaravelModelTranslate\LaravelModelTranslateServiceProvider" --tag=config --tag=migrations` and run `php artisan migrate`.

## Coding Style & Naming Conventions
- Follow PSR-2 (4-space indentation, braces on the next line, camelCase methods, StudlyCase classes). Run `vendor/bin/phpcs` if you have PHP_CodeSniffer installed.
- Keep translation column lists in `getTranslatable()` sorted alphabetically to simplify review diffs.
- Place new drivers under `src/Drivers/<DriverName>.php` and keep file names matched to class names.

## Testing Guidelines
- Prefer PHPUnit with Orchestra Testbench; base your test cases on `Orchestra\Testbench\TestCase` so the service provider boots automatically.
- Name tests after the scenario under test (e.g., `DatabaseDriverTest::it_stores_translation_payloads()`).
- Any new locale logic or driver must include both happy-path and failure-path assertions plus coverage for configuration fallbacks.

## Commit & Pull Request Guidelines
- Match the existing history style: short, imperative, lowercase summaries (`add redis driver`, `fix mongo config`). Squash exploratory commits before sharing.
- Reference related issues in the PR body and describe the behavior change plus testing commands executed (`composer test`, `composer test-coverage`).
- Include config or migration screenshots/snippets when they change so reviewers can confirm publishable assets without checking them out.
