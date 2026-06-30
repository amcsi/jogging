# Laravel & PHP Modernization Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Incrementally upgrade the jogging app from Laravel 5.6 / PHP 7.2 to Laravel 12 / PHP 8.2+ with one commit per framework/PHP version and unchanged Vue 2 API behavior.

**Architecture:** Long-lived `upgrade/laravel-12` branch; GitHub Actions gates every commit; PHP bumps precede the Laravel hop that requires them; Passport/Octane/Node change only when composer or Docker forces it. Existing PHPUnit feature tests are the acceptance suite — run after every hop.

**Tech Stack:** Laravel 5.6→12, PHP 7.2→8.2, Passport, swooletw→Octane (conditional), SQLite, Vue 2 + Laravel Mix 2, GitHub Actions.

**Spec:** `docs/superpowers/specs/2026-06-30-laravel-php-modernization-design.md`

---

## File Map

| File | Role in upgrade |
|------|-----------------|
| `composer.json` / `composer.lock` | Framework and package versions every hop |
| `package.json` | Unchanged unless Node chore commit |
| `Dockerfile` | PHP/Node image; updated in PHP/Node/Octane commits |
| `.github/workflows/ci.yml` | CI PHP version tracks Dockerfile |
| `.travis.yml` | Removed in Task 1 |
| `README.md` | CI badge update |
| `.env.example` | SQLite defaults (Task 2) |
| `phpunit.xml` | Env key renames across hops |
| `routes/api.php` | Class-based routes in Laravel 8 hop |
| `app/Providers/RouteServiceProvider.php` | Laravel 8+ structure |
| `app/Http/Kernel.php` | Middleware renames 5.7–11 |
| `app/Http/Middleware/TrustProxies.php` | Laravel 7: drop fideloper |
| `app/Exceptions/Handler.php` | `Throwable` type hints Laravel 7+ |
| `app/Providers/AuthServiceProvider.php` | Passport boot changes L10+ |
| `database/seeds/DatabaseSeeder.php` | Moves to `database/seeders/` at L8 |
| `database/factories/*.php` | Class-based factories at L8 |
| `app/Http/Controllers/LoginController.php` | Swoole hack removed in Octane task |
| `bin/start.sh` | `swoole:http` → `octane:start` in Octane task |
| `tests/**/*.php` | PHPUnit API fixes per hop |

---

## Shared Commands

**Baseline test run (after every commit):**

```bash
touch database/testing.database.sqlite
composer install --no-interaction
cp .env.testing .env
php artisan key:generate --force
php artisan migrate --force
php artisan passport:install --force
vendor/bin/phpunit
```

Expected: `OK` with 0 failures.

**Composer update for a Laravel hop:**

```bash
composer require laravel/framework:<CONSTRAINT> --no-update
# also bump passport/collision/phpunit in composer.json if needed
composer update --with-all-dependencies
```

**Check if swooletw blocks next hop:**

```bash
composer why-not laravel/framework <NEXT_VERSION>
composer why-not swooletw/laravel-swoole <NEXT_VERSION>
```

---

### Task 0: Create upgrade branch

**Files:**
- Modify: `.gitignore` (if using local worktrees)

- [ ] **Step 1: Add worktree directory to gitignore**

```bash
echo ".worktrees/" >> .gitignore
```

- [ ] **Step 2: Create branch**

```bash
git checkout -b upgrade/laravel-12
```

If using an isolated worktree instead:

```bash
git check-ignore -q .worktrees || echo ".worktrees/" >> .gitignore
git worktree add .worktrees/laravel-12 upgrade/laravel-12
cd .worktrees/laravel-12
```

- [ ] **Step 3: Verify starting point**

```bash
php -v          # 7.2+ acceptable for first hops
composer install
vendor/bin/phpunit
```

Expected: tests pass on current Laravel 5.6.

- [ ] **Step 4: Commit gitignore change (if modified)**

```bash
git add .gitignore
git commit -m "chore: ignore local worktree directory"
```

---

### Task 1: GitHub Actions CI

