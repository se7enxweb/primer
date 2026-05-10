<?php

/*
 * This file is part of the symfony package.
 * (c) 2004-2026 7x <info@se7enx.com>
 * (c) 2004-2006 Fabien Potencier <fabien.potencier@symfony-project.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * symfony 1.5 — web front controller.
 *
 * This is the single entry point for all HTTP requests (symfony1 calls it
 * the "front controller"). In a production symfony1 project this file lives
 * in web/index.php and its entire body is typically just three lines:
 *
 *   require_once dirname(__FILE__).'/../config/ProjectConfiguration.class.php';
 *   $configuration = ProjectConfiguration::getApplicationConfiguration('frontend', 'prod', false);
 *   sfContext::createInstance($configuration)->dispatch();
 *
 * Here we use sfMicroDispatcher — a lightweight stand-in that exercises the
 * same symfony1 routing → action → view → layout pipeline using real
 * symfony1 framework classes (sfPatternRouting, sfRoute, sfInflector, …)
 * without requiring a full application configuration.
 *
 * Constants follow symfony1 convention (SF_ROOT_DIR, SF_APP, SF_ENV, SF_DEBUG).
 */

define('SF_ROOT_DIR', realpath(__DIR__));
define('SF_APP',      'site');
define('SF_ENV',      'prod');
define('SF_DEBUG',    true);

// ── Core autoloader ───────────────────────────────────────────────────────────
// Mirrors the require in config/ProjectConfiguration.class.php — registers
// sfCoreAutoload so all symfony1 lib classes are available on demand.
require_once SF_ROOT_DIR.'/lib/autoload/sfCoreAutoload.class.php';
sfCoreAutoload::register();

// ── Composer autoloader (optional) ────────────────────────────────────────────
// When vendor/autoload.php exists (i.e. `composer install` has been run),
// Composer's PSR-4 / classmap autoloader is registered alongside sfCoreAutoload.
// Both can coexist: sfCoreAutoload handles symfony1 lib/ classes first, and
// Composer's loader picks up any installed packages from vendor/.
//
// To install a package:
//   composer require vendor/package-name
//
// In symfony1 plugins served the same role as Composer packages — each plugin
// dropped its classes into lib/plugins/{name}/lib/ and they were discovered by
// sfSimpleAutoload. Composer lets modern packages follow the same pattern
// without requiring framework-specific plugin scaffolding.
if (is_file(SF_ROOT_DIR.'/vendor/autoload.php')) {
  require_once SF_ROOT_DIR.'/vendor/autoload.php';
}

// Load app-level lib classes (sfMicroAction, sfMicroDispatcher).
// In symfony1 sfSimpleAutoload discovers and registers these automatically;
// here we bootstrap them explicitly before the dispatch call.
foreach (glob(SF_ROOT_DIR.'/apps/'.SF_APP.'/lib/*.class.php') ?: [] as $_libFile) {
  require_once $_libFile;
}

// ── Dispatch ──────────────────────────────────────────────────────────────────
// In symfony1 this is sfContext::createInstance($configuration)->dispatch().
// sfMicroDispatcher replicates that pipeline (routing → action → view → layout)
// using the real symfony1 routing and utility classes.
$dispatcher = new sfMicroDispatcher(SF_APP, SF_ENV, SF_DEBUG);
$dispatcher->dispatch();
