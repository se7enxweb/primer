<?php

/*
 * This file is part of the symfony package.
 * (c) 2004-2026 7x <info@se7enx.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * site application — route configuration.
 *
 * In a full symfony1 project this lives in apps/{app}/config/routing.yml
 * and is processed by sfRoutingConfigHandler, which converts each YAML
 * entry into an sfRoute (or subclass) and registers it via:
 *
 *   $routing->connect($name, new sfRoute($url, $defaults, $requirements));
 *
 * The equivalent routing.yml for these routes would be:
 *
 *   homepage:
 *     url:   /
 *     param: { module: status, action: index }
 *
 *   default_index:
 *     url:   /:module
 *     param: { action: index }
 *
 *   default:
 *     url:   /:module/:action
 *     param: {  }
 *
 * $routing is injected by sfMicroDispatcher::loadRouting() — identical to
 * how sfRoutingConfigHandler exposes the routing object during boot.
 *
 * @see lib/routing/sfPatternRouting.class.php  (symfony1 framework)
 * @see lib/routing/sfRoute.class.php           (symfony1 framework)
 */

// Homepage — redirects to /version (mirrors a symfony1 sfAction::redirect() pattern).
// In routing.yml this would be:
//
//   homepage:
//     url:   /
//     param: { module: status, action: homepage }
//
$routing->connect('homepage', new sfRoute('/', [
  'module' => 'status',
  'action' => 'homepage',
]));

// Version status page — the main content route.
// Equivalent routing.yml entry:
//
//   version:
//     url:   /version
//     param: { module: status, action: index }
//
$routing->connect('version', new sfRoute('/version', [
  'module' => 'status',
  'action' => 'index',
]));

// symfony1 default catch-all routes (always registered last)
$routing->connect('default_index', new sfRoute('/:module', [
  'action' => 'index',
]));

$routing->connect('default', new sfRoute('/:module/:action', []));