**Files:**
- Create: `.github/workflows/ci.yml`
- Delete: `.travis.yml`
- Modify: `README.md`

- [ ] **Step 1: Create workflow file**

Create `.github/workflows/ci.yml`:

```yaml
name: CI

on:
  push:
  pull_request:

jobs:
  phpunit:
    runs-on: ubuntu-latest
    steps:
      - uses: actions/checkout@v4

      - name: Setup PHP
        uses: shivammathur/setup-php@v2
        with:
          php-version: '7.2'
          extensions: mbstring, pdo_sqlite
          coverage: none

      - name: Get composer cache directory
        id: composer-cache
        run: echo "dir=$(composer config cache-files-dir)" >> $GITHUB_OUTPUT

      - name: Cache composer dependencies
        uses: actions/cache@v4
        with:
          path: ${{ steps.composer-cache.outputs.dir }}
          key: ${{ runner.os }}-composer-${{ hashFiles('composer.lock') }}
          restore-keys: ${{ runner.os }}-composer-

      - name: Install dependencies
        run: composer install --no-interaction --prefer-dist

      - name: Prepare environment
        run: |
          touch database/testing.database.sqlite
          cp .env.testing .env
          php artisan key:generate --force

      - name: Run migrations and Passport
        run: |
          php artisan migrate --force
          php artisan passport:install --force

      - name: Run tests
        run: vendor/bin/phpunit
```

- [ ] **Step 2: Remove Travis config**

```bash
rm .travis.yml
```

- [ ] **Step 3: Update README badge**

In `README.md`, replace the Travis badge line with:

```markdown
[![CI](https://github.com/amcsi/jogging/actions/workflows/ci.yml/badge.svg)](https://github.com/amcsi/jogging/actions/workflows/ci.yml)
```

(Adjust `amcsi/jogging` if the GitHub org/repo differs.)

- [ ] **Step 4: Run tests locally**

```bash
vendor/bin/phpunit
```

Expected: PASS

- [ ] **Step 5: Commit**

```bash
git add .github/workflows/ci.yml README.md
git rm .travis.yml
git commit -m "ci: add GitHub Actions workflow"
```

---

### Task 2: Align `.env.example` with SQLite

**Files:**
- Modify: `.env.example`

- [ ] **Step 1: Replace database section**

In `.env.example`, replace lines 9–14 with:

```env
DB_CONNECTION=sqlite
# DB_DATABASE defaults to database/database.sqlite (see config/database.php)
```

- [ ] **Step 2: Verify config default**

Confirm `config/database.php` line 16: `'default' => env('DB_CONNECTION', 'sqlite'),` — no change needed.

- [ ] **Step 3: Run tests**

```bash
vendor/bin/phpunit
```

Expected: PASS (no runtime change).

- [ ] **Step 4: Commit**

```bash
git add .env.example
git commit -m "config: align .env.example with SQLite"
```

---

### Task 3: Laravel 5.6 → 5.7

**Files:**
- Modify: `composer.json`, `composer.lock`
- Reference: https://laravel.com/docs/5.7/upgrade

- [ ] **Step 1: Bump framework constraint**

In `composer.json`:

```json
"laravel/framework": "5.7.*",
```

- [ ] **Step 2: Update dependencies**

```bash
composer update laravel/framework --with-all-dependencies
```

If Passport conflicts, bump to compatible 5.x/6.x per composer output.

- [ ] **Step 3: Apply 5.7 breaking changes**

In `app/Http/Kernel.php`, rename middleware if present after update:

```php
\Illuminate\Foundation\Http\Middleware\CheckForMaintenanceMode::class,
```

stays as-is for 5.7 (renamed in later versions).

Run:

```bash
php artisan package:discover
```

- [ ] **Step 4: Run tests**

```bash
vendor/bin/phpunit
```

Expected: PASS

- [ ] **Step 5: Commit**

```bash
git add composer.json composer.lock
git commit -m "laravel: upgrade 5.6 → 5.7"
```

---

### Task 4: Laravel 5.7 → 5.8

