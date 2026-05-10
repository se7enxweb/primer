<?php

/*
 * This file is part of the symfony package.
 * (c) 2004-2026 7x <info@se7enx.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * Status module actions.
 *
 * In symfony1 every module contains exactly one actions class that extends
 * sfActions. Each public execute*() method handles one action and assigns
 * template variables via $this->varName.
 *
 * symfony1 dispatch conventions
 * ─────────────────────────────
 *   Module directory : apps/{app}/modules/status/
 *   Actions class    : statusActions  (module name + "Actions")
 *   Action method    : executeIndex() (execute + ucfirst(action))
 *   Success template : modules/status/templates/indexSuccess.php
 *
 * The return value selects the view:
 *   sfView::SUCCESS  → renders {action}Success.php  (default)
 *   sfView::ERROR    → renders {action}Error.php
 *   sfView::NONE     → no template rendered (action handled output itself)
 *
 * @see lib/action/sfActions.class.php   (symfony1 framework)
 * @see lib/action/sfAction.class.php    (symfony1 framework)
 * @see lib/action/sfComponent.class.php (symfony1 framework)
 */
class statusActions extends sfMicroAction
{
  /**
   * Executes the homepage action.
   *
   * Routed from:   GET /
   * Behaviour:     301 permanent redirect to /version
   *
   * In symfony1 a redirect action typically returns sfView::NONE after
   * calling $this->redirect(), which causes sfFrontWebController to skip
   * template rendering and emit the Location header directly.
   *
   * @return string sfView::NONE (redirect — no template rendered)
   */
  public function executeHomepage(): string
  {
    // $this->redirect() mirrors sfAction::redirect() — sets Location header
    // and returns sfView::NONE to tell the dispatcher to stop rendering.
    return $this->redirect('/version', 301);
  }

  /**
   * Executes the index action.
   *
   * Routed from:   GET /
   * Renders:       modules/status/templates/indexSuccess.php
   *
   * Every property assigned to $this inside an execute*() method becomes
   * a local variable in the corresponding template — this is the core
   * symfony1 MVC contract between action and view.
   *
   * @return string sfView::SUCCESS
   */
  public function executeIndex(): string
  {
    // ── Environment ───────────────────────────────────────────────────────
    $this->phpVersion = PHP_VERSION;
    $this->sfVersion  = '1.5-dev (PHP 8.x upgrade of symfony 1.4)';
    $this->gitBranch  = trim(
      shell_exec('git -C '.escapeshellarg(SF_ROOT_DIR).' rev-parse --abbrev-ref HEAD 2>/dev/null') ?: 'unknown'
    );
    $this->gitCommit  = trim(
      shell_exec('git -C '.escapeshellarg(SF_ROOT_DIR).' log -1 --format="%h %s" 2>/dev/null') ?: 'unknown'
    );

    // ── Composer integration ──────────────────────────────────────────────
    $composerJson     = SF_ROOT_DIR.'/composer.json';
    $composerLock     = SF_ROOT_DIR.'/composer.lock';
    $composerVendor   = SF_ROOT_DIR.'/vendor/autoload.php';

    $this->composerInstalled = is_file($composerVendor);
    $this->composerJson      = is_file($composerJson);
    $this->composerPackages  = [];

    if ($this->composerInstalled && is_file($composerLock)) {
      $lock = json_decode(file_get_contents($composerLock), true);
      $this->composerPackages = array_column($lock['packages'] ?? [], 'version', 'name');
    }

    // ── Core class availability checks ────────────────────────────────────
    // Verifies the symfony1 autoloader correctly resolves key framework classes.
    $this->checks = [
      'sfCoreAutoload'    => class_exists('sfCoreAutoload'),
      'sfYaml'            => class_exists('sfYaml'),
      'sfInflector'       => class_exists('sfInflector'),
      'sfFinder'          => class_exists('sfFinder'),
      'sfEventDispatcher' => class_exists('sfEventDispatcher'),
      'sfPatternRouting'  => class_exists('sfPatternRouting'),
      'sfForm'            => class_exists('sfForm'),
      'sfValidatorBase'   => class_exists('sfValidatorBase'),
    ];

    $this->allOk = !in_array(false, $this->checks, true);
    $this->title  = 'symfony 1.5 — Test Installation';

    // Returning sfView::SUCCESS causes the dispatcher to render indexSuccess.php
    // Returning sfView::ERROR  would render indexError.php instead
    // Returning sfView::NONE   would skip rendering entirely
    return sfView::SUCCESS;
  }
}
