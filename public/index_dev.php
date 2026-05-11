<?php

/*
 * This file is part of the symfony package.
 * (c) 2004-2026 7x <info@se7enx.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * 7x Primer Framework v1.5 — development front controller.
 *
 * This file is the symfony1 convention for a "dev" environment entry point:
 *
 *   public/index_dev.php  →  SF_ENV='dev', SF_DEBUG=true
 *   public/index.php      →  SF_ENV='prod', SF_DEBUG=false
 *
 * SF_DEBUG=true activates:
 *   - The sfWebDebug toolbar (injected before </body> on every HTML response)
 *   - Full PHP error display (E_ALL)
 *   - sfVarLogger capturing every event-dispatcher log entry
 *
 * Access this controller via /index_dev.php/path or configure your web server
 * to use it as the DocumentRoot entry point for the local dev vhost.
 *
 * SECURITY: Restrict access to localhost / trusted IPs only.
 */

// ── IP guard ──────────────────────────────────────────────────────────────────
// Prevents the dev controller from being exposed on a public-facing server.
// Allowed IPs and CIDR ranges are read from apps/site/config/dev.yml so they
// can be managed without touching this file.
$devConfigFile = realpath(__DIR__.'/../apps/site/config/dev.yml');
$allowedIPs    = ['127.0.0.1', '::1']; // safe fallback if file is missing

if ($devConfigFile && is_file($devConfigFile)) {
    // sfYaml is not yet available (autoloader not booted), so use a minimal
    // inline YAML parser sufficient for the simple list structure we write.
    $raw = file_get_contents($devConfigFile);
    if (preg_match_all('/^\s+-\s+[\'"]?([0-9a-fA-F.:\/]+)[\'"]?/m', $raw, $m)) {
        $allowedIPs = $m[1];
    }
}

$remoteIP = $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';

$isAllowed = false;
foreach ($allowedIPs as $range) {
    if (str_contains($range, '/')) {
        // CIDR check
        [$subnet, $bits] = explode('/', $range, 2);
        $mask = ~((1 << (32 - (int)$bits)) - 1);
        if ((ip2long($remoteIP) & $mask) === (ip2long($subnet) & $mask)) {
            $isAllowed = true;
            break;
        }
    } elseif ($remoteIP === $range) {
        $isAllowed = true;
        break;
    }
}

if (!$isAllowed) {
    http_response_code(403);
    exit('This script is only accessible from trusted IP addresses.');
}

// ── Error display ─────────────────────────────────────────────────────────────
ini_set('display_errors', '1');
error_reporting(E_ALL);

// ── Project root ──────────────────────────────────────────────────────────────
$path = realpath(__DIR__);
define('SF_ROOT_DIR', $path . '/../');

define('SF_APP',   'site');
define('SF_ENV',   'dev');
define('SF_DEBUG', true);

// ── Core autoloader ───────────────────────────────────────────────────────────
require_once SF_ROOT_DIR.'/lib/autoload/sfCoreAutoload.class.php';
sfCoreAutoload::register();

// ── Composer autoloader (optional) ────────────────────────────────────────────
if (is_file(SF_ROOT_DIR.'/vendor/autoload.php')) {
    require_once SF_ROOT_DIR.'/vendor/autoload.php';
}

// ── App lib classes ───────────────────────────────────────────────────────────
foreach (glob(SF_ROOT_DIR.'/apps/'.SF_APP.'/lib/*.class.php') ?: [] as $_libFile) {
    require_once $_libFile;
}

// ── Dispatch ──────────────────────────────────────────────────────────────────
// SF_DEBUG=true causes sfMicroDispatcher to boot sfVarLogger + sfWebDebug and
// inject the debug toolbar into every HTML response before </body>.
$dispatcher = new sfMicroDispatcher(SF_APP, SF_ENV, SF_DEBUG);
$dispatcher->dispatch();
