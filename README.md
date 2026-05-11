# 7x Primer Framework v1.5 — PHP 8.5 Support (Stable; Open Source)

[![PHP](https://img.shields.io/badge/PHP-8.0%2B-blue)](https://php.net/)
[![Primer](https://img.shields.io/badge/primer-1.5-orange)](https://github.com/se7enxweb/primer)
[![License: MIT](https://img.shields.io/badge/License-MIT-green)](LICENSE)
[![GitHub issues](https://img.shields.io/github/issues/se7enxweb/primer)](https://github.com/se7enxweb/primer/issues)

> **7x Primer Framework v1.5** is the continuing evolution of the original symfony 1.4 framework,
> now fully compatible with PHP 8.0 through PHP 8.5. Maintained by [7x (se7enx.com)](https://se7enx.com/)
> and the open-source developer community that has relied on symfony1 for over two decades.

---

## Table of Contents

1. [Project Notice](#1-project-notice)
2. [Project Status](#2-project-status)
3. [Who is 7x](#3-who-is-7x)
4. [What is 7x Primer Framework?](#4-what-is-7x-primer-framework)
5. [Architecture Overview](#5-architecture-overview)
6. [Technology Stack](#6-technology-stack)
7. [Requirements](#7-requirements)
8. [Quick Start](#8-quick-start)
9. [Main Features](#9-main-features)
10. [Building Pages, Routes, and Database Results](#10-building-pages-routes-and-database-results)
11. [Installation](#11-installation)
12. [Key CLI & Task Reference](#12-key-cli--task-reference)
13. [Issue Tracker](#13-issue-tracker)
14. [Where to Get More Help](#14-where-to-get-more-help)
15. [How to Contribute](#15-how-to-contribute)
16. [Donate & Support](#16-donate--support)
17. [Copyright](#17-copyright)
18. [License](#18-license)

---

## 1. Project Notice

> **Please Note:** This project is not associated with the original symfony project, SensioLabs, or Fabien
> Potencier beyond attribution. It is an independent, 7x + community-driven continuation of the symfony 1.4
> codebase, stewarded and evolved by [7x (se7enx.com)](https://se7enx.com/) to support PHP 8.x in production.

---

## 2. Project Status

The symfony 1.4 codebase reached end-of-life in November 2012. Thousands of production websites and
applications continued to run on it because its architecture — convention-over-configuration, composable
action-filter-view pipeline, flexible ORM integration — remained sound and productive.

**7x Primer Framework v1.5** is the first release branch that brings this codebase fully into the PHP 8.x era.

The `1.5` branch is the current active development branch. Work focuses on:

- PHP 8.0 through PHP 8.5 full compatibility and test suite passing
- Composer package manager integration alongside sfCoreAutoload
- Security patches and vulnerability triage
- Documentation and developer experience improvements

---

## 3. Who is 7x

[7x](https://se7enx.com/) is a North American web software corporation with over 24 years of experience
building and maintaining PHP web applications and content platforms. Previously known as Brookins Consulting,
7x took on stewardship of the symfony1 codebase to ensure that the many applications built on it can
continue to run on modern, supported PHP versions.

7x offers:

- Commercial PHP 8.x upgrade consulting for symfony1 applications
- Hosting and infrastructure for PHP 8.x projects
- Custom development, migrations, and training
- Open-source community stewardship

---

## 4. What is 7x Primer Framework?

7x Primer Framework (v1.5) is a PHP 8.x-compatible continuation of the symfony 1.4 MVC web application
framework. It provides:

- A **Model-View-Controller** architecture with clean separation of concerns
- **Convention-over-configuration** — a predictable directory layout that removes boilerplate
- **sfPatternRouting** — a flexible URL routing engine with named routes, parameters, and requirements
- **Action pipeline** — `executeActionName()` methods dispatch HTTP requests through configurable pre/post
  filters to typed PHP template files
- **symfony1-style autoloading** via `sfCoreAutoload` — every class in `lib/` is available on demand
- **Composer integration** (new in 1.5) — `vendor/autoload.php` coexists with `sfCoreAutoload` via
  PHP's `spl_autoload` stack, giving access to the full Packagist ecosystem
- **ORM integration** — `sfDoctrinePlugin` and `sfPropelPlugin` remain fully functional for
  database-backed applications
- **Lime test framework** — a TAP-based unit and functional test suite with 600+ tests
- **Full PHP 8.5 support** — every PHP 8.x breaking change addressed in the core library

### Architecture at a Glance

```
HTTP Request
      │
      ▼
   Web Server (Apache / Nginx)  →  DocumentRoot: public/
      │
      ▼
  public/index.php  ──  sfCoreAutoload + (optional) Composer vendor/autoload.php
      │
      ├── sfPatternRouting  →  matches URL to module/action
      │
      ├── {Module}Actions::execute{Action}()  →  business logic
      │
      ├── {action}Success.php  →  view template (PHP)
      │
      └── layout.php  →  HTML decorator (wraps template output)
```

---

## 5. Architecture Overview

### Directory Layout

```
project-root/
├── apps/
│   └── {appName}/
│       ├── config/
│       │   └── routing.php          Route definitions (sfRoute / sfPatternRouting)
│       ├── lib/
│       │   ├── sfMicroAction.class.php    Base action class (mirrors sfAction API)
│       │   └── sfMicroDispatcher.class.php   Front controller dispatcher
│       ├── modules/
│       │   └── {moduleName}/
│       │       ├── actions/
│       │       │   └── actions.class.php  {Module}Actions extends sfMicroAction
│       │       └── templates/
│       │           └── {action}Success.php  View template
│       └── templates/
│           ├── layout.php           HTML decorator layout
│           └── error404.php         404 fallback template
├── lib/                             symfony1 core library (sfCoreAutoload scans here)
│   ├── autoload/                    sfCoreAutoload class
│   ├── cache/                       Cache drivers
│   ├── config/                      Configuration handlers
│   ├── form/                        Form and widget system
│   ├── i18n/                        Internationalisation
│   ├── plugins/                     sfDoctrinePlugin, sfPropelPlugin
│   ├── routing/                     sfPatternRouting, sfRoute
│   ├── task/                        symfony CLI tasks
│   ├── util/                        sfToolkit, sfInflector, sfFinder, …
│   ├── validator/                   Validator library
│   ├── vendor/
│   │   ├── lime/                    Lime test framework
│   │   └── swiftmailer/             Swift Mailer
│   └── yaml/                        YAML parser
├── public/                          ← Web server DocumentRoot (only publicly served dir)
│   ├── index.php                    Web front controller — single entry point
│   ├── .htaccess                    Apache rewrite rules
│   ├── favicon.ico
│   └── favicon.png
├── test/
│   ├── unit/                        Unit tests (lime-based)
│   └── functional/                  Functional tests
└── composer.json                    Composer manifest (new in v1.5)
```

---

## 6. Technology Stack

| Component | Version |
|-----------|---------|
| Language | PHP **8.0+** (tested through **8.5.6**) |
| Framework | 7x Primer Framework **1.5** (symfony1 core) |
| Autoloading | sfCoreAutoload + Composer PSR-4/classmap (coexistent) |
| ORM | sfDoctrinePlugin (Doctrine 1.x) · sfPropelPlugin (Propel 1.x) · PDO (direct) |
| Template Engine | PHP (native) |
| Routing | sfPatternRouting + sfRoute |
| Test Framework | Lime (TAP-based) |
| Dependency Mgmt | Composer 2.x (optional — new in v1.5) |
| Web Server | Apache 2.4 · Nginx 1.18+ |
| Database | MySQL 8.0+ · MariaDB 10.3+ · PostgreSQL 14+ · SQLite 3.x |

---

## 7. Requirements

- **PHP 8.0 or later** (PHP 8.5 recommended; tested on 8.5.6)
- A web server: **Apache 2.4** (with `mod_rewrite`) or **Nginx 1.18+**
- A database: **MySQL 8.0+**, **MariaDB 10.3+**, **PostgreSQL 14+**, or **SQLite 3.x**
- **Composer 2.x** (optional — required only when installing Packagist packages)

### Requirements Summary

| Requirement | Minimum | Recommended |
|-------------|---------|-------------|
| PHP | 8.0 | 8.5+ |
| Apache | 2.4 | 2.4 (event + PHP-FPM) |
| Nginx | 1.18 | 1.24+ |
| MySQL | 8.0 | 8.0+ |
| MariaDB | 10.3 | 10.6+ |
| PostgreSQL | 14 | 16+ |
| SQLite | 3.0 | 3.35+ |
| Composer | 2.0 | latest 2.x (optional) |

---

## 8. Quick Start

```bash
# 1. Clone the repository (branch 1.5)
git clone -b 1.5 https://github.com/se7enxweb/primer.git my-project
cd my-project

# 2. (Optional) Install Composer packages
composer install

# 3. Point your web server DocumentRoot to the project's public/ directory
#    This keeps lib/, apps/, vendor/, composer.json, etc. off the web.
#    See INSTALL.md for Apache and Nginx virtual host examples.

# 4. Open in a browser
#    GET /         → 301 redirect to /version
#    GET /version  → live status dashboard (all 8 core class checks)
```

---

## 9. Main Features

- **Convention-over-configuration MVC** — modules, actions, and templates follow a predictable naming
  convention; zero config required for the happy path
- **sfPatternRouting** — named routes, parameter requirements, default values, and reverse URL generation
- **Action pipeline** — pre-filters, post-filters, and slots for cross-cutting concerns (auth, logging, caching)
- **symfony1-style autoloading** — `sfCoreAutoload` discovers every class in `lib/` automatically
- **Composer integration** (new in v1.5) — `vendor/autoload.php` loads alongside `sfCoreAutoload`
  when present; install any Packagist package with `composer require`
- **Form system** — `sfForm`, `sfFormField`, `sfWidget`, and `sfValidator` for type-safe form handling
- **i18n / l10n** — `sfI18N`, `sfDateFormat`, `sfNumberFormat` for internationalised applications
- **Lime test framework** — TAP-based unit and functional testing with 600+ passing tests
- **sfDoctrinePlugin** — Doctrine 1.x ORM with model generation, migrations, and fixtures
- **sfPropelPlugin** — Propel 1.x ORM as an alternative
- **Cache drivers** — filesystem, APC, SQLite, and Memcache cache backends
- **Swift Mailer** — bundled for transactional email
- **YAML configuration** — sfYaml parser for configuration files
- **PHP 8.0–8.5 full compatibility** — all breaking changes addressed in 62 core library files

---

## 10. Building Pages, Routes, and Database Results

See [INSTALL.md](INSTALL.md) for full step-by-step instructions. Below is the three-minute version.

### Define a route

```php
// apps/site/config/routing.php
$routing->connect('articles', new sfRoute('/articles', [
    'module' => 'article',
    'action' => 'index',
]));
$routing->connect('article_show', new sfRoute('/articles/:slug', [
    'module' => 'article',
    'action' => 'show',
], ['slug' => '[a-z0-9\-]+']));
```

### Create an action

```php
// apps/site/modules/article/actions/actions.class.php
class articleActions extends sfMicroAction
{
    public function executeIndex(): string
    {
        $pdo = new PDO('mysql:host=127.0.0.1;dbname=myapp;charset=utf8mb4', 'user', 'pass');
        $this->articles = $pdo->query('SELECT * FROM articles ORDER BY created_at DESC LIMIT 10')
                              ->fetchAll(PDO::FETCH_ASSOC);
        $this->title = 'Latest Articles';
        return sfView::SUCCESS;
    }

    public function executeShow(): string
    {
        $pdo = new PDO('mysql:host=127.0.0.1;dbname=myapp;charset=utf8mb4', 'user', 'pass');
        $stmt = $pdo->prepare('SELECT * FROM articles WHERE slug = ? LIMIT 1');
        $stmt->execute([$this->getRequest()->getParameter('slug')]);
        $this->article = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$this->article) {
            $this->forward404();
        }
        $this->title = $this->article['title'];
        return sfView::SUCCESS;
    }
}
```

### Create a template

```php
<!-- apps/site/modules/article/templates/indexSuccess.php -->
<h1><?php echo htmlspecialchars($title) ?></h1>
<ul>
<?php foreach ($articles as $article): ?>
  <li>
    <a href="/articles/<?php echo htmlspecialchars($article['slug']) ?>">
      <?php echo htmlspecialchars($article['title']) ?>
    </a>
    <span><?php echo $article['created_at'] ?></span>
  </li>
<?php endforeach ?>
</ul>
```

---

## 11. Installation

See **[INSTALL.md](INSTALL.md)** for the complete step-by-step guide, including:

- Git clone and composer install
- Apache and Nginx virtual host configuration
- Building a page — module, action, template, and layout
- Route definition with parameters and requirements
- Database queries (PDO, sfDoctrinePlugin, sfPropelPlugin)
- Running the lime test suite
- Composer package integration
- Deployment checklist

---

## 12. Key CLI & Task Reference

```bash
# ── Test Suite ───────────────────────────────────────────────────────────────
php test/unit/util/sfInflectorTest.php              # run one unit test
php test/bin/prove.php test/unit/                   # run all unit tests
php test/bin/prove.php test/                        # run all tests

# ── symfony1 Tasks (requires full project configuration) ─────────────────────
php symfony list                                    # list all registered tasks
php symfony help <task>                             # help for a task
php symfony generate:app myapp                      # scaffold a new application
php symfony generate:module myapp mymodule          # scaffold a new module
php symfony cc                                      # clear the application cache
php symfony project:permissions                     # fix directory permissions

# ── Composer ─────────────────────────────────────────────────────────────────
composer install                                    # install packages from composer.json
composer require vendor/package-name                # add a Packagist package
composer dump-autoload -o                           # regenerate optimised autoloader
composer show                                       # list installed packages
composer audit                                      # check for security advisories
```

---

## 13. Issue Tracker

Submit bugs, feature requests, and improvements at:
**https://github.com/se7enxweb/primer/issues**

If you discover a security issue, please report it responsibly by email to
[security@se7enx.com](mailto:security@se7enx.com) rather than opening a public issue.

---

## 14. Where to Get More Help

| Resource | URL |
|----------|-----|
| Repository | github.com/se7enxweb/primer |
| Release Notes | RELEASE_NOTES.md |
| Installation Guide | INSTALL.md |
| Issue Tracker | github.com/se7enxweb/primer/issues |
| Discussions | github.com/se7enxweb/primer/discussions |
| 7x Corporate | se7enx.com |
| Support | support@se7enx.com |
| Sponsor 7x | sponsor.se7enx.com |

---

## 15. How to Contribute

Everyone is encouraged to contribute. To get started:

1. Fork the repository: [github.com/se7enxweb/primer](https://github.com/se7enxweb/primer)
2. Clone your fork and create a feature branch:
   ```bash
   git checkout -b feature/my-improvement
   ```
3. Make your changes following the existing code style
4. Add the `(c) 2004-2026 7x <info@se7enx.com>` copyright header to every modified PHP file
5. Run the lime test suite to verify no regressions:
   ```bash
   php test/bin/prove.php test/unit/
   ```
6. Push and open a Pull Request against the `1.5` branch
7. Participate in the code review — maintainers respond promptly

Bug reports, feature requests, and discussions are welcome via the
[issue tracker](https://github.com/se7enxweb/primer/issues) and
[GitHub Discussions](https://github.com/se7enxweb/primer/discussions).

---

## 16. Donate & Support

7x Primer Framework v1.5 is free and open-source. If it has saved you migration time, upgrade costs,
or kept a production application running, please consider supporting the project:

- [sponsor.se7enx.com](https://sponsor.se7enx.com/) — support subscriptions
- [paypal.me/7xweb](https://www.paypal.com/paypalme/7xweb) — one-time donation
- [github.com/sponsors/se7enxweb](https://github.com/sponsors/se7enxweb) — GitHub Sponsors

Every contribution funds:

- PHP compatibility testing as new PHP versions release
- Security patching and vulnerability triage
- Documentation and developer experience improvements
- Community infrastructure

---

## 17. Copyright

```
Copyright (C) 2004-2026 7x (se7enx.com). All rights reserved.
Copyright (C) 2004-2006 Fabien Potencier <fabien.potencier@symfony-project.com>
Copyright (C) 2004-2006 Sean Kerr <sean@code-box.org>
```

---

## 18. License

Licensed under the **MIT License**. See [LICENSE](LICENSE) for the full license text.

```
Permission is hereby granted, free of charge, to any person obtaining a copy
of this software and associated documentation files (the "Software"), to deal
in the Software without restriction, including without limitation the rights
to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
copies of the Software...
```