**Files:**
- Modify: `composer.json`, `composer.lock`
- Reference: https://laravel.com/docs/5.8/upgrade

- [ ] **Step 1: Bump framework**

```json
"laravel/framework": "5.8.*",
```

- [ ] **Step 2: Update**

```bash
composer update laravel/framework --with-all-dependencies
```

Bump `laravel/passport` if composer requires (likely `^7.0`).

- [ ] **Step 3: Apply 5.8 changes**

In `config/logging.php` (if added by update), keep defaults. Update `phpunit.xml` if `QUEUE_DRIVER` still used — leave for Laravel 6.

In `database/factories/UserFactory.php`, if `str_random()` deprecated warnings appear, defer fix to Laravel 8 factory rewrite.

- [ ] **Step 4: Run tests**

```bash
vendor/bin/phpunit
```

- [ ] **Step 5: Commit**

```bash
git add composer.json composer.lock
git commit -m "laravel: upgrade 5.7 → 5.8"
```

---

### Task 5: Laravel 5.8 → 6.0

**Files:**
- Modify: `composer.json`, `composer.lock`, `.env.example`, `phpunit.xml`
- Reference: https://laravel.com/docs/6.x/upgrade

- [ ] **Step 1: Bump framework and PHP constraint**

```json
"php": "^7.2",
"laravel/framework": "^6.0",
```

- [ ] **Step 2: Update**

```bash
composer update --with-all-dependencies
```

- [ ] **Step 3: Rename env keys**

In `.env.example` and `phpunit.xml`:

- `QUEUE_DRIVER` → `QUEUE_CONNECTION`
- `MAIL_DRIVER` → `MAIL_MAILER`

- [ ] **Step 4: Check swooletw compatibility**

```bash
composer show swooletw/laravel-swoole
```

If still compatible, keep it. If not, insert **Task 5b: Octane** (see Conditional Tasks) before continuing.

- [ ] **Step 5: Run tests**

```bash
vendor/bin/phpunit
```

- [ ] **Step 6: Commit**

```bash
git add composer.json composer.lock .env.example phpunit.xml
git commit -m "laravel: upgrade 5.8 → 6.0"
```

---

### Task 6: Laravel 6.0 → 7.0

**Files:**
- Modify: `composer.json`, `composer.lock`, `app/Http/Middleware/TrustProxies.php`, `app/Exceptions/Handler.php`, `app/Http/Kernel.php`
- Reference: https://laravel.com/docs/7.x/upgrade

- [ ] **Step 1: Bump framework**

```json
"laravel/framework": "^7.0",
```

Remove from `composer.json`:

```json
"fideloper/proxy": "~4.0",
```

- [ ] **Step 2: Update**

```bash
composer update --with-all-dependencies
```

- [ ] **Step 3: Replace TrustProxies**

Replace `app/Http/Middleware/TrustProxies.php`:

```php
<?php

namespace App\Http\Middleware;

use Illuminate\Http\Middleware\TrustProxies as Middleware;
use Illuminate\Http\Request;

class TrustProxies extends Middleware
{
    protected $proxies;

    protected $headers = Request::HEADER_X_FORWARDED_FOR
        | Request::HEADER_X_FORWARDED_HOST
        | Request::HEADER_X_FORWARDED_PORT
        | Request::HEADER_X_FORWARDED_PROTO;
}
```

- [ ] **Step 4: Update Exception Handler signatures**

In `app/Exceptions/Handler.php`, change `Exception` to `Throwable` in `report()` and `render()`:

```php
use Throwable;

public function report(Throwable $exception)
public function render($request, Throwable $exception)
```

Keep `catch (Exception $e)` inside `report()` as `catch (Throwable $e)` if needed.

- [ ] **Step 5: Check swooletw — insert Octane task if blocked**

- [ ] **Step 6: Run tests**

```bash
vendor/bin/phpunit
```

- [ ] **Step 7: Commit**

```bash
git add composer.json composer.lock app/Http/Middleware/TrustProxies.php app/Exceptions/Handler.php
git commit -m "laravel: upgrade 6.0 → 7.0"
```

---

