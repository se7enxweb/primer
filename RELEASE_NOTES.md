# 7x Symfony Framework v1.5 — Release Notes

---

## v1.5.0.2 — Security Hardening + Public Directory Web Root

**Release:** `v1.5.0.2`  
**Branch:** `1.5`  
**Date:** 2026-05-10  
**Maintainer:** [7x](https://se7enx.com) — [info@se7enx.com](mailto:info@se7enx.com)  
**Repository:** https://github.com/se7enxweb/symfony1

---

### Summary

This release delivers **four targeted security fixes** to the framework core and a **structural change
to the web root layout** that eliminates an entire class of information-disclosure risks. All changes
are backward-compatible with existing v1.5.0.x application code.

---

### Security Fixes

#### 1. YAML PHP Object Injection — `!!php/object:` tag disabled (Critical — RCE)

**File:** `lib/yaml/sfYamlInline.php`  
**CWE:** CWE-502 Deserialization of Untrusted Data  
**Severity:** Critical

**What the vulnerability was:**  
The YAML parser honoured the `!!php/object:` tag, which called `unserialize()` on the base64-encoded
payload embedded in the YAML value. If any YAML processed by the application was sourced from
user input, a file upload, a remote API, or any other untrusted source, an attacker could craft a
payload that exploits a PHP "gadget chain" — a sequence of existing classes whose `__wakeup()` or
`__destruct()` methods chain together to achieve arbitrary code execution.

This is the same vector exploited in **CVE-2024-28859** (symfony1 via Swift Mailer gadget chain) and
is one of the most frequently weaponised PHP vulnerabilities of the past decade. The OWASP Top 10
(A08:2021 – Software and Data Integrity Failures) specifically calls out insecure deserialization.

**Why it matters:**  
Gadget-chain exploits require no zero-day. Attackers use public tool sets (e.g. phpggc) to generate
payloads from well-known classes already present in the application's vendor tree. A symfony1
application with Swift Mailer (bundled), Doctrine 1.x, or Propel 1.x in its autoload path is
exploitable without any custom code — the gadget classes ship with the framework itself.

**The fix:**  
The `!!php/object:` case now throws `InvalidArgumentException` unconditionally. PHP object
deserialization from YAML is disabled. Applications that genuinely need to round-trip PHP objects
through YAML must do so explicitly via application-level serialisation under their own controlled
schema, not via a general-purpose YAML tag.

```php
// BEFORE — executes unserialize() on attacker-controlled data
case 0 === strpos($scalar, '!!php/object:'):
    return unserialize(substr($scalar, 13));

// AFTER — blocked
case 0 === strpos($scalar, '!!php/object:'):
    throw new InvalidArgumentException(
        'The !!php/object YAML tag is not allowed for security reasons.'
    );
```

---

#### 2. CSRF Token Timing Attack — Constant-Time Comparison (High)

**File:** `lib/validator/sfValidatorCSRFToken.class.php`  
**CWE:** CWE-208 Observable Timing Discrepancy  
**Severity:** High

**What the vulnerability was:**  
The CSRF token validator compared the submitted token against the expected value using PHP's `!=`
operator, which performs a short-circuit string comparison — it stops as soon as it finds the first
byte that differs. The time taken therefore leaks information about how many leading bytes of the
submitted token match the real token. A sufficiently fast and persistent attacker can exploit this
timing difference to brute-force a CSRF token one character at a time, reducing the search space
from $O(n^{256})$ to $O(n \times 256)$.

**Why it matters:**  
CSRF tokens are the primary defence against Cross-Site Request Forgery attacks (OWASP A01:2021 —
Broken Access Control). A defeated CSRF token means an attacker can trick an authenticated user's
browser into performing any state-changing action — transferring money, changing an email address,
deleting records — simply by getting them to visit a malicious page.

**The fix:**  
Replaced `!=` with PHP's `hash_equals()`, which was specifically added in PHP 5.6.0 to perform
constant-time string comparison regardless of where differences occur.

```php
// BEFORE — timing-leaky
if ($value != $this->getOption('token')) { ... }

// AFTER — constant-time
if (!hash_equals((string) $this->getOption('token'), (string) $value)) { ... }
```

---

#### 3. Weak CSRF Token Generation — MD5 → HMAC-SHA256 (Medium)

**File:** `lib/form/sfForm.class.php`  
**CWE:** CWE-326 Inadequate Encryption Strength  
**Severity:** Medium

**What the vulnerability was:**  
CSRF tokens were generated with `md5($secret . session_id() . get_class($form))`. MD5 is a
cryptographically broken hash function — it is preimage-vulnerable and trivially reversible via
rainbow tables. More critically, the construction used **concatenation** rather than an HMAC, making
it vulnerable to length-extension attacks. An attacker who knows or can guess the session ID and form
class name can forge a valid token if they obtain any MD5 output derived from the same secret.

**Why it matters:**  
CSRF token strength is only as good as the algorithm generating it. A forged CSRF token grants
the same rights as a legitimately obtained one — combined with the timing vulnerability above, a
weak token makes CSRF protection effectively decorative.

**The fix:**  
Replaced `md5()` with `hash_hmac('sha256', ...)`, using the secret as the HMAC key. HMAC-SHA256:
- Is not vulnerable to length-extension attacks (unlike raw concatenation with MD5/SHA1/SHA256)
- Produces 256 bits of output vs MD5's 128 bits
- Uses the secret properly as a cryptographic key rather than a simple prefix

```php
// BEFORE — MD5 concatenation (weak)
return md5($secret . session_id() . get_class($this));

// AFTER — HMAC-SHA256 (strong)
return hash_hmac('sha256', session_id() . get_class($this), $secret);
```

---

#### 4. `eval()` Injection via sfChoiceFormat Set Notation — Input Allowlist (Medium)

**File:** `lib/i18n/sfChoiceFormat.class.php`  
**CWE:** CWE-94 Improper Control of Generation of Code ('Code Injection')  
**Severity:** Medium

**What the vulnerability was:**  
The `{n: expr}` set notation in i18n plural/choice strings allowed arbitrary PHP expressions to be
passed to `eval()` without any character-level validation. The `$set` value from the YAML translation
catalogue was substituted directly as the `eval`'d expression after replacing the token `n` with
`$number`. An attacker who controls translation catalogue content (e.g. via an admin interface, a
writable YAML file, or a YAML injection in a higher-level parser) could inject arbitrary PHP code.

**Why it matters:**  
`eval()` in a web-accessible code path is a direct remote code execution vector. Even when
translation catalogues are not directly user-editable, defence-in-depth requires that `eval()` be
guarded against malicious input at the point of consumption, not only at the point of ingestion.

**The fix:**  
Added a strict allowlist regular expression that only permits digits, the placeholder variable `n`,
whitespace, and arithmetic/comparison/logical operators. Any `$set` expression containing unexpected
characters (quotes, function names, semicolons, dollar signs, etc.) is rejected and returns `false`.

```php
// BEFORE — raw eval without validation
$str = '$result = ' . str_replace('n', '$number', $set) . ';';
eval($str);

// AFTER — allowlist before eval
if (!preg_match('/^[0-9n\s\+\-\*\/\%\<\>\=\!\&\|\(\)\.]+$/i', $set)) {
    return false;
}
$str = '$result = ' . str_replace('n', '$number', $set) . ';';
eval($str);
```

---

### Web Root Structural Change — `public/` Directory

**Files moved:** `index.php` → `public/index.php`, `.htaccess` → `public/.htaccess`  
**Documentation updated:** `README.md`, `INSTALL.md`

#### `public/index.php` — SF_ROOT_DIR Path Calculation Fix

The initial `public/index.php` in v1.5.0.2 contained a critical path calculation error:

```php
// WRONG — SF_ROOT_DIR pointed at public/ itself, not the project root
define('SF_ROOT_DIR', realpath(__DIR__));
```

Because this file lives at `public/index.php`, `__DIR__` resolves to the `public/` directory.
Setting `SF_ROOT_DIR` to `realpath(__DIR__)` meant the framework was looking for `lib/`, `apps/`,
`config/`, and `vendor/` inside `public/` — they do not exist there. The correct value must be
one level up: the project root.

```php
// CORRECT — navigate one level up from public/ to the project root
$path = realpath(__DIR__);
define('SF_ROOT_DIR', $path . '/../');
```

This was corrected in the same release. `SF_ROOT_DIR` now resolves to the project root directory
regardless of what the web server sets as `DocumentRoot`. The comment block above the definition
in `public/index.php` now explicitly documents this relationship.

This class of bug is a common pitfall when migrating a symfony1 project from the flat-root layout
(where `index.php` lived at the project root and `SF_ROOT_DIR = realpath(__DIR__)` was correct)
to the `public/` layout (where the front controller is one level deeper and the path must ascend).

**What changed:**  
The front controller (`index.php`) and Apache rewrite rules (`.htaccess`) have been moved from
the project root into a dedicated `public/` subdirectory. The web server `DocumentRoot` (Apache)
or `root` (Nginx) should now point to `public/` rather than the project root.

**Why this matters:**  
When `DocumentRoot` was the project root, every file in the repository was potentially reachable
over HTTP — subject only to web server configuration. This included:

- `composer.json` — reveals all installed dependencies and their versions, enabling targeted CVE research
- `composer.lock` — reveals exact dependency versions, narrowing CVE search further
- `lib/` — framework source code readable directly
- `apps/` — application configuration, routing rules, and templates
- `vendor/` — third-party packages potentially containing their own vulnerabilities
- `.git/` — if misconfigured, the entire source repository history

The `public/` layout is the **standard pattern** used by Symfony 2+, Laravel, Laminas, Slim, and
every other modern PHP framework. By setting `DocumentRoot` to `public/`, none of the above are
reachable by HTTP even if the web server's access controls are misconfigured. The directory boundary
is enforced by the file system, not by configuration.

**How to update your virtual host:**

Apache:
```apacheconf
# Before
DocumentRoot /var/www/myapp

# After — point to public/
DocumentRoot /var/www/myapp/public
```

Nginx:
```nginx
# Before
root /var/www/myapp;

# After — point to public/
root /var/www/myapp/public;
```

See [INSTALL.md](INSTALL.md) for complete virtual host examples.

---

### Files Changed in v1.5.0.2

| File | Change |
|------|--------|
| `lib/yaml/sfYamlInline.php` | Block `!!php/object:` deserialization |
| `lib/validator/sfValidatorCSRFToken.class.php` | Use `hash_equals()` for CSRF validation |
| `lib/form/sfForm.class.php` | Upgrade CSRF token to HMAC-SHA256 |
| `lib/i18n/sfChoiceFormat.class.php` | Allowlist guard before `eval()` in set notation |
| `index.php` → `public/index.php` | Move front controller to `public/`; fix `SF_ROOT_DIR` path calculation |
| `.htaccess` → `public/.htaccess` | Move rewrite rules to `public/` directory |
| `README.md` | Update DocumentRoot instructions and directory layout |
| `INSTALL.md` | Update vhost examples, step-by-step guide, troubleshooting |

---

### Upgrade Notes

**Application code:** No changes required. The security fixes are internal to the framework's
validator, form, YAML, and i18n subsystems.

**Web server configuration:** Update `DocumentRoot` / `root` from the project root to `public/`.
This is the only operator action required.

**Existing `index.php` in project root:** If you have a copy of `index.php` at the project root
from a prior deployment, it should be removed. Only `public/index.php` is the authoritative
front controller in v1.5.0.2.

---

## v1.5.0.1 — Documentation & Staging Cleanup

**Release:** `v1.5.0.1`  
**Date:** 2026-05-09

- Removed private staging site references from public documentation
- Added README.md and INSTALL.md in 7x standard format with full installation, routing,
  action, template, database, Composer, test suite, and deployment sections

---

## v1.5.0.0 — PHP 8.5 Support Release

**Release:** `v1.5.0.0`  
**Branch:** `1.5`  
**Date:** 2026-05-09  
**Maintainer:** [7x](https://se7enx.com) — [info@se7enx.com](mailto:info@se7enx.com)  
**Repository:** https://github.com/se7enxweb/symfony1

---

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
