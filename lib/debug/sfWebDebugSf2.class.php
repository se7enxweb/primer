<?php

/*
 * 7x Primer Framework – SF2-style debug toolbar skin
 *
 * Subclass of sfWebDebug that replaces the classic grey top-right bar with a
 * dark, full-width, bottom-fixed bar modelled on the Symfony 2/3/4 profiler.
 *
 * Only getStylesheet() and injectToolbar() are overridden; all panel logic is
 * inherited unchanged from sfWebDebug.
 */
class sfWebDebugSf2 extends sfWebDebug
{
  /** @var array<string,mixed> */
  protected array $requestInfo = [
    'method'     => 'GET',
    'status'     => 200,
    'route'      => '(none)',
    'controller' => '',
    'action'     => '',
    'uri'        => '/',
  ];

  /**
   * Called by sfMicroDispatcher after routing to supply request metadata.
   *
   * @param array<string,mixed> $info  Keys: method, status, route, controller, action, uri
   */
  public function setRequestInfo(array $info): void
  {
    $this->requestInfo = array_merge($this->requestInfo, $info);
  }

  /**
   * Returns the CSS to inject into the page <head>.
   *
   * @return string
   */
  public function getStylesheet(): string
  {
    return <<<'CSS'
/* ── 7x Primer · SF2-style Debug Toolbar ─────────────────────────────────── */

/* Push page content up so the fixed bar doesn't cover the footer */
body.sfWdActive { padding-bottom: 36px !important; }

/* ── Outer wrapper ── */
#sfWebDebug {
  all: initial;
  font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
  font-size: 12px;
  line-height: 1;
  color: #eee;
  position: fixed;
  bottom: 0;
  left: 0;
  right: 0;
  z-index: 99999;
  text-align: left;
}
#sfWebDebug *, #sfWebDebug *::before, #sfWebDebug *::after {
  box-sizing: border-box;
}

/* ── The bar ── */
#sfWebDebugBar {
  display: flex;
  align-items: stretch;
  background: #222;
  height: 36px;
  padding: 0;
  margin: 0;
  opacity: 1;
  filter: none;
  position: static;
  border-top: 1px solid #444;
  overflow: hidden;
  width: 100%;
}

/* ── Left info block ── */
#sfWebDebugInfo {
  display: flex;
  align-items: stretch;
  flex-shrink: 0;
  border-right: 1px solid #922b21;
}
/* Logo pill */
#sfWebDebugInfo .sfWdLogo {
  display: flex;
  align-items: center;
  justify-content: center;
  padding: 0 12px;
  background: #c0392b;
  cursor: pointer;
  transition: background 0.15s;
  text-decoration: none !important;
  border: none !important;
}
#sfWebDebugInfo .sfWdLogo:hover { background: #e74c3c; }
#sfWebDebugInfo .sfWdLogo img {
  width: 20px;
  height: 20px;
  vertical-align: middle;
  margin: 0;
  float: none;
  display: block;
}
/* Request metadata cells */
#sfWebDebugInfo .sfWdCell {
  display: flex;
  flex-direction: column;
  justify-content: center;
  padding: 0 16px;
  border-left: 1px solid #3a3a3a;
  line-height: 1.35;
  min-width: 0;
}
#sfWebDebugInfo .sfWdCell .sfWdLabel {
  font-size: 9px;
  text-transform: uppercase;
  letter-spacing: 0.08em;
  color: #999;
  white-space: nowrap;
  margin-bottom: 2px;
}
#sfWebDebugInfo .sfWdCell .sfWdValue {
  font-size: 13px;
  font-weight: 600;
  color: #fff;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
  max-width: 240px;
}
/* HTTP status badge — bright vivid colours */
#sfWebDebugInfo .sfWdStatus-2xx .sfWdValue { color: #4ade80; }
#sfWebDebugInfo .sfWdStatus-3xx .sfWdValue { color: #38bdf8; }
#sfWebDebugInfo .sfWdStatus-4xx .sfWdValue { color: #fb923c; }
#sfWebDebugInfo .sfWdStatus-5xx .sfWdValue { color: #f87171; }
/* HTTP method badge — bright white on a coloured pill */
#sfWebDebugInfo .sfWdMethod .sfWdValue {
  display: inline-block;
  background: #1d4ed8;
  border-radius: 4px;
  padding: 2px 9px;
  font-size: 11px;
  font-weight: 800;
  letter-spacing: 0.06em;
  color: #fff;
  text-shadow: none;
}
/* Route / controller — slightly accent-tinted */
#sfWebDebugInfo .sfWdCell:not(.sfWdMethod):not([class*="sfWdStatus"]) .sfWdValue {
  color: #e2e8f0;
}
#sfWebDebugInfo .sfWdCell .sfWdValue strong {
  color: #93c5fd;
  font-weight: 700;
}
/* Time cell — icon sits inline with value */
#sfWebDebugInfo .sfWdTimeVal img {
  width: 13px;
  height: 13px;
  vertical-align: middle;
  filter: brightness(0) invert(0.85);
  margin: 0 3px 0 0;
}
#sfWebDebugInfo .sfWdTimeCell:hover { background: #2d2d2d; }