### Task 7: PHP 7.2 → 7.3

**Files:**
- Modify: `Dockerfile`, `.github/workflows/ci.yml`

- [ ] **Step 1: Update Dockerfile base image**

Change line 1:

```dockerfile
FROM php:7.3
```

- [ ] **Step 2: Update CI PHP version**

In `.github/workflows/ci.yml`:

```yaml
php-version: '7.3'
```

- [ ] **Step 3: Verify locally (if PHP 7.3 available) or via Docker**

```bash
docker build -t jogging:test .
vendor/bin/phpunit
```

- [ ] **Step 4: Commit**

```bash
git add Dockerfile .github/workflows/ci.yml
git commit -m "php: upgrade 7.2 → 7.3"
```

---

### Task 8: Laravel 7.0 → 8.0

**Files:**
- Modify: `composer.json`, `composer.lock`, `routes/api.php`, `routes/web.php`, `app/Providers/RouteServiceProvider.php`, `database/seeds/`, `database/factories/`, all test files using `factory()`, `phpunit.xml`
- Reference: https://laravel.com/docs/8.x/upgrade

- [ ] **Step 1: Bump framework and dev deps**

```json
"php": "^7.3",
"laravel/framework": "^8.0",
"nunomaduro/collision": "^5.0",
"phpunit/phpunit": "^9.0",
"facade/ignition": "^2.5"
```

Remove `filp/whoops` if Ignition replaces it. Replace `fzaninotto/faker` with `fakerphp/faker: ^1.9`.

- [ ] **Step 2: Update**

```bash
composer update --with-all-dependencies
```

- [ ] **Step 3: Move seeder**

```bash
mkdir -p database/seeders
mv database/seeds/DatabaseSeeder.php database/seeders/DatabaseSeeder.php
rmdir database/seeds
```

In `database/seeders/DatabaseSeeder.php`, add namespace:

```php
namespace Database\Seeders;

use Illuminate\Database\Seeder;
```

Update `composer.json` autoload — remove `database/seeds` classmap, add:

```json
"Database\\Seeders\\": "database/seeders/"
```

- [ ] **Step 4: Convert factories to classes**

Create `database/factories/UserFactory.php`:

```php
<?php

namespace Database\Factories;

use App\User;
use App\User\Role;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class UserFactory extends Factory
{
    protected $model = User::class;

    public function definition(): array
    {
        return [
            'email' => $this->faker->unique()->safeEmail(),
            'password' => '$2y$10$TKh8H1.PfQx37YgCzwiKb.KjNyWgaHb9cbcoQgdIVFlYg7B77UdFm',
            'remember_token' => Str::random(10),
            'role' => random_int(1, 10) === 10 ? Role::MANAGER : Role::USER,
        ];
    }
}
```

Create `database/factories/JoggingTimeFactory.php` similarly from existing closure factory.

Add to `app/User.php` and `app/JoggingTime.php`:

```php
use Illuminate\Database\Eloquent\Factories\HasFactory;

class User extends Authenticatable
{
    use HasFactory, Notifiable, HasApiTokens;
```

- [ ] **Step 5: Replace `factory()` in tests and seeder**

In all files under `tests/` and `database/seeders/DatabaseSeeder.php`:

```php
// before
factory(User::class)->create();
// after
User::factory()->create();
```

- [ ] **Step 6: Rewrite RouteServiceProvider**

Replace `app/Providers/RouteServiceProvider.php`:

```php
<?php

namespace App\Providers;

use Illuminate\Foundation\Support\Providers\RouteServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Route;

class RouteServiceProvider extends ServiceProvider
{
    public const HOME = '/';

    public function boot()
    {
        $this->routes(function () {
            Route::middleware('web')
                ->group(base_path('routes/web.php'));

            Route::prefix('api')
                ->middleware('api')
                ->group(base_path('routes/api.php'));
        });
    }
}
```

- [ ] **Step 7: Convert routes to class syntax**

Replace `routes/api.php` with:

