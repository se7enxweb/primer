<?php
/*
 * This file is part of the symfony package.
 * (c) 2004-2026 7x <info@se7enx.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
?>
<style>
  .sf-404 { text-align: center; padding: 3.5rem 1rem; }
  .sf-404 h2 { font-size: 5rem; font-weight: 800; color: #e0e0e0; line-height: 1; }
  .sf-404 p  { color: #888; margin-top: .6rem; font-size: .9rem; }
  .sf-404 a  { color: #1a237e; text-decoration: none; }
</style>
<div class="sf-404">
  <h2>404</h2>
  <p>No page found at <code><?php echo htmlspecialchars($_SERVER['REQUEST_URI'] ?? '/') ?></code></p>
  <p style="margin-top:1rem"><a href="/">&#8592; Back to home</a></p>
</div>
