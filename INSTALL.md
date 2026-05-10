# 7x Symfony Framework v1.5 — Installation & Developer Guide

> Full installation, web server configuration, route and page examples,
> database integration patterns, Composer package usage, and the lime test suite.
> Target: PHP 8.0 – 8.5 · Apache 2.4 / Nginx 1.18+ · MySQL / MariaDB / PostgreSQL / SQLite

---

## Table of Contents

1. [Requirements](#1-requirements)
2. [Architecture & Directory Overview](#2-architecture--directory-overview)
3. [First-Time Installation](#3-first-time-installation)
4. [Composer Dependency Management](#4-composer-dependency-management)
5. [Web Server Configuration](#5-web-server-configuration)
6. [File Permissions](#6-file-permissions)
7. [Building Your First Page](#7-building-your-first-page)
8. [Routing — Full Reference](#8-routing--full-reference)
9. [Actions — Request, Parameters, Redirects](#9-actions--request-parameters-redirects)
10. [Templates & Layouts](#10-templates--layouts)
11. [Database Integration — PDO (Built-in)](#11-database-integration--pdo-built-in)
12. [Database Integration — sfDoctrinePlugin (ORM)](#12-database-integration--sfdoctrineplugin-orm)
13. [Database Integration — sfPropelPlugin (ORM)](#13-database-integration--sfpropelplugin-orm)
14. [Composer Packages in Actions](#14-composer-packages-in-actions)
15. [Forms and Validation](#15-forms-and-validation)
16. [Running the Lime Test Suite](#16-running-the-lime-test-suite)
17. [Writing Your Own Tests](#17-writing-your-own-tests)
18. [symfony1 CLI Tasks](#18-symfony1-cli-tasks)
19. [Cache Management](#19-cache-management)
20. [Deployment Checklist](#20-deployment-checklist)
21. [Troubleshooting](#21-troubleshooting)

---

## 1. Requirements

### Mandatory

| Requirement | Minimum | Tested Versions |
|-------------|---------|-----------------|
| PHP | 8.0 | 8.0, 8.1, 8.2, 8.3, 8.4, 8.5 |
| Web Server | — | Apache 2.4 · Nginx 1.18+ |
| OS | Linux | CentOS 7 / AlmaLinux 8 / Ubuntu 22.04+ / Debian 12+ |

### Optional but Recommended

| Optional Dependency | Purpose |
|--------------------|---------|
| Composer 2.x | Install Packagist packages |
| MySQL 8.0+ / MariaDB 10.3+ | Relational database |
| PostgreSQL 14+ | Alternative relational database |
| SQLite 3.x | Lightweight / embedded database |
| PHP ext-pdo | PDO database driver (usually bundled) |
| PHP ext-pdo_mysql | MySQL PDO driver |
| PHP ext-pdo_pgsql | PostgreSQL PDO driver |
| PHP ext-mbstring | Multi-byte string functions (recommended) |
| PHP ext-intl | Internationalisation (recommended) |
| PHP ext-opcache | Performance (production) |

### Check PHP Version

```bash
php -v
# Expected: PHP 8.x.x ...

php -m | grep -E 'pdo|mbstring|intl|opcache'
# Check enabled extensions
```

---

## 2. Architecture & Directory Overview

```
project-root/
├── apps/
│   └── site/                        Default application (name configurable)
│       ├── config/
│       │   └── routing.php          URL routing rules
│       ├── lib/
│       │   ├── sfMicroAction.class.php     Base action class
│       │   └── sfMicroDispatcher.class.php Web front-controller dispatcher
│       ├── modules/
│       │   └── {module}/
│       │       ├── actions/
│       │       │   └── actions.class.php   {Module}Actions class
│       │       └── templates/
│       │           ├── {action}Success.php  Happy-path template
│       │           └── {action}Error.php    Error-path template
│       └── templates/
│           ├── layout.php           Global HTML decorator
│           └── error404.php         404 fallback
├── lib/                             symfony1 core library (sfCoreAutoload scans this)
│   ├── autoload/sfCoreAutoload.class.php
│   ├── cache/                       Cache backends (sfFileCache, sfAPCCache, …)
│   ├── config/                      Configuration handlers
│   ├── exception/                   Exception hierarchy
│   ├── form/                        Form / widget / validator system
│   ├── i18n/                        i18n / l10n
│   ├── plugins/
│   │   ├── sfDoctrinePlugin/        Doctrine 1.x ORM plugin
│   │   └── sfPropelPlugin/          Propel 1.x ORM plugin
│   ├── routing/                     sfPatternRouting, sfRoute, sfRequestRoute, …
│   ├── task/                        symfony CLI task system
│   ├── util/                        sfToolkit, sfInflector, sfFinder, …
│   ├── validator/                   sfValidator hierarchy
│   └── vendor/
│       ├── lime/lime.php            Lime TAP test framework
│       └── swiftmailer/             Swift Mailer
├── test/
│   ├── bin/prove.php                Test runner
│   ├── unit/                        Unit test files (*Test.php)
│   └── functional/                  Functional test fixtures
├── src/                             Your PSR-4 namespaced code (optional, Composer)
├── vendor/                          Composer packages (git-ignored)
├── composer.json                    Composer manifest
├── composer.lock                    Lock file (git-ignored in this repo)
├── index.php                        Web front controller
└── .htaccess                        Apache rewrite configuration
```

### Request Lifecycle

```
1.  Browser sends GET /articles/hello-world
2.  Apache/Nginx rewrites all requests → index.php
3.  index.php boots sfCoreAutoload (+ vendor/autoload.php if present)
4.  sfMicroDispatcher creates sfPatternRouting, loads routing.php
5.  sfPatternRouting matches /articles/hello-world → module=article, action=show
6.  sfInflector::camelize('article') → 'Article' → loads articleActions class
7.  articleActions::executeShow() runs — sets $this->article, returns sfView::SUCCESS
8.  Dispatcher renders apps/site/modules/article/templates/showSuccess.php
9.  Template output is wrapped in apps/site/templates/layout.php
10. HTML response sent to browser
```

---

## 3. First-Time Installation

### Step 1 — Clone the Repository

```bash
git clone -b 1.5 https://github.com/se7enxweb/symfony1.git my-project
cd my-project
```

### Step 2 — Verify PHP Version

```bash
php -r "echo PHP_VERSION . PHP_EOL;"
# Must print 8.0.0 or higher
```

### Step 3 — (Optional) Install Composer Packages

```bash
# Only needed if you plan to add Packagist packages or rely on composer.json
composer install
```

### Step 4 — Set Up Web Server

See [Section 5 — Web Server Configuration](#5-web-server-configuration) for Apache and Nginx examples.
Point your `DocumentRoot` at the project root (where `index.php` lives).

### Step 5 — Set File Permissions

```bash
# Make cache and log directories writable
mkdir -p apps/site/cache apps/site/log test/functional/fixtures/cache test/functional/fixtures/log
chmod -R 777 apps/site/cache apps/site/log
```

### Step 6 — Verify the Installation

Navigate to your domain or `http://localhost/`. You should be redirected to `/version` where a
green status dashboard confirms all symfony1 core classes load correctly.

```bash
curl -s http://localhost/version | grep -i "all systems"
```

### 💾 Git Save Point — Installation Complete

```bash
git status        # verify working tree is clean
git log --oneline -3
```

---

## 4. Composer Dependency Management

### How It Works in v1.5

`index.php` boots symfony1's own `sfCoreAutoload` first, then optionally requires
`vendor/autoload.php` if it exists. PHP's `spl_autoload` stack means both coexist cleanly:

```php
// index.php (simplified)
require_once SF_ROOT_DIR.'/lib/autoload/sfCoreAutoload.class.php';
sfCoreAutoload::register();

if (is_file(SF_ROOT_DIR.'/vendor/autoload.php')) {
    require_once SF_ROOT_DIR.'/vendor/autoload.php';
}
```

### Adding a Packagist Package

```bash
composer require guzzlehttp/guzzle
# Now GuzzleHttp\Client is available anywhere in your application
```

### Example — Using a Packagist Library in an Action

```php
// apps/site/modules/api/actions/actions.class.php
class apiActions extends sfMicroAction
{
    public function executeFetch(): string
    {
        $client   = new \GuzzleHttp\Client();
        $response = $client->get('https://api.example.com/data');
        $this->data = json_decode($response->getBody(), true);
        return sfView::SUCCESS;
    }
}
```

### Keeping vendor/ Out of Git

The `composer.json` in this repository already configures `.gitignore` to exclude `vendor/`
and `composer.lock`. Collaborators run `composer install` after cloning.

---

## 5. Web Server Configuration

### Apache 2.4 — Virtual Host

```apacheconf
<VirtualHost *:80>
    ServerName myapp.example.com
    DocumentRoot /var/www/myapp

    <Directory /var/www/myapp>
        Options -Indexes +FollowSymLinks
        AllowOverride All
        Require all granted
    </Directory>

    ErrorLog  /var/log/apache2/myapp-error.log
    CustomLog /var/log/apache2/myapp-access.log combined
</VirtualHost>
```

The repository's `.htaccess` file handles rewriting. Ensure `mod_rewrite` is enabled:

```bash
a2enmod rewrite
systemctl reload apache2
```

`.htaccess` rewrite rules (already in the repository root):

```apacheconf
DirectoryIndex index.php

<IfModule mod_rewrite.c>
    RewriteEngine On
    RewriteCond %{REQUEST_FILENAME} !-f
    RewriteCond %{REQUEST_FILENAME} !-d
    RewriteRule .* index.php [QSA,L]
</IfModule>
```

### Nginx — Server Block

```nginx
server {
    listen 80;
    server_name myapp.example.com;
    root /var/www/myapp;
    index index.php;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location ~ \.php$ {
        fastcgi_pass   unix:/var/run/php/php8.5-fpm.sock;
        fastcgi_index  index.php;
        fastcgi_param  SCRIPT_FILENAME $document_root$fastcgi_script_name;
        include        fastcgi_params;
    }

    location ~ /\. {
        deny all;
    }
}
```

Reload Nginx after changes:

```bash
nginx -t && systemctl reload nginx
```

---

## 6. File Permissions

symfony1 writes to cache and log directories. Set them writable by the web server user:

```bash
# Create directories if they do not exist
mkdir -p apps/site/cache apps/site/log

# Option A — open permissions (development only)
chmod -R 777 apps/site/cache apps/site/log

# Option B — www-data ownership (production recommended)
chown -R www-data:www-data apps/site/cache apps/site/log
chmod -R 755 apps/site/cache apps/site/log
```

---

## 7. Building Your First Page

This section walks through creating a `hello` module from scratch, covering route, action,
template, and layout in full.

### Step 7.1 — Create the Module Directory Structure

```bash
mkdir -p apps/site/modules/hello/actions
mkdir -p apps/site/modules/hello/templates
```

### Step 7.2 — Create the Action Class

```php
<?php
// apps/site/modules/hello/actions/actions.class.php

/*
 * (c) 2004-2026 7x <info@se7enx.com>
 */

class helloActions extends sfMicroAction
{
    /**
     * Display a personalised greeting.
     * URL: /hello/{name}
     */
    public function executeIndex(): string
    {
        // Retrieve the :name parameter from the matched route
        $this->name = ucfirst(htmlspecialchars(
            $this->getRequest()->getParameter('name', 'World'),
            ENT_QUOTES,
            'UTF-8'
        ));

        // Set a page title for the template
        $this->title = 'Hello, ' . $this->name . '!';

        // Return SUCCESS to render indexSuccess.php
        return sfView::SUCCESS;
    }
}
```

> **Convention:** The class name is `{module}Actions` (lowercase module + `Actions`).
> Each action is a public method named `execute{ActionName}()` (PascalCase action name).
> Returning `sfView::SUCCESS` renders `{action}Success.php`.

### Step 7.3 — Create the Template

```php
<?php
// apps/site/modules/hello/templates/indexSuccess.php
// Variables set on $this in the action are available as local variables here.
?>
<div class="page hello-page">
    <h1><?php echo $title ?></h1>
    <p>Hello from 7x Symfony Framework v1.5!</p>
    <p>PHP <?php echo PHP_VERSION ?></p>
    <p><a href="/version">System Status</a></p>
</div>
```

### Step 7.4 — Define a Route

```php
<?php
// apps/site/config/routing.php
// Routes are evaluated top-to-bottom; first match wins.

// Greeting page — /hello or /hello/{name}
$routing->connect('hello', new sfRoute('/hello/:name', [
    'module' => 'hello',
    'action' => 'index',
    'name'   => 'World',            // default value when :name is omitted
], [
    'name' => '[A-Za-z][A-Za-z0-9\-_]{0,31}',   // requirement regex
]));

// … existing routes below …
```

### Step 7.5 — Test the Page

```bash
curl -s http://localhost/hello
# → Greeting with "Hello, World!"

curl -s http://localhost/hello/Alice
# → Greeting with "Hello, Alice!"
```

---

## 8. Routing — Full Reference

Routing is configured in `apps/{app}/config/routing.php`. The `$routing` variable is an
`sfPatternRouting` instance provided by `sfMicroDispatcher`.

### Static Route

```php
$routing->connect('about', new sfRoute('/about', [
    'module' => 'page',
    'action' => 'about',
]));
```

### Route with Parameters

```php
$routing->connect('article_show', new sfRoute('/articles/:slug', [
    'module' => 'article',
    'action' => 'show',
], [
    'slug' => '[a-z0-9\-]+',    // requirement: only lowercase slug characters
]));
```

### Route with Multiple Parameters

```php
$routing->connect('blog_archive', new sfRoute('/blog/:year/:month', [
    'module' => 'blog',
    'action' => 'archive',
    'month'  => null,           // :month is optional
], [
    'year'  => '\d{4}',
    'month' => '\d{2}',
]));
```

### Catch-All Module/Action Route

```php
// Matches /module/action and /module/action/id
$routing->connect('default_index', new sfRoute('/:module', [
    'action' => 'index',
]));
$routing->connect('default', new sfRoute('/:module/:action/*'));
```

### Accessing Route Parameters in Actions

```php
public function executeArchive(): string
{
    $this->year  = $this->getRequest()->getParameter('year');
    $this->month = $this->getRequest()->getParameter('month');
    return sfView::SUCCESS;
}
```

---

## 9. Actions — Request, Parameters, Redirects

### Reading GET / POST Parameters

```php
public function executeSearch(): string
{
    // GET or POST parameter
    $query = $this->getRequest()->getParameter('q', '');
    $page  = (int) $this->getRequest()->getParameter('page', 1);

    // HTTP method check
    if ($this->getRequest()->isMethod('POST')) {
        // process form submission
    }

    $this->query   = htmlspecialchars($query, ENT_QUOTES, 'UTF-8');
    $this->page    = max(1, $page);
    return sfView::SUCCESS;
}
```

### Redirect

```php
public function executeOldUrl(): string
{
    $this->redirect('/new-url', 301);
    return sfView::NONE;   // redirect() outputs headers; no template rendered
}
```

### Forward 404

```php
public function executeShow(): string
{
    $item = $this->findItem();
    if (!$item) {
        $this->forward404();   // renders apps/site/templates/error404.php
    }
    $this->item = $item;
    return sfView::SUCCESS;
}
```

---

## 10. Templates & Layouts

### Passing Variables to Templates

Every `$this->varName = $value;` call in the action becomes `$varName` in the template.

```php
// Action
$this->title    = 'Welcome';
$this->items    = ['Apples', 'Oranges', 'Pears'];
$this->isAdmin  = false;

// Template (indexSuccess.php)
echo $title;                    // Welcome
foreach ($items as $item) { … }
if ($isAdmin) { … }
```

### Global Layout

```php
<?php // apps/site/templates/layout.php ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title><?php echo isset($title) ? htmlspecialchars($title) : '7x App' ?></title>
</head>
<body>
    <header><a href="/">Home</a></header>
    <main>
        <?php echo $content ?>
    </main>
    <footer>&copy; <?php echo date('Y') ?> 7x</footer>
</body>
</html>
```

The template output is injected into `$content` automatically by the dispatcher.

---

## 11. Database Integration — PDO (Built-in)

PDO is available in any standard PHP installation and requires no additional configuration
beyond your database credentials.

### Step 11.1 — Store Credentials Securely

Create a config file **outside the DocumentRoot** or use environment variables:

```bash
# Using a .env-style approach with getenv()
export DB_HOST=127.0.0.1
export DB_NAME=myapp
export DB_USER=myapp_user
export DB_PASS=secret
```

Or store a PHP config file outside the web root:

```php
<?php
// /etc/myapp/db.php  — NOT inside the project DocumentRoot
return [
    'dsn'  => 'mysql:host=127.0.0.1;dbname=myapp;charset=utf8mb4',
    'user' => 'myapp_user',
    'pass' => 'secret',
];
```

### Step 11.2 — Create a PDO Helper (Optional)

```php
<?php
// apps/site/lib/Database.class.php
// sfCoreAutoload discovers every class in apps/site/lib/ automatically.

/*
 * (c) 2004-2026 7x <info@se7enx.com>
 */

class Database
{
    private static ?PDO $connection = null;

    public static function getConnection(): PDO
    {
        if (self::$connection === null) {
            $dsn  = getenv('DB_DSN')  ?: 'mysql:host=127.0.0.1;dbname=myapp;charset=utf8mb4';
            $user = getenv('DB_USER') ?: 'root';
            $pass = getenv('DB_PASS') ?: '';

            self::$connection = new PDO($dsn, $user, $pass, [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES   => false,
            ]);
        }
        return self::$connection;
    }
}
```

### Step 11.3 — Query in an Action

```php
<?php
// apps/site/modules/article/actions/actions.class.php

/*
 * (c) 2004-2026 7x <info@se7enx.com>
 */

class articleActions extends sfMicroAction
{
    // ── List all articles ────────────────────────────────────────────────────
    public function executeIndex(): string
    {
        $pdo = Database::getConnection();

        $stmt = $pdo->prepare(
            'SELECT id, slug, title, excerpt, created_at
             FROM   articles
             WHERE  published = 1
             ORDER  BY created_at DESC
             LIMIT  :limit OFFSET :offset'
        );
        $page  = max(1, (int) $this->getRequest()->getParameter('page', 1));
        $limit = 10;
        $stmt->bindValue(':limit',  $limit,              PDO::PARAM_INT);
        $stmt->bindValue(':offset', ($page - 1) * $limit, PDO::PARAM_INT);
        $stmt->execute();

        $this->articles = $stmt->fetchAll();
        $this->page     = $page;
        $this->title    = 'Articles';
        return sfView::SUCCESS;
    }

    // ── Single article by slug ───────────────────────────────────────────────
    public function executeShow(): string
    {
        $slug = $this->getRequest()->getParameter('slug', '');
        $pdo  = Database::getConnection();

        $stmt = $pdo->prepare(
            'SELECT id, slug, title, body, created_at
             FROM   articles
             WHERE  slug = :slug AND published = 1
             LIMIT  1'
        );
        $stmt->execute([':slug' => $slug]);
        $article = $stmt->fetch();

        if (!$article) {
            $this->forward404();
        }

        $this->article = $article;
        $this->title   = $article['title'];
        return sfView::SUCCESS;
    }

    // ── Insert (POST form) ───────────────────────────────────────────────────
    public function executeCreate(): string
    {
        if (!$this->getRequest()->isMethod('POST')) {
            $this->redirect('/articles');
            return sfView::NONE;
        }

        $title = trim($this->getRequest()->getParameter('title', ''));
        $body  = trim($this->getRequest()->getParameter('body', ''));
        $slug  = strtolower(preg_replace('/[^a-z0-9]+/i', '-', $title));

        if ($title === '' || $body === '') {
            $this->error = 'Title and body are required.';
            return sfView::ERROR;       // renders createError.php
        }

        $pdo  = Database::getConnection();
        $stmt = $pdo->prepare(
            'INSERT INTO articles (slug, title, body, published, created_at)
             VALUES (:slug, :title, :body, 1, NOW())'
        );
        $stmt->execute([
            ':slug'  => $slug,
            ':title' => $title,
            ':body'  => $body,
        ]);

        $this->redirect('/articles/' . $slug, 303);
        return sfView::NONE;
    }
}
```

### Step 11.4 — Display Results in a Template

```php
<?php // apps/site/modules/article/templates/indexSuccess.php ?>
<h1><?php echo htmlspecialchars($title) ?></h1>

<?php if (empty($articles)): ?>
    <p>No articles yet.</p>
<?php else: ?>
<ul class="article-list">
    <?php foreach ($articles as $row): ?>
    <li>
        <a href="/articles/<?php echo htmlspecialchars($row['slug']) ?>">
            <?php echo htmlspecialchars($row['title']) ?>
        </a>
        <time><?php echo htmlspecialchars($row['created_at']) ?></time>
        <p><?php echo htmlspecialchars($row['excerpt']) ?></p>
    </li>
    <?php endforeach ?>
</ul>
<nav>
    <?php if ($page > 1): ?>
        <a href="/articles?page=<?php echo $page - 1 ?>">Previous</a>
    <?php endif ?>
    <a href="/articles?page=<?php echo $page + 1 ?>">Next</a>
</nav>
<?php endif ?>
```

```php
<?php // apps/site/modules/article/templates/showSuccess.php ?>
<article>
    <h1><?php echo htmlspecialchars($article['title']) ?></h1>
    <time><?php echo htmlspecialchars($article['created_at']) ?></time>
    <div class="body">
        <?php echo nl2br(htmlspecialchars($article['body'])) ?>
    </div>
    <p><a href="/articles">← Back to articles</a></p>
</article>
```

### Sample MySQL Table DDL

```sql
CREATE TABLE articles (
    id          INT UNSIGNED      NOT NULL AUTO_INCREMENT PRIMARY KEY,
    slug        VARCHAR(128)      NOT NULL UNIQUE,
    title       VARCHAR(255)      NOT NULL,
    excerpt     TEXT              NULL,
    body        LONGTEXT          NOT NULL,
    published   TINYINT(1)        NOT NULL DEFAULT 0,
    created_at  DATETIME          NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at  DATETIME          NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_published_created (published, created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

---

## 12. Database Integration — sfDoctrinePlugin (ORM)

`sfDoctrinePlugin` wraps Doctrine 1.x ORM. It is bundled in `lib/plugins/sfDoctrinePlugin/`.

### Step 12.1 — Define a Schema

```yaml
# config/doctrine/schema.yml
Article:
  tableName: articles
  columns:
    id:         { type: integer, primary: true, autoincrement: true }
    slug:       { type: string(128), notnull: true, unique: true }
    title:      { type: string(255), notnull: true }
    body:       { type: clob, notnull: true }
    published:  { type: boolean, notnull: true, default: false }
    created_at: { type: timestamp }
    updated_at: { type: timestamp }
```

### Step 12.2 — Generate Model Classes

```bash
php symfony doctrine:build --model
php symfony doctrine:build --sql
php symfony doctrine:create-tables
```

### Step 12.3 — Query in an Action

```php
public function executeIndex(): string
{
    $this->articles = Doctrine_Core::getTable('Article')
        ->createQuery('a')
        ->where('a.published = ?', true)
        ->orderBy('a.created_at DESC')
        ->limit(10)
        ->execute();

    $this->title = 'Articles';
    return sfView::SUCCESS;
}

public function executeShow(): string
{
    $slug = $this->getRequest()->getParameter('slug');

    $this->article = Doctrine_Core::getTable('Article')
        ->findOneBySlug($slug);

    if (!$this->article) {
        $this->forward404();
    }
    $this->title = $this->article->title;
    return sfView::SUCCESS;
}
```

### Step 12.4 — Display in a Template

```php
<?php foreach ($articles as $article): ?>
<li>
    <a href="/articles/<?php echo $article->slug ?>">
        <?php echo htmlspecialchars($article->title) ?>
    </a>
    <time><?php echo $article->created_at ?></time>
</li>
<?php endforeach ?>
```

---

## 13. Database Integration — sfPropelPlugin (ORM)

`sfPropelPlugin` wraps Propel 1.x ORM, bundled in `lib/plugins/sfPropelPlugin/`.

### Step 13.1 — Define a Schema

```xml
<!-- config/schema.xml -->
<database name="myapp" defaultIdMethod="native">
  <table name="articles" phpName="Article">
    <column name="id"         type="INTEGER" primaryKey="true" autoIncrement="true"/>
    <column name="slug"       type="VARCHAR" size="128" required="true"/>
    <column name="title"      type="VARCHAR" size="255" required="true"/>
    <column name="body"       type="LONGVARCHAR" required="true"/>
    <column name="published"  type="BOOLEAN" required="true" default="false"/>
    <column name="created_at" type="TIMESTAMP"/>
    <unique name="unique_slug"><unique-column name="slug"/></unique>
  </table>
</database>
```

### Step 13.2 — Generate Model Classes

```bash
php symfony propel:build --model
php symfony propel:build --sql
php symfony propel:insert-sql
```

### Step 13.3 — Query in an Action

```php
public function executeIndex(): string
{
    $c = new Criteria();
    $c->add(ArticlePeer::PUBLISHED, true);
    $c->addDescendingOrderByColumn(ArticlePeer::CREATED_AT);
    $c->setLimit(10);

    $this->articles = ArticlePeer::doSelect($c);
    $this->title    = 'Articles';
    return sfView::SUCCESS;
}
```

---

## 14. Composer Packages in Actions

After running `composer require vendor/package`, the installed library is available anywhere
via the Composer autoloader (which `index.php` loads if `vendor/autoload.php` exists).

### Example — Carbon Date Formatting

```bash
composer require nesbot/carbon
```

```php
// apps/site/modules/article/actions/actions.class.php
use Carbon\Carbon;

public function executeShow(): string
{
    // … fetch $article from DB …
    $this->article    = $article;
    $this->humanDate  = Carbon::parse($article['created_at'])->diffForHumans();
    return sfView::SUCCESS;
}
```

### Example — Monolog Logging

```bash
composer require monolog/monolog
```

```php
use Monolog\Logger;
use Monolog\Handler\StreamHandler;

public function executeCreate(): string
{
    $log = new Logger('article');
    $log->pushHandler(new StreamHandler(SF_ROOT_DIR.'/apps/site/log/app.log'));
    // …
    $log->info('Article created', ['slug' => $slug]);
    // …
}
```

### Example — Symfony HTTP Client (Packagist)

```bash
composer require symfony/http-client
```

```php
use Symfony\Component\HttpClient\HttpClient;

public function executeFetch(): string
{
    $client       = HttpClient::create();
    $response     = $client->request('GET', 'https://api.example.com/data');
    $this->data   = $response->toArray();
    return sfView::SUCCESS;
}
```

---

## 15. Forms and Validation

symfony1 ships a full form and validator system under `lib/form/` and `lib/validator/`.

### Define a Form Class

```php
<?php
// apps/site/lib/ArticleForm.class.php

/*
 * (c) 2004-2026 7x <info@se7enx.com>
 */

class ArticleForm extends sfForm
{
    public function configure(): void
    {
        $this->setWidgets([
            'title' => new sfWidgetFormInputText(),
            'body'  => new sfWidgetFormTextarea(),
        ]);

        $this->setValidators([
            'title' => new sfValidatorString(['min_length' => 3, 'max_length' => 255]),
            'body'  => new sfValidatorString(['min_length' => 10]),
        ]);

        $this->widgetSchema->setNameFormat('article[%s]');
    }
}
```

### Use the Form in an Action

```php
public function executeNew(): string
{
    $this->form = new ArticleForm();
    return sfView::SUCCESS;
}

public function executeCreate(): string
{
    $this->form = new ArticleForm();
    $this->form->bind($this->getRequest()->getParameter('article', []));

    if ($this->form->isValid()) {
        $values = $this->form->getValues();
        // … save to DB using PDO or ORM …
        $this->redirect('/articles');
        return sfView::NONE;
    }

    return sfView::ERROR;   // re-render form with errors → newError.php
}
```

---

## 16. Running the Lime Test Suite

### Run All Unit Tests

```bash
php test/bin/prove.php test/unit/
```

### Run a Single Test File

```bash
php test/unit/util/sfInflectorTest.php
php test/unit/routing/sfRoutingTest.php
php test/unit/form/sfFormTest.php
```

### Run All Tests

```bash
php test/bin/prove.php test/
```

### Expected Output (all passing)

```
1..42
ok 1 - sfInflector::camelize() converts underscored string
ok 2 - sfInflector::underscore() converts camelCase string
…
All tests passed (42/42).
```

---

## 17. Writing Your Own Tests

Lime is a TAP-based framework. Place test files in `test/unit/` following the `*Test.php`
naming convention.

```php
<?php
// test/unit/lib/ArticleTest.php

/*
 * (c) 2004-2026 7x <info@se7enx.com>
 */

require_once __DIR__.'/../../bootstrap/unit.php';

$t = new lime_test(3);

// Test 1 — slug generation
$slug = strtolower(preg_replace('/[^a-z0-9]+/i', '-', 'Hello World!'));
$t->is($slug, 'hello-world-', 'slug is correctly generated from title');

// Test 2 — trimming trailing dash
$slug = rtrim($slug, '-');
$t->is($slug, 'hello-world', 'trailing dash is trimmed from slug');

// Test 3 — empty string handling
$empty = rtrim(strtolower(preg_replace('/[^a-z0-9]+/i', '-', '')), '-');
$t->is($empty, '', 'empty title produces empty slug');
```

Run it:

```bash
php test/unit/lib/ArticleTest.php
# 1..3
# ok 1 - slug is correctly generated from title
# ok 2 - trailing dash is trimmed from slug
# ok 3 - empty title produces empty slug
```

---

## 18. symfony1 CLI Tasks

The task system is accessed via `php symfony` from the project root.

```bash
# List all available tasks
php symfony list

# Display help for a specific task
php symfony help doctrine:build

# ── Application ──────────────────────────────────────────────────────────────
php symfony generate:app myapp                  # scaffold a new application
php symfony generate:module myapp articles      # scaffold a new module

# ── Cache ────────────────────────────────────────────────────────────────────
php symfony cc                                  # clear all caches

# ── Doctrine ORM ─────────────────────────────────────────────────────────────
php symfony doctrine:build --model              # generate model classes from schema
php symfony doctrine:build --sql                # generate SQL from schema
php symfony doctrine:create-tables              # run CREATE TABLE statements
php symfony doctrine:load-data fixtures/        # load YAML fixture data
php symfony doctrine:dump-data > dump.yml       # dump live data to YAML

# ── Propel ORM ───────────────────────────────────────────────────────────────
php symfony propel:build --model
php symfony propel:insert-sql

# ── Permissions ──────────────────────────────────────────────────────────────
php symfony project:permissions                 # fix directory write permissions

# ── Composer ─────────────────────────────────────────────────────────────────
composer install                                # install packages
composer require vendor/package                 # add a package
composer remove vendor/package                  # remove a package
composer dump-autoload -o                       # rebuild optimised autoloader
composer audit                                  # check for known vulnerabilities
composer show                                   # list installed packages
```

---

## 19. Cache Management

### Clear the symfony1 Cache

```bash
php symfony cc
# or
rm -rf apps/site/cache/*
```

### PHP OPcache (Production)

Ensure `opcache.validate_timestamps=0` in production `php.ini` for maximum performance.
Clear OPcache after deployment:

```bash
php -r "opcache_reset();"
# or via a web endpoint that calls opcache_reset() behind authentication
```

---

## 20. Deployment Checklist

### Pre-Deployment

```bash
# 1. Run the full test suite — all tests must pass
php test/bin/prove.php test/unit/

# 2. Install Composer packages (production mode — no dev deps)
composer install --no-dev --optimize-autoloader

# 3. Review .env / credentials — no secrets committed to git
git log --follow -p .env 2>/dev/null   # should return nothing

# 4. Verify PHP 8.x compatibility
php -l lib/routing/sfRoute.class.php
```

### Post-Deployment

```bash
# 5. Clear the symfony1 cache
php symfony cc

# 6. Set correct file permissions
php symfony project:permissions
# or manually:
chmod -R 777 apps/site/cache apps/site/log

# 7. Confirm the site loads
curl -Is http://yoursite.com/ | head -5
# HTTP/1.1 301 Moved Permanently  (homepage redirects to /version)

curl -Is http://yoursite.com/version | head -5
# HTTP/1.1 200 OK
```

### Security Hardening

- Ensure `vendor/` is not publicly accessible (place above DocumentRoot or deny in web server config)
- Set `SF_DEBUG` to `false` in `index.php` for production
- Use HTTPS — configure TLS in Apache/Nginx and redirect HTTP to HTTPS
- Review database credentials — use environment variables, not hardcoded values
- Run `composer audit` to check installed packages for known CVEs

---

## 21. Troubleshooting

**Q: I get a blank page or 500 error after installation.**
A: Check PHP error logs:
```bash
tail -f /var/log/apache2/error.log
# or
tail -f /var/log/nginx/error.log
```
Enable display errors temporarily in `index.php` (`ini_set('display_errors', 1)`), reproduce the error,
then remove the setting.

---

**Q: Routes are not matching — I always get 404.**
A: Verify `mod_rewrite` is enabled (`a2enmod rewrite`) and `AllowOverride All` is set in your Apache
vhost. For Nginx, confirm the `try_files` directive includes `/index.php?$query_string`.

---

**Q: `sfCoreAutoload` cannot find my class.**
A: Class files in `lib/` must follow the naming convention `ClassName.class.php` and live inside
the `lib/` directory tree. Run `php symfony cc` to rebuild the autoload cache if you added a new class.

---

**Q: Composer packages are not found at runtime.**
A: Verify `vendor/autoload.php` exists (`composer install` must have been run). Check that `index.php`
includes the `require_once SF_ROOT_DIR.'/vendor/autoload.php';` block. Use `composer show` to list
installed packages.

---

**Q: Tests fail with PHP 8.x type errors.**
A: You may be running a version of a test file that was not updated to the PHP 8.x baseline. Check
the `1.5` branch for the latest test files. Report regressions at:
[github.com/se7enxweb/symfony1/issues](https://github.com/se7enxweb/symfony1/issues)

---

**Q: `preg_replace()` deprecated /e modifier warning.**
A: All `/e` modifier usage has been removed from the symfony1 core in v1.5. If you see this warning it
is in custom code or a third-party plugin. Replace with `preg_replace_callback()`.

---

**Q: Database connection fails.**
A: Check DSN format:
```php
// MySQL
$dsn = 'mysql:host=127.0.0.1;port=3306;dbname=myapp;charset=utf8mb4';

// PostgreSQL
$dsn = 'pgsql:host=127.0.0.1;port=5432;dbname=myapp';

// SQLite
$dsn = 'sqlite:/absolute/path/to/myapp.sqlite';
```
Ensure the PHP PDO extension for your database is enabled (`php -m | grep pdo`).

---

**Q: How do I get commercial support?**
A: Contact [support@se7enx.com](mailto:support@se7enx.com) or visit [se7enx.com](https://se7enx.com/).

---

## Copyright

```
Copyright (C) 2004-2026 7x (se7enx.com). All rights reserved.
Copyright (C) 2004-2006 Fabien Potencier <fabien.potencier@symfony-project.com>
Copyright (C) 2004-2006 Sean Kerr <sean@code-box.org>
```

## License

Licensed under the **MIT License**. See [LICENSE](LICENSE) for the full text.