```php
<?php

use App\Http\Controllers\JoggingTimeController;
use App\Http\Controllers\JoggingTime\JoggingTimeByWeekController;
use App\Http\Controllers\LoginController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth:api')->group(function () {
    Route::put('/jogging-times/{joggingTime}', [JoggingTimeController::class, 'update']);
    Route::delete('/jogging-times/{joggingTime}', [JoggingTimeController::class, 'destroy']);

    Route::get('/users', [UserController::class, 'index']);
    Route::get('/users/me', [UserController::class, 'me']);
    Route::prefix('/users/{user}')->group(function () {
        Route::get('/', [UserController::class, 'show']);
        Route::prefix('jogging-times')->group(function () {
            Route::get('/', [JoggingTimeController::class, 'index']);
            Route::get('/by-week', [JoggingTimeByWeekController::class, 'index']);
            Route::post('/', [JoggingTimeController::class, 'store']);
        });
    });
    Route::put('/users/{user}', [UserController::class, 'update']);
    Route::delete('/users/{user}', [UserController::class, 'destroy']);
});

Route::post('/users', [UserController::class, 'store']);
Route::post('/login', [LoginController::class, 'login']);
Route::post('/login/refresh', [LoginController::class, 'refresh']);
```

Update `routes/web.php` similarly for any string controller refs.

- [ ] **Step 8: Fix Kernel middleware aliases**

In `app/Http/Kernel.php` api group, replace `'bindings'` with:

```php
\Illuminate\Routing\Middleware\SubstituteBindings::class,
```

- [ ] **Step 9: Fix PHPUnit assertions**

In `tests/Feature/Http/Controllers/LoginControllerTest.php`:

```php
self::assertIsString($data['access_token']);
self::assertIsString($data['refresh_token']);
```

Update `tests/TestCase.php` import:

```php
use Illuminate\Testing\TestResponse;
```

- [ ] **Step 10: Check swooletw — Octane task if blocked**

- [ ] **Step 11: Run tests**

```bash
composer dump-autoload
vendor/bin/phpunit
```

- [ ] **Step 12: Commit**

```bash
git add -A
git commit -m "laravel: upgrade 7.0 → 8.0"
```

---

### Task 9: PHP 7.3 → 8.0

**Files:**
- Modify: `Dockerfile`, `.github/workflows/ci.yml`, possibly test files for PHP 8 deprecations

- [ ] **Step 1: Update Dockerfile**

```dockerfile
FROM php:8.0
```

- [ ] **Step 2: Update CI**

```yaml
php-version: '8.0'
```

- [ ] **Step 3: Fix PHP 8 compatibility**

Address any `Required parameter follows optional parameter` or null-to-string errors in app/tests revealed by:

```bash
vendor/bin/phpunit
```

- [ ] **Step 4: Docker build**

```bash
docker build -t jogging:test .
```

- [ ] **Step 5: Commit**

```bash
git add Dockerfile .github/workflows/ci.yml
git commit -m "php: upgrade 7.3 → 8.0"
```

---

### Task 10: Laravel 8.0 → 9.0

**Files:**
- Modify: `composer.json`, `composer.lock`, `app/Http/Kernel.php`
- Reference: https://laravel.com/docs/9.x/upgrade

- [ ] **Step 1: Bump constraints**

```json
"php": "^8.0",
"laravel/framework": "^9.0",
"laravel/passport": "^10.0",
"nunomaduro/collision": "^6.0",
```

- [ ] **Step 2: Update**

```bash
composer update --with-all-dependencies
```

- [ ] **Step 3: Apply 9.x changes**

In `app/Http/Kernel.php`, if `CheckForMaintenanceMode` exists, replace with:

```php
\Illuminate\Foundation\Http\Middleware\PreventRequestsDuringMaintenance::class,
```

In `app/Http/Middleware/TrustProxies.php`, ensure `$proxies` property type hint if needed.

Remove `symfony/thanks` from dev if composer flags it.

- [ ] **Step 4: Run tests**

```bash
vendor/bin/phpunit
```

- [ ] **Step 5: Commit**

```bash
git add composer.json composer.lock app/Http/Kernel.php
git commit -m "laravel: upgrade 8.0 → 9.0"
```

