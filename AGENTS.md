# Repository Guidelines

## Project Structure & Module Organization

This repository combines a Laravel 12 backend with a Flutter client. Backend code lives in `app/`, with controllers in `app/Http/Controllers`, models in `app/Models`, services in `app/Services`, and Artisan commands in `app/Console/Commands`. Routes are in `routes/web.php`, `routes/api.php`, and `routes/console.php`. Blade views and Vite assets live in `resources/`; public assets are in `public/`; migrations, factories, and seeders are in `database/`. PHP tests are under `tests/Feature` and `tests/Unit`. The Flutter app is isolated in `cotabrasilis_app/`, with Dart code in `lib/`, assets in `assets/`, and widget tests in `test/`.

## Build, Test, and Development Commands

- `composer install` and `npm install`: install PHP and Vite dependencies.
- `php artisan migrate`: apply migrations after configuring `.env`.
- `composer run dev`: run Laravel, queue listener, logs, and Vite together.
- `npm run build`: build production frontend assets.
- `composer test`: clear config and run PHPUnit.
- `vendor/bin/pint`: format PHP with Laravel Pint.
- `cd cotabrasilis_app && flutter pub get`: install Flutter dependencies.
- `cd cotabrasilis_app && flutter analyze && flutter test`: lint and test Flutter code.

## Coding Style & Naming Conventions

Use spaces and LF line endings as defined in `.editorconfig`; PHP files use 4-space indentation. Keep PHP PSR-12 compatible and run Pint before submitting backend changes. Use PascalCase for PHP classes and Eloquent models, camelCase for methods and properties, and snake_case for migrations and database fields. Format Dart with `dart format`; follow `flutter_lints`, PascalCase widgets/classes, camelCase members, and snake_case file names such as `offer_detail_screen.dart`.

## Testing Guidelines

Add PHPUnit feature tests for HTTP flows and unit tests for isolated services/helpers. Name PHP test classes with a `Test` suffix and methods as `test_*`. PHPUnit uses in-memory SQLite plus array-backed cache, mail, queue, and session drivers. Add Flutter widget tests in `cotabrasilis_app/test`, for example `login_screen_test.dart`.

## Commit & Pull Request Guidelines

This checkout does not include local Git history, so no existing commit convention can be inferred. Use short, imperative commit subjects, optionally scoped, for example `backend: validate rental offer dates`. Pull requests should include a summary, test results, linked issues, migration/config notes, and screenshots or screen recordings for visible UI changes.

## Security & Configuration Tips

Do not commit real credentials from `.env`; update `.env.example` when adding required settings. Review payment, upload, KYC, and notification changes carefully because they affect user trust and external integrations.