/* ── Panel list ── */
#sfWebDebugDetails {
  display: flex;
  align-items: stretch;
  list-style: none;
  margin: 0;
  padding: 0;
  flex: 1;
  overflow: hidden;
}
/* Two-line label + value layout */
#sfWebDebugDetails li {
  display: flex;
  flex-direction: column;
  align-items: flex-start;
  justify-content: center;
  padding: 0 12px;
  border-right: 1px solid #333;
  white-space: nowrap;
  cursor: pointer;
  font-size: 12px;
  color: #fff;
  transition: background 0.14s, color 0.14s;
  position: relative;
}
#sfWebDebugDetails li:hover { background: #2d2d2d; }

/* Column-flex the <a> too so label/value stack inside it */
#sfWebDebugDetails li a {
  display: flex;
  flex-direction: column;
  align-items: flex-start;
  gap: 0;
  color: inherit !important;
  text-decoration: none !important;
  background: transparent !important;
  border: none !important;
}

/* Tiny uppercase category label (e.g. "Logs", "Memory") */
.sfWdBarLabel {
  font-size: 9px;
  font-weight: 600;
  text-transform: uppercase;
  letter-spacing: 0.07em;
  color: #888;
  line-height: 1;
  margin-bottom: 2px;
  display: block;
}
#sfWebDebugDetails li:hover .sfWdBarLabel { color: #aaa; }

/* The actual value row (icon + text) */
.sfWdBarValue {
  display: flex;
  align-items: center;
  gap: 4px;
  font-size: 12px;
  font-weight: 600;
  color: #fff;
  line-height: 1;
}

/* Images/icons inside the bar list items */
#sfWebDebugDetails li img {
  width: 14px;
  height: 14px;
  vertical-align: middle;
  filter: brightness(0) invert(0.85);
  margin: 0;
  float: none;
  display: inline-block;
}
#sfWebDebugDetails li:hover img { filter: brightness(0) invert(1); }

/* ── CSS tooltip on hover ── */
#sfWebDebugDetails li[title]:hover::after {
  content: attr(title);
  position: absolute;
  bottom: calc(100% + 10px);
  left: 50%;
  transform: translateX(-50%);
  background: #111;
  color: #e2e8f0;
  padding: 6px 12px;
  border-radius: 5px;
  font-size: 11px;
  font-weight: 500;
  white-space: nowrap;
  border: 1px solid #555;
  box-shadow: 0 4px 14px rgba(0,0,0,0.65);
  pointer-events: none;
  z-index: 100002;
  letter-spacing: 0.02em;
}
#sfWebDebugDetails li[title]:hover::before {
  content: '';
  position: absolute;
  bottom: calc(100% + 4px);
  left: 50%;
  transform: translateX(-50%);
  border: 5px solid transparent;
  border-top-color: #555;
  pointer-events: none;
  z-index: 100003;
}

