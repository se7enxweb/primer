<?php
/*
 * This file is part of the symfony package.
 * (c) 2004-2026 7x <info@se7enx.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * Application layout — decorator template.
 *
 * In symfony1 the layout file wraps every action's rendered output.
 * sfPHPView::decorate() renders the action template first, stores the
 * result in $content, then includes this file — exactly as done here
 * by sfMicroDispatcher::decorate().
 *
 * Available variables
 * ───────────────────
 *   $content  — the rendered action template (injected by dispatcher)
 *   $title    — page title (set via $this->title in the action)
 *   $sf_app   — application name
 *   $sf_env   — current environment
 *
 * @see lib/view/sfPHPView.class.php  (symfony1 framework)
 */
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title><?php echo htmlspecialchars($title ?? 'symfony 1.5') ?></title>
  <style>
    *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

    body {
      font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, sans-serif;
      background: #f0f2f5;
      color: #2c2c2c;
      min-height: 100vh;
      display: flex;
      flex-direction: column;
    }

    /* ── Header ──────────────────────────────────────────────── */
    .sf-header {
      background: #1a237e;
      color: #fff;
      padding: .9rem 1.75rem;
      display: flex;
      align-items: center;
      gap: .85rem;
    }
    .sf-header-logo {
      font-size: 1.3rem;
      font-weight: 800;
      letter-spacing: -0.5px;
    }
    .sf-header-logo em {
      font-style: normal;
      color: #7986cb;
    }
    .sf-header-pill {
      font-size: .72rem;
      background: rgba(255,255,255,.15);
      border-radius: 20px;
      padding: .2rem .65rem;
      letter-spacing: .3px;
    }
    .sf-header-env {
      margin-left: auto;
      font-size: .72rem;
      opacity: .6;
      font-family: monospace;
    }

    /* ── Main ────────────────────────────────────────────────── */
    .sf-main {
      flex: 1;
      max-width: 780px;
      width: 100%;
      margin: 0 auto;
      padding: 1.75rem 1.25rem;
    }

    /* ── Footer ──────────────────────────────────────────────── */
    .sf-footer {
      background: #fff;
      border-top: 1px solid #e0e0e0;
      text-align: center;
      padding: .85rem 1rem;
      font-size: .78rem;
      color: #999;
    }
    .sf-footer a { color: #1a237e; text-decoration: none; }
    .sf-footer a:hover { text-decoration: underline; }
  </style>
</head>
<body>

  <header class="sf-header">
    <span class="sf-header-logo">7x Symfony<em>One</em></span>
    <span class="sf-header-pill">1.5-dev &bull; PHP 8.5.x Compatible</span>
    <span class="sf-header-env"><?php echo htmlspecialchars($sf_app ?? '') ?>/<?php echo htmlspecialchars($sf_env ?? '') ?></span>
  </header>

  <main class="sf-main">
    <?php echo $content ?>
  </main>

  <footer class="sf-footer">
    &copy; 2004 &ndash;2026 <a href="https://se7enx.com">7x</a>
    &mdash; <a href="https://github.com/se7enxweb/symfony1">7x Symfony 1.5 on GitHub</a>
  </footer>

</body>
</html>
