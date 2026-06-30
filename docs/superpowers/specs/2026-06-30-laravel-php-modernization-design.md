# Laravel & PHP Modernization Design

**Date:** 2026-06-30  
**Status:** Approved (brainstorming)  
**Target:** Laravel 12 on PHP 8.2+

## Summary

Incrementally upgrade the jogging app from **Laravel 5.6 / PHP 7.2** to **Laravel 12 / PHP 8.2+**, with one git commit per Laravel version and one per PHP version. Vue 2 and Laravel Mix stay unchanged unless Node.js must be bumped for Docker builds. Passport upgrades only when forced by a Laravel hop. Replace `swooletw/laravel-swoole` with Laravel Octane (Swoole driver) only when the package blocks the next upgrade. Add GitHub Actions CI early. Keep SQLite; update `.env.example` to match.

## Goals

1. End on Laravel 12 and PHP 8.2+ with a passing test suite.
2. Minimal scope: no Vue 3, no auth redesign, no database portability work.
3. Traceable history: each Laravel minor/major and each PHP bump is its own commit.
4. Working CI on GitHub Actions before the heavy upgrade chain.
5. API behavior unchanged for the existing Vue 2 SPA.

## Non-Goals

- Vue 3 migration or Mix → Vite
- Sanctum migration (unless Passport becomes impossible — requires separate decision)
- MySQL/Postgres portability or rewriting SQLite-specific weekly aggregation SQL
- Rewriting transformers, policies, or API design
- New features or UI changes
- Production deployment

## Current State

| Component | Version |
|-----------|---------|
| Laravel | 5.6.40 |
| PHP (composer) | >=7.1.3 |
| PHP (Docker/CI) | 7.2 |
| Passport | 5.0.3 |
| HTTP server | swooletw/laravel-swoole 2.7.0 |
| Frontend | Vue 2.5 + Laravel Mix 2 |
| Node (Docker) | 10.x |
| Database | SQLite (dev/CI/Docker) |
| CI | Travis CI (discontinued) |
| Tests | 7 PHPUnit files |

Small API app: 5 controllers, 2 models, 2 policies, ~4,300 LOC PHP.

## Approach