/* ── Status-coloured panel blocks ── */
#sfWebDebugDetails li.sfWebDebugInfo {
  background: #1a3550;
  color: #7eb8e8;
}
#sfWebDebugDetails li.sfWebDebugInfo a { color: #7eb8e8 !important; }
#sfWebDebugDetails li.sfWebDebugInfo img { filter: brightness(0) invert(0.55) sepia(1) saturate(3) hue-rotate(180deg); }
#sfWebDebugDetails li.sfWebDebugInfo:hover { background: #204570; color: #aad4f5; }

#sfWebDebugDetails li.sfWebDebugWarning {
  background: #4a3800;
  color: #f0c040;
}
#sfWebDebugDetails li.sfWebDebugWarning a { color: #f0c040 !important; }
#sfWebDebugDetails li.sfWebDebugWarning img { filter: brightness(0) invert(0.8) sepia(1) saturate(4) hue-rotate(10deg); }
#sfWebDebugDetails li.sfWebDebugWarning:hover { background: #6b5200; }

#sfWebDebugDetails li.sfWebDebugError {
  background: #4a1515;
  color: #f08080;
}
#sfWebDebugDetails li.sfWebDebugError a { color: #f08080 !important; }
#sfWebDebugDetails li.sfWebDebugError img { filter: brightness(0) invert(0.8) sepia(1) saturate(4) hue-rotate(300deg); }
#sfWebDebugDetails li.sfWebDebugError:hover { background: #6e2020; }

/* ── Symfony version (plain text block, no panel) ── */
#sfWebDebugDetails li #sfWebDebugSymfonyVersion {
  font-size: 12px;
  font-weight: 700;
  color: #e2e8f0;
}

/* ── Close button (last item, pushed right) ── */
#sfWebDebugDetails li.last {
  margin-left: auto;
  border-right: none;
  border-left: 1px solid #333;
  padding: 0 14px;
}
#sfWebDebugDetails li.last img { filter: brightness(0) invert(0.4); }
#sfWebDebugDetails li.last:hover img { filter: brightness(0) invert(0.9); }

/* ── Kernel version pill — pinned second-to-last ── */
#sfWebDebugDetails li.sfWdVersionLi {
  border-left: 1px solid #3a3a3a;
  border-right: none;
  padding: 0 14px;
  margin-left: auto;
}
/* Keep close button from double-pushing right */
#sfWebDebugDetails li.sfWdVersionLi ~ li.last {
  margin-left: 0;
}

/* ── Panel detail popups (slide up from bar) ── */
.sfWebDebugTop {
  position: fixed;
  bottom: 36px;
  left: 0;
  right: 0;
  max-height: 60vh;
  overflow-y: auto;
  background: #1e1e1e;
  color: #ccc;
  border-top: 2px solid #c0392b;
  padding: 16px 22px 14px;
  font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
  font-size: 12px;
  z-index: 99998;
  box-shadow: 0 -6px 24px rgba(0, 0, 0, 0.55);
}
.sfWebDebugTop h1 {
  margin: 0 0 14px;
  font-size: 13px;
  font-weight: 700;
  color: #fff;
  letter-spacing: 0.03em;
  text-transform: uppercase;
  border-bottom: 1px solid #383838;
  padding-bottom: 10px;
}