---

### Task 11: PHP 8.0 → 8.1

**Files:**
- Modify: `Dockerfile`, `.github/workflows/ci.yml`

- [ ] **Step 1: Update Dockerfile to `FROM php:8.1`**

- [ ] **Step 2: Update CI to `php-version: '8.1'`**

- [ ] **Step 3: Verify**

```bash
vendor/bin/phpunit
docker build -t jogging:test .
```

- [ ] **Step 4: Commit**

```bash
git add Dockerfile .github/workflows/ci.yml
git commit -m "php: upgrade 8.0 → 8.1"
```

---

### Task 12: Laravel 9.0 → 10.0

**Files:**
- Modify: `composer.json`, `composer.lock`, `app/Providers/AuthServiceProvider.php`, `phpunit.xml`
- Reference: https://laravel.com/docs/10.x/upgrade

- [ ] **Step 1: Bump constraints**

```json
"php": "^8.1",
"laravel/framework": "^10.0",
"laravel/passport": "^11.0",
"phpunit/phpunit": "^10.0",
"nunomaduro/collision": "^7.0",
```

- [ ] **Step 2: Update**

```bash
composer update --with-all-dependencies
```

- [ ] **Step 3: AuthServiceProvider**

Remove `$this->registerPolicies();` from `boot()` — policy auto-discovery handles it.

Keep `Passport::routes();` unless upgrade guide says otherwise for Passport 11.

- [ ] **Step 4: Update phpunit.xml**

Replace `<filter><whitelist>` with:

```xml
<source>
    <include>
        <directory suffix=".php">./app</directory>
    </include>
</source>
```

- [ ] **Step 5: Run tests**

```bash
vendor/bin/phpunit
```

- [ ] **Step 6: Commit**

```bash
git add composer.json composer.lock app/Providers/AuthServiceProvider.php phpunit.xml
git commit -m "laravel: upgrade 9.0 → 10.0"
```

---

### Task 13: PHP 8.1 → 8.2

**Files:**
- Modify: `Dockerfile`, `.github/workflows/ci.yml`

- [ ] **Step 1: Update Dockerfile to `FROM php:8.2`**

- [ ] **Step 2: Update CI to `php-version: '8.2'`**

- [ ] **Step 3: Verify**

```bash
vendor/bin/phpunit
docker build -t jogging:test .
```

- [ ] **Step 4: Commit**

```bash
git add Dockerfile .github/workflows/ci.yml
git commit -m "php: upgrade 8.1 → 8.2"
```

---

### Task 14: Laravel 10.0 → 11.0

**Files:**
- Modify: `composer.json`, `composer.lock`, `bootstrap/app.php` (if scaffolded), `app/Http/Kernel.php`, `routes/console.php`
- Reference: https://laravel.com/docs/11.x/upgrade

- [ ] **Step 1: Bump constraints**

```json
"php": "^8.2",
"laravel/framework": "^11.0",
"laravel/passport": "^12.0",
"phpunit/phpunit": "^10.5",
"nunomaduro/collision": "^8.0",
```

- [ ] **Step 2: Update**

```bash
composer update --with-all-dependencies
```

- [ ] **Step 3: Apply 11.x skeleton changes**

Follow official guide. Prefer minimal diff: keep `app/Http/Kernel.php` if Laravel allows, or migrate middleware to `bootstrap/app.php` as the guide recommends.

Update `bootstrap/app.php` only if `artisan` fails after update.

- [ ] **Step 4: Passport password grant check**

Run `tests/Feature/Http/Controllers/LoginControllerTest.php`. If password grant fails, in `AuthServiceProvider::boot()` add:

```php
Passport::enablePasswordGrant();
```

If that method does not exist, refactor `LoginController` to issue tokens via `Passport::token()` or personal access token factory while preserving response JSON keys `access_token` and `refresh_token`.

- [ ] **Step 5: Run full test suite**

```bash
vendor/bin/phpunit
```

- [ ] **Step 6: Commit**

