<?php
/*
 * This file is part of the symfony package.
 * (c) 2004-2026 7x <info@se7enx.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * status/index action — Success view.
 *
 * symfony1 template naming convention: {action}{viewName}.php
 *   action=index, view=Success → indexSuccess.php
 *
 * Variables are injected into this scope by sfMicroDispatcher::render(),
 * which mirrors sfPHPView::render() — all properties assigned via
 * $this->varName in statusActions::executeIndex() arrive here as locals.
 *
 * symfony1 global template vars (sf_*) are also available:
 *   $sf_app      — application name
 *   $sf_env      — environment (prod / dev / test)
 *   $sf_debug    — debug flag
 *   $sf_routing  — sfPatternRouting instance (for url_for() etc.)
 *   $sf_route    — name of the matched route
 *
 * Available locals from statusActions::executeIndex():
 *   $phpVersion  $sfVersion  $gitBranch  $gitCommit
 *   $checks      $allOk      $title      $sf_route
 */
?>
<style>
  .sf-status-banner {
    border-radius: 6px;
    padding: 1.1rem 1.4rem;
    margin-bottom: 1.4rem;
    background: <?php echo $allOk ? '#2e7d32' : '#c62828' ?>;
    color: #fff;
  }
  .sf-status-banner h2 { font-size: 1rem; font-weight: 700; letter-spacing: .3px; }
  .sf-status-banner p  { font-size: .82rem; opacity: .85; margin-top: .25rem; }

  .sf-card {
    background: #fff;
    border-radius: 6px;
    box-shadow: 0 1px 3px rgba(0,0,0,.08);
    margin-bottom: 1.2rem;
    overflow: hidden;
  }
  .sf-card-head {
    background: #f5f5f5;
    border-bottom: 1px solid #e8e8e8;
    padding: .5rem 1rem;
    font-size: .75rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: .6px;
    color: #666;
  }
  .sf-table { width: 100%; border-collapse: collapse; font-size: .88rem; }
  .sf-table th,
  .sf-table td { padding: .5rem 1rem; border-bottom: 1px solid #f0f0f0; text-align: left; }
  .sf-table tr:last-child th,
  .sf-table tr:last-child td { border-bottom: none; }
  .sf-table th { font-weight: 600; color: #555; width: 44%; }
  .ok   { color: #2e7d32; font-weight: 700; }
  .fail { color: #c62828; font-weight: 700; }
  .badge {
    display: inline-block;
    font-size: .7rem;
    padding: .1rem .45rem;
    border-radius: 10px;
    background: #e8eaf6;
    color: #283593;
    font-weight: 700;
    vertical-align: middle;
    margin-left: .3rem;
  }
</style>

<div class="sf-status-banner">
  <h2><?php echo $allOk ? '&#10003; All checks passed' : '&#10007; Some checks failed' ?></h2>
  <p>Matched route: <strong><?php echo htmlspecialchars($sf_route) ?></strong>
     &nbsp;&bull;&nbsp; env: <strong><?php echo htmlspecialchars($sf_env) ?></strong></p>
</div>

<div class="sf-card">
  <div class="sf-card-head">Environment</div>
  <table class="sf-table">
    <tr>
      <th>PHP Version</th>
      <td><?php echo htmlspecialchars($phpVersion) ?></td>
    </tr>
    <tr>
      <th>Framework Version</th>
      <td>
        <?php echo htmlspecialchars($sfVersion) ?>
        <span class="badge">branch <?php echo htmlspecialchars($gitBranch) ?></span>
      </td>
    </tr>
    <tr>
      <th>Git Branch</th>
      <td><?php echo htmlspecialchars($gitBranch) ?></td>
    </tr>
    <tr>
      <th>Last Commit</th>
      <td><?php echo htmlspecialchars($gitCommit) ?></td>
    </tr>
    <tr>
      <th>App / Module / Action</th>
      <td><?php echo htmlspecialchars($sf_app) ?> / status / index</td>
    </tr>
  </table>
</div>

<div class="sf-card">
  <div class="sf-card-head">Core Class Checks</div>
  <table class="sf-table">
    <?php foreach ($checks as $class => $ok): ?>
    <tr>
      <th><?php echo htmlspecialchars($class) ?></th>
      <td class="<?php echo $ok ? 'ok' : 'fail' ?>"><?php echo $ok ? 'OK' : 'FAIL' ?></td>
    </tr>
    <?php endforeach ?>
  </table>
</div>

<div class="sf-card">
  <div class="sf-card-head">Composer</div>
  <table class="sf-table">
    <tr>
      <th>composer.json</th>
      <td class="<?php echo $composerJson ? 'ok' : 'fail' ?>"><?php echo $composerJson ? 'Present' : 'Not found' ?></td>
    </tr>
    <tr>
      <th>vendor/autoload.php</th>
      <td class="<?php echo $composerInstalled ? 'ok' : 'fail' ?>">
        <?php if ($composerInstalled): ?>
          Loaded &mdash; <?php echo count($composerPackages) ?> package<?php echo count($composerPackages) !== 1 ? 's' : '' ?> installed
        <?php else: ?>
          Not installed &mdash; run <code>composer install</code>
        <?php endif ?>
      </td>
    </tr>
    <?php if ($composerPackages): ?>
    <?php foreach ($composerPackages as $pkg => $ver): ?>
    <tr>
      <th style="padding-left:1.75rem;color:#777;font-weight:500"><?php echo htmlspecialchars($pkg) ?></th>
      <td><?php echo htmlspecialchars($ver) ?></td>
    </tr>
    <?php endforeach ?>
    <?php endif ?>
  </table>
</div>