**Strict sequential manual upgrades** following [official Laravel upgrade guides](https://laravel.com/docs/upgrade) one version at a time. This matches the commit discipline, keeps bisect useful, and is manageable given the small codebase.

Alternatives considered and rejected:

- **Automated-assisted (Laravel Shift):** faster but paid; still needs manual fixes for Passport, Swoole, SQLite, custom `LoginController`.
- **Jump to intermediate baseline:** violates one-commit-per-version rule.

## Commit Strategy

### Rules

- One commit per Laravel version hop.
- One commit per PHP version hop.
- Never combine Laravel and PHP bumps in a single commit. If both are needed, PHP commit first.
- Passport and dev-dependency bumps ride along inside the Laravel commit that requires them.
- Octane migration and Node bumps get their own commits when triggered.

### Setup Commits

| # | Commit | Content |
|---|--------|---------|
| 1 | `ci: add GitHub Actions workflow` | Replace Travis; PHPUnit on SQLite |
| 2 | `config: align .env.example with SQLite` | `DB_CONNECTION=sqlite`; remove MySQL defaults |

### PHP Commits (before the Laravel hop that requires them)

| Commit | Triggered by | Updates |
|--------|--------------|---------|
| `php: upgrade 7.2 → 7.3` | Laravel 8 requires ≥7.3 | `Dockerfile`, CI PHP version |
| `php: upgrade 7.3 → 8.0` | Laravel 9 requires ≥8.0 | Dockerfile, CI, PHP 8 fixes in app/tests |
| `php: upgrade 8.0 → 8.1` | Laravel 10 requires ≥8.1 | Dockerfile, CI |
| `php: upgrade 8.1 → 8.2` | Laravel 11 requires ≥8.2 | Dockerfile, CI |

Laravel 12 also requires PHP 8.2 — no additional PHP commit.

### Laravel Commits (in order)

```
5.6 → 5.7 → 5.8 → 6.0 → 7.0 → 8.0 → 9.0 → 10.0 → 11.0 → 12.0
```

Each commit: bump `laravel/framework` and forced dependencies, apply that version's breaking changes only, green PHPUnit, green CI.

### Conditional Commits

| Commit | When |
|--------|------|
| `runtime: replace swooletw with Laravel Octane (Swoole)` | When `swooletw/laravel-swoole` cannot support the next Laravel version |
| `chore: upgrade Node.js for build tooling` | When `npm install` or `npm run production` fails in Docker |

### Full Ordered Timeline

```
 1. ci: add GitHub Actions workflow
 2. config: align .env.example with SQLite
 3. laravel: upgrade 5.6 → 5.7
 4. laravel: upgrade 5.7 → 5.8
 5. laravel: upgrade 5.8 → 6.0
 6. laravel: upgrade 6.0 → 7.0
 7. php: upgrade 7.2 → 7.3
 8. laravel: upgrade 7.0 → 8.0
 9. php: upgrade 7.3 → 8.0
10. laravel: upgrade 8.0 → 9.0
11. php: upgrade 8.0 → 8.1
12. laravel: upgrade 9.0 → 10.0
13. php: upgrade 8.1 → 8.2
14. laravel: upgrade 10.0 → 11.0
15. laravel: upgrade 11.0 → 12.0

Conditional (insert at first blocked hop):
 ?. runtime: replace swooletw with Laravel Octane (Swoole)
 ?. chore: upgrade Node.js for build tooling
```

Minimum ~15 commits, plus 0–2 conditional.

## Package Migration

### Passport

**Policy:** bump `laravel/passport` only when `composer update` for a Laravel hop requires it.

Current setup:

- `Passport::routes()` in `AuthServiceProvider`
- Password grant via `LoginController` (internal dispatch to `/oauth/token`)
- `CreateFreshApiToken` on web middleware group
- `auth:api` on protected routes

| Laravel hop | Expected Passport action |
|-------------|--------------------------|
| 5.6 → 5.8 | 5.x → 7.x range |
| 6.x → 7.x | 7.x → 9.x |
| 8.x | → Passport 10 |
| 9.x → 10.x | → Passport 11 |
| 11.x → 12.x | → Passport 12/13 (highest risk) |

**Passport 11+ risk:** password grant deprecated/removed from core Passport. Mitigation order:

1. Enable password grant via documented opt-in if available for that version.
2. If not, replace internal OAuth dispatch with direct token issuance while keeping `/api/login` and `/api/login/refresh` API contract unchanged.

`LoginController` Swoole hack is only for `swooletw`; remove in Octane commit, not during Passport bumps.

Per-hop: run `passport:install` in CI; run `LoginControllerTest`.

### Swoole → Octane

**Until blocked:** keep `swooletw/laravel-swoole`, `bin/start.sh` → `php artisan swoole:http start`, and `LoginController::dispatchRequest()` workaround.

**Trigger:** `composer require laravel/framework:<next>` conflicts with `swooletw/laravel-swoole`.

| Change | Detail |
|--------|--------|
| Remove | `swooletw/laravel-swoole`, unused `routes/websocket.php` stubs |
| Add | `laravel/octane` |
| Start command | `php artisan octane:start --server=swoole` |
| Dockerfile | Swoole via PECL; verify Octane extension requirements |
| LoginController | Drop route-container hack; test login/refresh under Octane |
| Config | Publish `config/octane.php`; remove swoole-specific config |

Evaluate at each hop with `composer why-not`; likely around Laravel 7–9.

### Frontend & Node

**Frozen:** Vue 2.5.x, vue-router 3, bootstrap-vue, Laravel Mix 2, `resources/assets/` structure.

**Allowed:** Node.js Docker image bump only when `npm install` or `npm run production` fails.

- Separate commit: `chore: upgrade Node.js for build tooling`
- Likely trigger: Node 10 → 14 or 16
- Optional cleanup: remove accidental `"npm"` and `"install"` from `package.json` dependencies if they cause install issues

No Mix → Vite. CDN assets in `welcome.blade.php` stay unless a scaffold change forces otherwise.

### Dev/Test Dependencies

Bundled into the Laravel commit that requires them:

| Package | Modernization path |
|---------|-------------------|
| PHPUnit 7.x | → 8 → 9 → 10 → 11 across hops |
| `fzaninotto/faker` | → `fakerphp/faker` |
| `fideloper/proxy` | → built-in `TrustProxies` (Laravel 7+) |
| `database/seeds`, `factory()` | → `database/seeders`, class-based factories (Laravel 8+) |

## App-Level Breaking Changes by Phase

Changes land in the Laravel commit that introduces them.

**Laravel 5.7 / 5.8:** minor deprecations; Passport bump if required.

**Laravel 6:** `MAIL_DRIVER` → `MAIL_MAILER`, `QUEUE_DRIVER` → `QUEUE_CONNECTION` in env files when expected.

**Laravel 7:** replace `fideloper/proxy`; `Exception` → `Throwable` in `Handler.php`.

**Laravel 8 (largest hop):**

- Route syntax: `'UserController@index'` → `[UserController::class, 'index']`
- `RouteServiceProvider`: drop `$namespace` + `map()` → `boot()` + `Route::middleware(...)->group(...)`
- `database/seeds` → `database/seeders/DatabaseSeeder.php`
- Class-based model factories
- PHPUnit 9+ adjustments

**Laravel 9:** Symfony mailer, flysystem 3 if touched; remove PHP 7.x workarounds.

**Laravel 10:** `registerPolicies()` auto-discovery; `Passport::routes()` changes if required; PHPUnit 10.

**Laravel 11:** slimmer skeleton (`bootstrap/app.php` routing/middleware); migrate or keep `Kernel.php` for smaller diff.

**Laravel 12:** follow official 11→12 guide; dependency and minor API cleanups.

**Out of scope:** `JoggingTimeByWeekController` SQLite SQL; API response shape changes.

## Configuration

### `.env.example` (setup commit)

```env
DB_CONNECTION=sqlite
# DB_DATABASE defaults to database/database.sqlite per config/database.php
```

Remove MySQL host/user/password defaults. Align `phpunit.xml` env vars when Laravel hops rename them.

## CI/CD

### GitHub Actions (commit 1)

Replace `.travis.yml` with `.github/workflows/ci.yml`. Remove Travis config; update README badge.

**Triggers:** `push` and `pull_request` on all branches.

**Steps:**

1. Checkout
2. Setup PHP (version tracks Dockerfile minimum; update in PHP commits)
3. `composer install --no-interaction --prefer-dist`
4. `touch database/testing.database.sqlite`
5. Configure env (`APP_ENV=testing`, fixed `APP_KEY`, SQLite database)
6. `php artisan key:generate`
7. `php artisan migrate --force`
8. `php artisan passport:install --force`
9. `vendor/bin/phpunit`

**Not in CI initially:** Swoole/Octane server start, `npm run production`. Add Node build step only if a hop requires it.

**Caching:** Composer cache keyed on `composer.lock`.

### Branch Strategy

Work on a long-lived branch (e.g. `upgrade/laravel-12`). Optional draft PR for CI visibility. Preserve per-version commits at merge (no squash).

## Docker Policy

Updated only in PHP commits, Octane commit, or Node commit (if forced).

**PHP image progression:** `php:7.2` → `7.3` → `8.0` → `8.1` → `8.2`

**Node:** stay on 10.x until `npm install` fails; then separate chore commit.

**Laravel-only commits:** no Dockerfile change unless build breaks.

## Verification (Every Commit)

| Step | Laravel | PHP | Octane | CI setup |
|------|---------|-----|--------|----------|
| `composer install` | ✓ | ✓ | ✓ | ✓ |
| `vendor/bin/phpunit` | ✓ | ✓ | ✓ | ✓ |
| `docker build` | optional | ✓ | ✓ | — |
| `npm run production` | only if FE tooling changed | only if Node bumped | — | — |

**Laravel hop:** read upgrade guide → bump framework → apply breaking changes → `composer update` → phpunit green → CI green → commit.

**PHP hop:** bump Dockerfile + CI → fix compatibility → phpunit + docker build green → commit.

## Risks & Mitigations

| Risk | Likelihood | Mitigation |
|------|------------|------------|
| `swooletw/laravel-swoole` blocks upgrade | High | Octane commit when composer conflicts |
| Passport password grant removed | Medium (L11–12) | Keep API contract; opt-in or direct token issuance |
| `LoginController` dispatch breaks | Medium | `LoginControllerTest` in every auth-affecting hop |
| Laravel 8 hop is large | Certain | Dedicated commit; full test suite before continuing |
| PHPUnit API removals | Certain | Fix in commit that bumps PHPUnit |
| Node 10 fails on newer images | Low–medium | Separate chore commit |
| SQLite SQL breaks | Low | Fix only if framework change affects it |

**Rollback:** each commit independently revertible. Do not skip versions.

## Success Criteria

1. `laravel/framework` is 12.x on PHP 8.2+
2. GitHub Actions CI passes
3. `vendor/bin/phpunit` passes locally
4. `docker build` succeeds; app starts via `bin/start.sh`
5. Vue 2 frontend builds with `npm run production`
6. API endpoints, JSON shapes, and auth flow unchanged for SPA
7. `.env.example` reflects SQLite
8. Separate git commits for each Laravel version, each PHP version, CI setup, and Octane (if needed)