/* Tables inside panels */
.sfWebDebugTop table {
  width: 100%;
  border-collapse: collapse;
  font-size: 11px;
  margin-bottom: 10px;
}
.sfWebDebugTop th {
  background: #2a2a2a;
  color: #999;
  text-align: left;
  padding: 5px 10px;
  font-weight: 600;
  border: 1px solid #383838;
}
.sfWebDebugTop td {
  padding: 4px 10px;
  border: 1px solid #2a2a2a;
  color: #ccc;
  vertical-align: top;
}
.sfWebDebugTop tr:nth-child(even) td { background: #202020; }

/* Log-level row tinting */
.sfWebDebugTop tr.sfWebDebugError td   { background: #3a1515; color: #f09090; }
.sfWebDebugTop tr.sfWebDebugWarning td { background: #3a2e00; color: #e0b030; }
.sfWebDebugTop tr.sfWebDebugInfo td    { background: #0e1e30; color: #7eb8e8; }

/* Inline status badges */
.sfWebDebugTop .sfWebDebugWarning { color: #f0c040; }
.sfWebDebugTop .sfWebDebugError   { color: #f07070; }
.sfWebDebugTop .sfWebDebugInfo    { color: #7eb8e8; }

/* Links */
.sfWebDebugTop a            { color: #7eb8e8; text-decoration: underline; }
.sfWebDebugTop a:hover      { color: #aad4f5; }

/* Icons inside panel content */
.sfWebDebugTop img {
  width: 14px;
  height: 14px;
  vertical-align: middle;
  filter: brightness(0) invert(0.7);
}

/* Monospace */
.sfWebDebugTop code,
.sfWebDebugTop pre {
  font-family: "SFMono-Regular", Consolas, "Liberation Mono", Menlo, monospace;
  font-size: 11px;
  background: #111;
  color: #a8d8a8;
  padding: 1px 5px;
  border-radius: 3px;
}

/* Toggle buttons */
.sfWebDebugTop ul { margin: 6px 0; padding: 0 0 0 18px; }
.sfWebDebugTop ul li { margin: 2px 0; color: #bbb; }

/* ── Logs panel ──────────────────────────────────────────────────────────── */

/* Filter bar */
ul#sfWebDebugLogMenu {
  display: flex;
  align-items: center;
  gap: 6px;
  list-style: none;
  margin: 0 0 14px;
  padding: 8px 12px;
  background: #2a2a2a;
  border: 1px solid #383838;
  border-radius: 5px;
  flex-wrap: wrap;
}
ul#sfWebDebugLogMenu li {
  margin: 0;
  padding: 0;
  color: inherit;
}
/* Divider between [all]/[none] and filter buttons */
ul#sfWebDebugLogMenu li:nth-child(2) {
  margin-right: 6px;
  padding-right: 12px;
  border-right: 1px solid #444;
}

/* All buttons/links in the filter bar */
ul#sfWebDebugLogMenu li a {
  display: inline-flex;
  align-items: center;
  gap: 5px;
  padding: 4px 10px;
  border-radius: 4px;
  font-size: 11px;
  font-weight: 600;
  color: #ccc !important;
  background: #333;
  border: 1px solid #484848;
  text-decoration: none !important;
  white-space: nowrap;
  transition: background 0.12s, border-color 0.12s, color 0.12s;
  cursor: pointer;
}
ul#sfWebDebugLogMenu li a:hover {
  background: #444;
  border-color: #666;
  color: #fff !important;
}

/* Images inside the filter buttons — show them inline with a label via ::after */
ul#sfWebDebugLogMenu li a img {
  width: 14px;
  height: 14px;
  filter: brightness(0) invert(0.7);
  margin: 0;
}
ul#sfWebDebugLogMenu li a:hover img { filter: brightness(0) invert(1); }

/* Give the three icon-only filter buttons a text label */
ul#sfWebDebugLogMenu li a[onclick*="'info'"] img     { filter: brightness(0) invert(0.55) sepia(1) saturate(3) hue-rotate(180deg); }
ul#sfWebDebugLogMenu li a[onclick*="'warning'"] img  { filter: brightness(0) invert(0.8)  sepia(1) saturate(4) hue-rotate(10deg); }
ul#sfWebDebugLogMenu li a[onclick*="'error'"] img    { filter: brightness(0) invert(0.8)  sepia(1) saturate(4) hue-rotate(300deg); }

ul#sfWebDebugLogMenu li a[onclick*="'info'"]::after     { content: "Info";    font-size: 11px; color: #7eb8e8; }
ul#sfWebDebugLogMenu li a[onclick*="'warning'"]::after  { content: "Warning"; font-size: 11px; color: #f0c040; }
ul#sfWebDebugLogMenu li a[onclick*="'error'"]::after    { content: "Error";   font-size: 11px; color: #f07070; }

/* Log table */
table.sfWebDebugLogs {
  width: 100%;
  border-collapse: collapse;
  font-size: 11px;
}
table.sfWebDebugLogs th {
  background: #2a2a2a;
  color: #aaa;
  text-align: left;
  padding: 5px 10px;
  font-weight: 700;
  text-transform: uppercase;
  letter-spacing: 0.05em;
  border-bottom: 2px solid #383838;
}
table.sfWebDebugLogs td {
  padding: 4px 10px;
  border-bottom: 1px solid #2a2a2a;
  vertical-align: top;
  color: #ccc;
}
table.sfWebDebugLogs .sfWebDebugLogNumber { color: #666; width: 2.5em; text-align: right; }
table.sfWebDebugLogs .sfWebDebugLogType   { white-space: nowrap; color: #aaa; }
table.sfWebDebugLogs .sfWebDebugLogType img { width: 13px; height: 13px; vertical-align: middle; margin-right: 4px; }

/* Row tinting */
table.sfWebDebugLogs tr.sfWebDebugError   td { background: #2c1010; color: #f09090; }
table.sfWebDebugLogs tr.sfWebDebugWarning td { background: #2c2200; color: #e0b030; }
table.sfWebDebugLogs tr.sfWebDebugInfo    td { background: #0c1a28; color: #7eb8e8; }
table.sfWebDebugLogs tr:hover td { filter: brightness(1.15); }

/* ── Minimized state ── */
/* When sfWdMin is set the toolbar collapses to a circular badge floating */
/* 3rem above the bottom-right corner. Click it to restore the full bar.  */
#sfWebDebug.sfWdMin {
  bottom: 1.6rem;
  right: 1.25rem;
  left: auto;
  width: auto;
  transition: bottom 0.2s;
}
body.sfWdActive.sfWdMinActive { padding-bottom: 0 !important; }
#sfWebDebug.sfWdMin #sfWebDebugBar {
  width: 40px;
  height: 40px;
  border-radius: 50%;
  border-top: none;
  box-shadow: 0 4px 14px rgba(0,0,0,0.55);
  overflow: hidden;
  cursor: pointer;
  transition: transform 0.15s, box-shadow 0.15s;
}
#sfWebDebug.sfWdMin #sfWebDebugBar:hover {
  transform: scale(1.12);
  box-shadow: 0 6px 20px rgba(0,0,0,0.7);
}
/* Show only the red logo cell; hide info cells and panel list */
#sfWebDebug.sfWdMin #sfWebDebugInfo .sfWdCell { display: none !important; }
#sfWebDebug.sfWdMin #sfWebDebugInfo { border-right: none; }
#sfWebDebug.sfWdMin #sfWebDebugInfo .sfWdLogo {
  width: 40px;
  border-radius: 50%;
  padding: 0;
}
#sfWebDebug.sfWdMin #sfWebDebugDetails { display: none !important; }
/* Also hide any open panels */
#sfWebDebug.sfWdMin .sfWebDebugTop { display: none !important; }

/* ── Collapsed state (sfWdCol) ── */
/* Bar stays full-width at bottom but the right-hand panel list is hidden. */
/* Click the logo to restore. Persists across reloads via localStorage.    */
#sfWebDebug.sfWdCol #sfWebDebugDetails li:not(.last) { display: none !important; }
#sfWebDebug.sfWdCol .sfWebDebugTop    { display: none !important; }
#sfWebDebug.sfWdCol #sfWebDebugInfo { opacity: 0.65; }
/* Version li is hidden so sibling rule no longer neutralises margin-left on × */
#sfWebDebug.sfWdCol #sfWebDebugDetails li.last { margin-left: auto; }
CSS;
  }

  /**
   * Returns the HTML for the toolbar bar + panel divs.
   * Overrides parent only to swap the close button onclick to sfWebDebugMinimize().
   *
   * @return string
   */
  public function asHtml(): string
  {
    $current = isset($this->options['request_parameters']['sfWebDebugPanel'])
      ? $this->options['request_parameters']['sfWebDebugPanel'] : null;

    $titles = [];
    $panels = [];
    $versionLi = ''; // rendered separately, pinned before the close button
    foreach ($this->panels as $name => $panel) {
      if ($title = $panel->getTitle()) {
        // Time panel is shown in the info block — skip it from the right-hand list
        // but still register its detail panel so clicking the Time cell opens it
        if ($name === 'time') {
          if (($content = $panel->getPanelContent()) || $panel->getTitleUrl()) {
            $panels[] = sprintf(
              '<div id="%s" class="sfWebDebugTop" style="display:none"><h1>%s</h1>%s</div>',
              'sfWebDebugTimeDetails',
              htmlspecialchars((string) $panel->getPanelTitle(), ENT_QUOTES, 'UTF-8'),
              $content
            );
          }
          continue;
        }
        // Version panel is pinned to the right — pull it out of the flow
        if ($name === 'symfony_version') {
          $lbl = htmlspecialchars((string) $panel->getPanelTitle(), ENT_QUOTES, 'UTF-8');
          $versionLi = sprintf(
            '<li class="sfWdVersionLi" title="%s"><small class="sfWdBarLabel">%s</small><span class="sfWdBarValue">%s</span></li>',
            $lbl, $lbl, $title
          );
          continue;
        }
        if (($content = $panel->getPanelContent()) || $panel->getTitleUrl()) {
          $id       = sprintf('sfWebDebug%sDetails', $name);
          $lbl      = htmlspecialchars((string) $panel->getPanelTitle(), ENT_QUOTES, 'UTF-8');
          $titles[] = sprintf(
            '<li%s title="%s"><a href="%s"%s><small class="sfWdBarLabel">%s</small><span class="sfWdBarValue">%s</span></a></li>',
            $panel->getStatus() ? ' class="sfWebDebug'.ucfirst($this->getPriority($panel->getStatus())).'"' : '',
            $lbl,
            $panel->getTitleUrl() ? $panel->getTitleUrl() : '#',
            $panel->getTitleUrl() ? '' : ' onclick="sfWebDebugShowDetailsFor(\''.$id.'\'); return false;"',
            $lbl,
            $title
          );
          $panels[] = sprintf(
            '<div id="%s" class="sfWebDebugTop" style="display:%s"><h1>%s</h1>%s</div>',
            $id,
            $name == $current ? 'block' : 'none',
            $panel->getPanelTitle(),
            $content
          );
        } else {
          $lbl      = htmlspecialchars((string) $panel->getPanelTitle(), ENT_QUOTES, 'UTF-8');
          $titles[] = sprintf('<li title="%s"><small class="sfWdBarLabel">%s</small><span class="sfWdBarValue">%s</span></li>', $lbl, $lbl, $title);
        }
      }
    }

    // Build the left info block
    $ri     = $this->requestInfo;
    $status = (int) $ri['status'];
    $statusClass = 'sfWdStatus-'.(($status >= 500) ? '5xx' : (($status >= 400) ? '4xx' : (($status >= 300) ? '3xx' : '2xx')));
    $method = htmlspecialchars((string) $ri['method'], ENT_QUOTES, 'UTF-8');
    $route  = htmlspecialchars((string) $ri['route'],  ENT_QUOTES, 'UTF-8');
    $ctrl   = htmlspecialchars((string) $ri['controller'], ENT_QUOTES, 'UTF-8');
    $act    = htmlspecialchars((string) $ri['action'],     ENT_QUOTES, 'UTF-8');

    // Pull time from the timer panel if available
    $timeValue = '';
    if (isset($this->panels['time']) && $this->panels['time'] instanceof sfWebDebugPanelTimer) {
      $timeValue = $this->panels['time']->getTotalTimeValue();
    }

    $logoOnClick = 'var _el=document.getElementById(\'sfWebDebug\');if(_el.classList.contains(\'sfWdMin\')||_el.classList.contains(\'sfWdCol\')){sfWebDebugRestore();}else{sfWebDebugCollapse();} return false;';

    $timerId = 'sfWebDebugTimeDetails';
    $timePanel = $timeValue !== '' && isset($this->panels['time']) && ($this->panels['time']->getPanelContent()) ? '
        <div class="sfWdCell sfWdTimeCell" onclick="sfWebDebugShowDetailsFor(\''.$timerId.'\'); return false;" style="cursor:pointer">
          <span class="sfWdLabel">Time</span>
          <span class="sfWdValue sfWdTimeVal"><img src="'.$this->options['image_root_path'].'/time.png" alt="Time" /> '.$timeValue.'</span>
        </div>' : ($timeValue !== '' ? '
        <div class="sfWdCell">
          <span class="sfWdLabel">Time</span>
          <span class="sfWdValue sfWdTimeVal"><img src="'.$this->options['image_root_path'].'/time.png" alt="Time" /> '.$timeValue.'</span>
        </div>' : '');

    $infoBlock = '
      <div id="sfWebDebugInfo">
        <a class="sfWdLogo" href="#" onclick="'.$logoOnClick.'">
          <img src="'.$this->options['image_root_path'].'/sf.png" alt="7x Primer" />
        </a>
        <div class="sfWdCell sfWdMethod">
          <span class="sfWdLabel">Method</span>
          <span class="sfWdValue">'.$method.'</span>
        </div>
        <div class="sfWdCell '.$statusClass.'">
          <span class="sfWdLabel">Status</span>
          <span class="sfWdValue">'.$status.'</span>
        </div>
        <div class="sfWdCell">
          <span class="sfWdLabel">Route</span>
          <span class="sfWdValue" title="'.$route.'">'.$route.'</span>
        </div>
        '.($ctrl !== '' ? '
        <div class="sfWdCell">
          <span class="sfWdLabel">Controller</span>
          <span class="sfWdValue" title="'.$ctrl.'::'.$act.'">'.$ctrl.'::<strong>'.$act.'</strong></span>
        </div>' : '').'
        '.$timePanel.'
      </div>';

    return '
      <div id="sfWebDebug">
        <div id="sfWebDebugBar">
          '.$infoBlock.'

          <ul id="sfWebDebugDetails" class="sfWebDebugMenu">
            '.implode("\n", $titles).'
            '.$versionLi.'
            <li class="last" title="Minimize toolbar">
              <a href="#" onclick="sfWebDebugMinimize(); return false;"><img src="'.$this->options['image_root_path'].'/close.png" alt="Minimize" /></a>
            </li>
          </ul>
        </div>

        '.implode("\n", $panels).'
      </div>
    ';
  }

  /**
   * Injects the toolbar + stylesheet into the HTML response.
   * Identical to the parent except it also adds the `sfWdActive` body class
   * so `padding-bottom` is applied without needing to touch the layout.
   *
   * @param string $content
   * @return string
   */
  public function injectToolbar($content): string
  {
    if (function_exists('mb_stripos')) {
      $posFunction    = 'mb_stripos';
      $posrFunction   = 'mb_strripos';
      $substrFunction = 'mb_substr';
    } else {
      $posFunction    = 'stripos';
      $posrFunction   = 'strripos';
      $substrFunction = 'substr';
    }

    // Inject stylesheet before </head>
    if (false !== $pos = $posFunction($content, '</head>')) {
      $styles  = '<style type="text/css">'.str_replace(["\r", "\n"], ' ', $this->getStylesheet()).'</style>';
      $content = $substrFunction($content, 0, $pos).$styles.$substrFunction($content, $pos);
    }

    // Inject body-class script + JS + toolbar HTML before </body>
    // localStorage key is namespaced to this host+path so multiple apps don't collide
    $minimizeJs = '<script>'."\n"
      .'var sfWdLsKey="sfWdMin__"+location.hostname+location.pathname;'
      .'var sfWdColKey="sfWdCol__"+location.hostname+location.pathname;'
      ."\n"
      .'function sfWebDebugMinimize(){'
      .'var el=document.getElementById(\'sfWebDebug\');'
      .'var panels=sfWebDebugGetElementsByClassName(\'sfWebDebugTop\');'
      .'for(var i=0;i<panels.length;i++){panels[i].style.display=\'none\';}'
      .'el.classList.remove(\'sfWdCol\');'
      .'el.classList.add(\'sfWdMin\');'
      .'document.body.classList.remove(\'sfWdActive\');'
      .'try{localStorage.removeItem(sfWdColKey);localStorage.setItem(sfWdLsKey,"1");}catch(e){}'
      .'}'
      ."\n"
      .'function sfWebDebugCollapse(){'
      .'var el=document.getElementById(\'sfWebDebug\');'
      .'var panels=sfWebDebugGetElementsByClassName(\'sfWebDebugTop\');'
      .'for(var i=0;i<panels.length;i++){panels[i].style.display=\'none\';}'
      .'el.classList.remove(\'sfWdMin\');'
      .'el.classList.add(\'sfWdCol\');'
      .'try{localStorage.removeItem(sfWdLsKey);localStorage.setItem(sfWdColKey,"1");}catch(e){}'
      .'}'
      ."\n"
      .'function sfWebDebugRestore(){'
      .'var el=document.getElementById(\'sfWebDebug\');'
      .'el.classList.remove(\'sfWdMin\');'
      .'el.classList.remove(\'sfWdCol\');'
      .'document.body.classList.add(\'sfWdActive\');'
      .'try{localStorage.removeItem(sfWdLsKey);localStorage.removeItem(sfWdColKey);}catch(e){}'
      .'}'
      ."\n"
      .'</script>';
    // Init script runs inline AFTER toolbar HTML so #sfWebDebug is already in the DOM —
    // no DOMContentLoaded needed, which prevents the full-bar flash on reload.
    $initScript = '<script>'
      .'(function(){'
      .'var mk="sfWdMin__"+location.hostname+location.pathname;'
      .'var ck="sfWdCol__"+location.hostname+location.pathname;'
      .'var isMin=false,isCol=false;'
      .'try{isMin=localStorage.getItem(mk)==="1";isCol=localStorage.getItem(ck)==="1";}catch(e){}'
      .'if(isMin){sfWebDebugMinimize();}else if(isCol){sfWebDebugCollapse();}else{document.body.classList.add("sfWdActive");}'
      .'})();</script>';
    $debug = $this->asHtml();

    if (false === $pos = $posrFunction($content, '</body>')) {
      $content .= '<script type="text/javascript">'.$this->getJavascript().'</script>'.$minimizeJs.$debug.$initScript;
    } else {
      $content = $substrFunction($content, 0, $pos)
        .'<script type="text/javascript">'.$this->getJavascript().'</script>'
        .$minimizeJs
        .$debug
        .$initScript
        .$substrFunction($content, $pos);
    }

    return $content;
  }
}
