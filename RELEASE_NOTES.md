# 7x Symfony Framework v1.5 — PHP 8.5 Support Release

**Release:** `v1.5.0.0`  
**Branch:** `1.5`  
**Date:** 2026-05-09  
**Maintainer:** [7x](https://se7enx.com) — [info@se7enx.com](mailto:info@se7enx.com)  
**Repository:** https://github.com/se7enxweb/symfony1

---

## What is 7x Symfony Framework (v1.5)?

**7x Symfony Framework v1.5** is the continuing evolution of the original [symfony 1.4](https://symfony.com/legacy) PHP framework, maintained by [7x (se7enxweb)](https://se7enx.com). Where the upstream project stopped at PHP 5.x / PHP 7.x compatibility, this branch picks up the torch and carries the framework forward into the PHP 8.x era.

symfony1 remains one of the most architecturally coherent PHP frameworks ever written. Its convention-over-configuration philosophy, composable action-filter-view pipeline, and developer-facing APIs are still sound and productive. This release ensures that organisations running PHP 8.0 through PHP 8.5 can continue to operate their symfony1 applications without migrating to a completely different framework.

---

## PHP 8.x Support — What Changed and Why It Matters

PHP 8.0 was released in November 2020 and introduced the most breaking-change-dense release since PHP 5→7. PHP 8.1, 8.2, 8.4, and 8.5 each added further strictness. This release addresses every incompatibility found across the symfony1 core library.

### 1. Removed: `create_function()` and `preg_replace()` `/e` modifier

**PHP versions:** deprecated 7.2 / removed 8.0 (create_function); deprecated 5.5 / removed 7.0 (/e)

Both APIs were runtime `eval()` in disguise — they compiled and executed arbitrary PHP strings at request time. PHP removed them because:

- They are a **security liability**: user-supplied data reaching the argument of `create_function()` or the replacement string of a `/e` regex becomes executed code.
- They defeat **static analysis**: PHPStan, Psalm, and IDEs cannot inspect, type-check, or refactor code inside a string.
- They block **JIT compilation**: PHP 8's JIT compiler can only optimise code it can see at parse time. A closure defined in source is JIT-compiled; a string-built function is not.

**What was done:** Every `create_function()` call across 11 framework files was replaced with a proper anonymous closure. Every `/e` modifier across 6 files was replaced with `preg_replace_callback()`. The result is faster, safer, and fully analysable code.

### 2. Removed: Curly-brace string/array offset access `$str{n}`

**PHP versions:** deprecated 7.4 / **fatal error in 8.0**

```php
// PHP 7 (works but deprecated)
$char = $string{0};

// PHP 8+ (fatal parse error — application dies)
// Must use bracket notation:
$char = $string[0];
```

This was not just a notice — PHP 8 refuses to parse files containing curly-brace offsets. Any file not updated would **kill the entire request** with a `ParseError`. Fixed across all template and library files.

### 3. Removed: `get_magic_quotes_gpc()`

**PHP versions:** deprecated 7.4 / removed 8.0

`magic_quotes` auto-escaped user input in PHP 4 as a naive security measure. It was disabled by default since PHP 5.4 (2012) and removed entirely in PHP 8.0. The `get_magic_quotes_gpc()` function that queried its state was also removed. `sfWebRequest` called this function on every HTTP request:

```php
// BEFORE — fatal on PHP 8.0: "Call to undefined function"
$value = get_magic_quotes_gpc() ? stripslashes($_GET[$key]) : $_GET[$key];

// AFTER — direct access, no dead branch
$value = $_GET[$key];
```

Removing the dead branch also means one fewer function call per request variable read.

### 4. Fixed: Implicit nullable parameter declarations (PHP 8.4 deprecated)

```php
// PHP 8.4 E_DEPRECATED — "implicitly marking parameter as nullable is deprecated"
function foo(string $x = null) {}

// PHP 8.4+ correct form
function foo(?string $x = null) {}
```

**50+ method signatures** across the framework were updated. On PHP 8.5 the notices flood application error logs; on PHP 9 this will become a fatal error. The fix is strictly additive — no calling code changes.

### 5. Fixed: Non-canonical type cast syntax

PHP 8.0 deprecated the historical long-form cast aliases:

| Old (deprecated) | New (canonical) |
|-----------------|-----------------|
| `(boolean)` | `(bool)` |
| `(integer)` | `(int)` |
| `(double)` | `(float)` |

Hundreds of occurrences normalised throughout the framework. This eliminates deprecation log entries on PHP 8.0+ and aligns with every modern PHP coding standard (PSR-12, Symfony CS, Laravel Pint).

### 6. Fixed: Deprecated string interpolation syntax (PHP 8.2)

```php
// PHP 8.2 E_DEPRECATED
echo "Hello ${name}!";   // ambiguous dollar-brace form

// Correct — unambiguous brace-dollar form
echo "Hello {$name}!";
```

### 7. Fixed: Invalid static property declaration order

PHP 8.0 enforced correct visibility-modifier ordering for class properties:

```php
// BEFORE — parse error in PHP 8
static protected $active = null;
protected ?static $dispatcher = null;

// AFTER — visibility first, then static
protected static $active = null;
protected static $dispatcher = null;
```

### 8. Fixed: `count()` on non-Countable (PHP 7.2+ warning, PHP 8 TypeError)

`sfFormField::hasError()` called `count()` on an `sfValidatorError` object that does not implement `Countable`. PHP 8.0 promotes this to a `TypeError`. Fixed with an explicit type guard.

### 9. Fixed: Loose comparison semantics change (PHP 8.0)

```php
// PHP 7
0 == 'invalid'  // true  (string coerced to 0)

// PHP 8.0+
0 == 'invalid'  // false (string compared as string, not coerced)
```

This is one of PHP 8's most impactful correctness improvements — comparisons that returned surprising results for 20 years now behave intuitively. Test assertions that depended on the old behavior were updated.

---

## New: Composer Package Manager Integration

symfony1 plugins were the historical answer to third-party code. This release adds a `composer.json` that enables the modern Packagist ecosystem alongside the traditional plugin system:

```bash
# Install any of 400,000+ PHP packages:
composer require monolog/monolog
composer require league/flysystem
composer require guzzlehttp/guzzle
```

Both sfCoreAutoload and Composer's PSR-4/classmap loader coexist via PHP's `spl_autoload` stack. Projects that do not use Composer are completely unaffected — the bootstrap guard `if (is_file('vendor/autoload.php'))` ensures zero impact when `vendor/` does not exist.

---

## New: Live Test-Install Site

A reference symfony1 application is available for local installation demonstrating:

- Full symfony1 routing → action → view → layout pipeline on PHP 8.5
- Real framework classes: `sfPatternRouting`, `sfRoute`, `sfInflector`, `sfParameterHolder`, `sfView`
- Composer integration status dashboard
- 8 core class availability health checks

---

## Commits in this Release

| SHA | Description |
|-----|-------------|
| `e07a564` | feat(php8): replace eval-based patterns — remove create_function() and preg_replace /e modifier |
| `38ab996` | feat(php8): fix type system and syntax incompatibilities for PHP 8.x |
| `1eee05c` | feat(php8): update plugin admin list templates for PHP 8 compatibility |
| `a015cc0` | test(php8): update test suite for PHP 8 behavioral changes |
| `13ceca3` | feat(composer): integrate Composer package manager alongside sfCoreAutoload |
| `490728e` | feat(app): add symfony1-style front controller and live test-install site |
| `3a03509` | chore: exclude Composer vendor dir and lock file from version control |

---

## Files Changed

- **62 framework library files** in `lib/` — PHP 8.x compatibility fixes
- **3 test files** in `test/unit/` — test suite behavioral alignment
- **9 new files** in `apps/site/` — reference application
- `index.php` — web front controller
- `composer.json` — Composer manifest
- `.htaccess` — Apache rewrite rules
- `.gitignore` — Composer artifact exclusions

---

## Upgrade Guide

### From symfony 1.4 (branch 1.4)

1. Switch to branch `1.5`:
   ```bash
   git fetch origin
   git checkout 1.5
   ```

2. Ensure PHP 8.0 or later is active:
   ```bash
   php -v  # must be >= 8.0
   ```

3. (Optional) Install Composer packages:
   ```bash
   composer install
   ```

4. No changes to your application code are required. The fixes in this
   release are all in the framework library (`lib/`) and are transparent
   to application-level code.

### Minimum Requirements

| Requirement | Version |
|-------------|---------|
| PHP | **>= 8.0** (tested through 8.5.6) |
| Web server | Apache 2.4 / Nginx (any modern version) |
| Composer | >= 2.0 (optional) |

---

## Copyright

```
(c) 2004-2026 7x <info@se7enx.com>
(c) 2004-2006 Fabien Potencier <fabien.potencier@symfony-project.com>
(c) 2004-2006 Sean Kerr <sean@code-box.org>
```

Licensed under the MIT License. See [LICENSE](LICENSE) for details.
