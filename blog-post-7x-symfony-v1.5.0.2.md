## 7x Releases 7x SymfonyOne v1.5.0.2 — The Symfony v1 Drop-In Framework Security Upgrade! Update Now!

### 7x Releases 7x SymfonyOne v1.5.0.2 — The Symfony v1 Drop-In Framework Security Upgrade! Update Now!

(Critical security fixes for all Symfony 1.x installations — upgrade from Symfony 1.4.x immediately)

7x is urgently pleased to announce the release of [7x SymfonyOne v1.5.0.2](https://github.com/se7enxweb/symfonyone/releases/tag/v1.5.0.2) to developers and users worldwide. This is a security release. It patches four vulnerabilities in the Symfony One framework core — including a Critical-severity remote code execution vector inherited from all versions of Symfony 1.4.x — and hardens the framework against CSRF token forgery and code injection. If you are running any version of Symfony 1.4.x or an earlier 1.5.0.x release, you should upgrade immediately.

Read the full [GitHub release notes](https://github.com/se7enxweb/symfonyone/releases/tag/v1.5.0.2) and the updated [RELEASE_NOTES.md](https://github.com/se7enxweb/symfonyone/blob/1.5/RELEASE_NOTES.md) for complete technical details on every fix.

#### Why This Release Matters — The Symfony 1.4.x Security Debt

Symfony 1.4.x reached end-of-life in November 2012. No security patches have been issued by the upstream project in over a decade. Every vulnerability fixed in this release has been present and unpatched in Symfony 1.4.x for years. The four fixes shipped in v1.5.0.2 address issues that were always in the codebase — they were simply never found, never reported, or reported and never fixed upstream because the project was no longer maintained.

If your application is running Symfony 1.4.x on a public server today, it is exposed to every one of these vulnerabilities right now.

#### Security Fixes in v1.5.0.2

- **YAML PHP Object Injection — Critical (RCE)** — The YAML parser honoured the `!!php/object:` tag, passing attacker-controlled data directly to `unserialize()`. This is the same class of vulnerability as CVE-2024-28859. Using publicly available gadget-chain tooling (phpggc), an attacker who can influence any YAML the application parses — through a file upload, a form field, a remote API response, or an injected config value — can achieve arbitrary remote code execution without any zero-day exploit. The gadget classes are already bundled with the framework: Swift Mailer, Doctrine 1.x, and Propel 1.x all ship exploitable chains. **Fixed:** the `!!php/object:` tag now throws an exception unconditionally. PHP object deserialization from YAML is permanently disabled.

- **CSRF Token Timing Attack — High** — The CSRF token validator compared submitted tokens using PHP's `!=` operator, which short-circuits on the first differing byte. This leaks timing information that enables byte-by-byte brute-force of a CSRF token, defeating the primary defence against Cross-Site Request Forgery. A successful CSRF attack lets an attacker trigger any authenticated state-changing action — account changes, data deletion, fund transfers — by tricking a logged-in user into visiting a malicious page. **Fixed:** replaced with PHP's `hash_equals()` for constant-time comparison.

- **Weak CSRF Token Generation — Medium** — CSRF tokens were generated using `md5()` with simple string concatenation. MD5 is a cryptographically broken hash function, vulnerable to preimage attacks and length extension. The construction used (secret prefix concatenation rather than HMAC) means an attacker who can observe token output may forge valid tokens without knowing the secret. **Fixed:** upgraded to `hash_hmac('sha256', ...)`, which uses the secret as a proper cryptographic key and produces 256-bit output resistant to length-extension attacks.

- **eval() Injection via i18n Choice Format — Medium** — The `{n: expr}` set notation in i18n plural strings substituted YAML translation catalogue content directly into `eval()` without any character-level validation. An attacker who controls translation catalogue content — through an admin interface, a writable YAML file, or a higher-level YAML injection — can inject arbitrary PHP code. **Fixed:** a strict allowlist regex now rejects any expression containing characters outside digits, the placeholder variable `n`, whitespace, and standard arithmetic and comparison operators.

#### Web Root Structural Improvement

This release also moves the web front controller (`index.php`) and Apache rewrite rules (`.htaccess`) from the project root into a dedicated `public/` subdirectory. Web server `DocumentRoot` (Apache) and `root` (Nginx) should now point to `public/`.

This is the layout used by every modern PHP framework (Symfony 2+, Laravel, Laminas, Slim). With `DocumentRoot` set to `public/`, the file system enforces that `lib/`, `apps/`, `vendor/`, `composer.json`, `composer.lock`, and `.git/` are never reachable over HTTP — regardless of web server access-control configuration. Previously, any misconfiguration could expose framework internals, dependency manifests, and source code directly to the internet.

#### Getting Started

- Download the release from [GitHub](https://github.com/se7enxweb/symfonyone/releases/tag/v1.5.0.2).
- Review the updated [INSTALL.md](https://github.com/se7enxweb/symfonyone/blob/1.5/INSTALL.md) for Apache and Nginx virtual host configuration showing the `public/` DocumentRoot.
- Update your web server `DocumentRoot` / `root` from the project root to `public/` after upgrading.
- Run on PHP 8.4 or later for best security and stability. All PHP 8.x versions from 8.0 through 8.5.6 are supported.
- 7x SymfonyOne v1.5 is a drop-in upgrade for Symfony 1.4.x installations — review the framework requirements before upgrading and test on a staging environment first.

#### What's New in v1.5.0.2

- **lib/yaml/sfYamlInline.php** — `!!php/object:` YAML deserialization blocked (Critical RCE, CWE-502).
- **lib/validator/sfValidatorCSRFToken.class.php** — constant-time CSRF token comparison via `hash_equals()` (High, CWE-208).
- **lib/form/sfForm.class.php** — CSRF token generation upgraded from MD5 to HMAC-SHA256 (Medium, CWE-326).
- **lib/i18n/sfChoiceFormat.class.php** — character allowlist guard before `eval()` in i18n set notation (Medium, CWE-94).
- **public/index.php** — front controller relocated to `public/` subdirectory.
- **public/.htaccess** — Apache rewrite rules relocated to `public/` subdirectory.
- **README.md** — directory layout and DocumentRoot examples updated to reflect `public/`.
- **INSTALL.md** — all Apache and Nginx vhost examples, installation steps, security checklist, and troubleshooting updated for `public/` web root.
- **RELEASE_NOTES.md** — full v1.5.0.2 security advisory with technical detail, code diffs, and CWE references.

#### Get Support

The community is here to help. Join the conversation and get quick answers through any of the following channels:

- [GitHub Discussions](https://github.com/se7enxweb/symfonyone/discussions) — join the dedicated discussion thread for this release to share your experience and ask questions.
- [GitHub Issues](https://github.com/se7enxweb/symfonyone/issues) — report bugs or track known issues directly on the repository.
- [Share — Community Forums](https://share.se7enx.com/) — browse existing threads and post new questions.
- [Telegram — @exponentialcms](https://t.me/exponentialcms) — real-time help and community discussion.

Thank you to everyone in the community who reported issues and contributed. Please upgrade to 7x SymfonyOne v1.5.0.2 immediately.
