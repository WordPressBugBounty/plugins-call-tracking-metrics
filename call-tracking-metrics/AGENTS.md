# Repository Guidelines

## Project Structure & Module Organization
- `call-tracking-metrics.php` is the WordPress plugin entry point and hooks into WordPress.
- `src/` contains PHP source code organized by namespace (e.g., `CTM\Service`, `CTM\Admin`).
- `tests/` holds PHPUnit tests and helpers (see `tests/Traits/`).
- `views/`, `assets/`, `css/`, `js/`, and `languages/` contain admin UI templates, static assets, and translations.
- `vendor/` is Composer-managed dependencies; keep it in sync with `composer.lock` when committing.

## Build, Test, and Development Commands
- `composer install` installs PHP dependencies and autoloaders.
- `vendor/bin/phpunit` runs the PHPUnit test suite defined in `phpunit.xml`.
- `vendor/bin/phpstan analyse -c phpstan.neon --debug` runs static analysis (use `--debug` to avoid parallel worker issues).
- `./build-zip.sh` creates a production-ready plugin zip by stripping dev files.
- `./deploy.sh` builds a cleaned package and prints SVN deploy instructions.

## Coding Style & Naming Conventions
- PHP uses 4-space indentation and docblocks for public APIs.
- Namespaces follow PSR-4: `CTM\` for production code and `CTM\Tests\` for tests.
- Class/file names are PascalCase and should match (e.g., `src/Service/ApiService.php`).

## Testing Guidelines
- Framework: PHPUnit (see `composer.json` and `phpunit.xml`).
- Test files end with `Test.php`; add new tests under `tests/` and use the `CTM\Tests` namespace.
- Run targeted tests with `vendor/bin/phpunit tests/ApiServiceTest.php`.

## Commit & Pull Request Guidelines
- Commits are short and descriptive (e.g., `fix cf7 email issue ref PRODTEC-454`, `Bump version to 2.0.2`).
- Include version bumps or release notes when appropriate.
- PRs should include: a concise summary, testing notes, and screenshots for admin UI changes.

## Configuration Tips
- For local or staging API testing, set `CTM_API_BASE_URL` in `wp-config.php`:
  `define('CTM_API_BASE_URL', 'https://api.ctmdev.us');`
- Avoid committing environment-specific configs or secrets.