```bash
git add -A
git commit -m "laravel: upgrade 10.0 → 11.0"
```

---

### Task 15: Laravel 11.0 → 12.0

**Files:**
- Modify: `composer.json`, `composer.lock`
- Reference: https://laravel.com/docs/12.x/upgrade

- [ ] **Step 1: Bump constraints**

```json
"laravel/framework": "^12.0",
"laravel/passport": "^13.0",
```

(Use exact latest compatible Passport per `composer update` output.)

- [ ] **Step 2: Update**

```bash
composer update --with-all-dependencies
```

- [ ] **Step 3: Apply 12.x guide changes**

Fix any renamed config keys or removed helpers flagged by tests.

Re-run password grant check from Task 14 if needed.

- [ ] **Step 4: Final verification**

```bash
vendor/bin/phpunit
docker build -t jogging:test .
npm run production
```

Expected: all pass; `public/js/app.js` and `public/css/app.css` built.

- [ ] **Step 5: Commit**

```bash
git add composer.json composer.lock
git commit -m "laravel: upgrade 11.0 → 12.0"
```

---

## Conditional Task A: Replace swooletw with Octane

**Insert before the first Laravel hop where `composer update` fails on `swooletw/laravel-swoole`.**

**Files:**
- Modify: `composer.json`, `bin/start.sh`, `Dockerfile`, `app/Http/Controllers/LoginController.php`
- Delete: swoole-specific config if present
- Create: `config/octane.php` (via artisan)

- [ ] **Step 1: Remove swooletw, add Octane**

```bash
composer remove swooletw/laravel-swoole
composer require laravel/octane
php artisan octane:install --server=swoole
```

- [ ] **Step 2: Update start script**

In `bin/start.sh`:

```bash
#!/bin/bash
php artisan octane:start --server=swoole --host=0.0.0.0 --port=1215
```

(Use the port from existing swoole config if different.)

- [ ] **Step 3: Simplify LoginController**

Remove `dispatchRequest()` workaround; use standard route dispatch:

```php
private function dispatchRequest(Request $proxy)
{
    return app()->handle($proxy);
}
```

Or inline `return app()->handle($proxy);` in `login()` and `refresh()`.

- [ ] **Step 4: Run tests**

```bash
vendor/bin/phpunit
```

- [ ] **Step 5: Docker build and smoke test**

```bash
docker build -t jogging:test .
```

- [ ] **Step 6: Commit**

```bash
git add -A
git commit -m "runtime: replace swooletw with Laravel Octane (Swoole)"
```

---

## Conditional Task B: Upgrade Node.js for build tooling

**Insert only when `npm install` or `npm run production` fails in Docker.**

**Files:**
- Modify: `Dockerfile`, possibly `package.json`

- [ ] **Step 1: Bump Node in Dockerfile**

Replace:

```dockerfile
curl -sL https://deb.nodesource.com/setup_10.x | bash -
```

with:

```dockerfile
curl -fsSL https://deb.nodesource.com/setup_16.x | bash -
```

- [ ] **Step 2: Remove spurious dependencies from package.json if install fails**

Remove from `"dependencies"`:

```json
"npm": "^5.6.0",
"install": "^0.10.4"
```

- [ ] **Step 3: Verify build**

```bash
docker build -t jogging:test .
```

- [ ] **Step 4: Commit**

```bash
git add Dockerfile package.json package-lock.json
git commit -m "chore: upgrade Node.js for build tooling"
```

---

## Spec Coverage Checklist

| Spec requirement | Task |
|------------------|------|
| GitHub Actions CI | Task 1 |
| `.env.example` SQLite | Task 2 |
| 9 Laravel hops | Tasks 3–6, 8, 10, 12, 14, 15 |
| 4 PHP hops | Tasks 7, 9, 11, 13 |
| Passport as-needed | Within Laravel tasks per composer |
| Octane when blocked | Conditional Task A |
| Node when blocked | Conditional Task B |
| Vue 2 unchanged | No Vue tasks |
| One commit per version | Each task ends with commit |
| Docker on PHP hops | Tasks 7, 9, 11, 13 |
