<?php

/*
 * This file is part of the symfony package.
 * (c) 2004-2026 7x <info@se7enx.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * sfMicroDispatcher — mini front-controller dispatcher.
 *
 * Replicates the core symfony1 request / dispatch cycle without requiring
 * a full sfContext or application configuration YAML files.
 *
 * In a real symfony1 project the equivalent machinery lives inside:
 *
 *   sfContext::createInstance($config)->dispatch()
 *     └─ sfFrontWebController::dispatch()
 *          └─ sfWebController::forward(module, action)
 *               ├─ sfComponent::initialize()
 *               ├─ sfComponent::execute()          ← execute{Action}()
 *               └─ sfPHPView::render()
 *                    └─ sfPHPView::decorate()      ← layout wrapping
 *
 * The flow implemented here mirrors that pipeline step for step:
 *
 *   1. loadAppLibs()   — require app/lib/*.class.php  (sfSimpleAutoload)
 *   2. loadRouting()   — connect routes from config/routing.php
 *   3. findRoute()     — sfPatternRouting::findRoute($uri)
 *   4. forward()       — instantiate {module}Actions, call initialize()
 *   5. execute{A}()    — action method returns sfView::SUCCESS / ERROR / NONE
 *   6. render()        — include modules/{m}/templates/{a}{view}.php
 *   7. decorate()      — wrap $content in apps/{app}/templates/layout.php
 *
 * @see lib/controller/sfFrontWebController.class.php  (symfony1 framework)
 * @see lib/controller/sfWebController.class.php       (symfony1 framework)
 * @see lib/view/sfPHPView.class.php                   (symfony1 framework)
 */
class sfMicroDispatcher
{
  private readonly string $appDir;
  private sfEventDispatcher $eventDispatcher;
  private sfPatternRouting  $routing;
  private ?sfWebDebug       $webDebug = null;

  // ── Boot ───────────────────────────────────────────────────────────────────

  public function __construct(
    private readonly string $app,
    private readonly string $env   = 'prod',
    private readonly bool   $debug = false,
  ) {
    $this->appDir          = SF_ROOT_DIR.'/apps/'.$this->app;
    $this->eventDispatcher = new sfEventDispatcher();

    // sfPatternRouting is the real symfony1 URL router
    $this->routing = new sfPatternRouting($this->eventDispatcher, null, [
      'generate_shortest_url'            => false,
      'extra_parameters_as_query_string' => true,
    ]);

    if ($this->debug) {
      // sfConfig so sfWebDebug panels see the values they test for.
      sfConfig::add([
        'sf_debug'           => true,
        'sf_logging_enabled' => true,
        'sf_cache'           => false,
        'sf_web_debug'       => true,
      ]);

      // sfVarLogger captures every log entry emitted via sfEventDispatcher;
      // sfWebDebug reads those entries back to populate the Logs panel.
      $varLogger      = new sfVarLogger($this->eventDispatcher, [
        'level'        => sfLogger::DEBUG,
        'auto_shutdown' => false,
      ]);
      $this->webDebug = new sfWebDebugSf2($this->eventDispatcher, $varLogger, [
        'image_root_path'    => '/sf/sf_web_debug/images',
        'request_parameters' => $_GET,
      ]);
      // Remove panels that require a full sfContext instance (not available
      // in sfMicroDispatcher) to prevent fatal errors.
      $this->webDebug->removePanel('config');
      $this->webDebug->removePanel('view');
    }
  }

  // ── Private pipeline steps ─────────────────────────────────────────────────

  /**
   * Loads app-level lib classes.
   *
   * In symfony1 sfSimpleAutoload scans lib/ directories and registers
   * them with spl_autoload. Here we require them directly.
   */
  private function loadAppLibs(): void
  {
    $libDir = $this->appDir.'/lib';
    if (!is_dir($libDir)) {
      return;
    }
    foreach (glob($libDir.'/*.class.php') ?: [] as $file) {
      require_once $file;
    }
  }

  /**
   * Connects routes from config/routing.php.
   *
   * Mirrors how symfony1 processes routing.yml via sfRoutingConfigHandler
   * and passes each entry to sfPatternRouting::connect().
   *
   * Inside routing.php the variable $routing refers to this dispatcher's
   * sfPatternRouting instance — identical to how symfony1 exposes $routing
   * inside sfRoutingConfigHandler::execute().
   */
  private function loadRouting(): void
  {
    $routing = $this->routing; // exposed to routing.php scope
    require $this->appDir.'/config/routing.php';
  }

  /**
   * Renders a template file with extracted variables.
   *
   * Mirrors sfPHPView::render() — extracts action vars into local scope
   * via extract(), then includes the template file, capturing output.
   */
  private function render(string $templateFile, array $vars): string
  {
    ob_start();
    extract($vars, EXTR_SKIP);
    include $templateFile;
    return (string) ob_get_clean();
  }

  /**
   * Decorates $content with the app layout.
   *
   * Mirrors sfPHPView::decorate() — $content and slot variables are
   * available to layout.php after extraction.
   */
  private function decorate(string $layoutFile, string $content, array $vars): void
  {
    $title = $vars['title'] ?? 'symfony 1.5';
    include $layoutFile;
  }

  // ── Public API ─────────────────────────────────────────────────────────────

  /**
   * Dispatches the current HTTP request.
   *
   * This is the single public entry point — mirrors the call to
   * sfContext::createInstance($config)->dispatch() in a symfony1 front
   * controller.
   */
  public function dispatch(): void
  {
    $this->loadAppLibs();
    $this->loadRouting();

    // ── 1. Route the request ─────────────────────────────────────────────────
    // sfPatternRouting::findRoute() returns an array with:
    //   ['name' => routeName, 'parameters' => [module, action, ...]]
    $uri        = (string) parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH);

    // When the request goes through a visible PHP script (e.g. /index_dev.php/version)
    // Apache does NOT rewrite it, so REQUEST_URI contains the script name.
    // Strip it so sfPatternRouting sees /version instead of /index_dev.php/version.
    $scriptName = $_SERVER['SCRIPT_NAME'] ?? '';
    if ($scriptName !== '' && $scriptName !== '/' && str_starts_with($uri, $scriptName)) {
      $uri = substr($uri, strlen($scriptName)) ?: '/';
    }
    $module     = 'status';
    $action     = 'error404';
    $httpStatus = 200;
    $routeName  = '(none)';

    try {
      $match = $this->routing->findRoute($uri);
      if ($match !== false) {
        $routeName = $match['name'];
        $module    = $match['parameters']['module'] ?? 'status';
        $action    = $match['parameters']['action'] ?? 'index';
      } else {
        $httpStatus = 404;
      }
    } catch (Exception) {
      $httpStatus = 404;
    }

    // ── 2. Load action class ─────────────────────────────────────────────────
    // symfony1 naming: module "foo" → file modules/foo/actions/actions.class.php
    //                                → class fooActions extends sfActions
    $actionsFile = $this->appDir.'/modules/'.$module.'/actions/actions.class.php';

    if (!is_file($actionsFile)) {
      $module      = 'status';
      $action      = 'error404';
      $httpStatus  = 404;
      $actionsFile = $this->appDir.'/modules/'.$module.'/actions/actions.class.php';
    }

    require_once $actionsFile;

    // ── 3. Execute action ────────────────────────────────────────────────────
    // symfony1: module "status", action "index" → statusActions::executeIndex()
    // sfInflector::camelize() is the real symfony1 utility used for this.
    $actionClass  = $module.'Actions';
    $actionMethod = 'execute'.sfInflector::camelize($action);

    /** @var sfMicroAction $instance */
    $instance = new $actionClass();
    $instance->initialize(); // mirrors sfComponent::initialize()

    // The return value of execute*() selects the view suffix:
    //   sfView::SUCCESS → 'Success'  (renders {action}Success.php)
    //   sfView::ERROR   → 'Error'    (renders {action}Error.php)
    //   sfView::NONE    → no render
    $viewName = sfView::SUCCESS;
    if (method_exists($instance, $actionMethod)) {
      $result   = $instance->$actionMethod();
      $viewName = is_string($result) ? $result : sfView::SUCCESS;
    } else {
      $httpStatus = 404;
    }

    if ($viewName === sfView::NONE) {
      return; // action handled the response itself
    }

    // ── 4. Resolve template ───────────────────────────────────────────────────
    // symfony1 template naming: {action}{viewName}.php
    // e.g. action=index, view=Success → indexSuccess.php
    $templateFile = $this->appDir
      .'/modules/'.$module.'/templates/'
      .$action.$viewName.'.php';

    if (!is_file($templateFile)) {
      $httpStatus   = 404;
      $templateFile = $this->appDir.'/templates/error404.php';
    }

    // ── 5. Collect template vars ──────────────────────────────────────────────
    // Merges action vars (from varHolder) with symfony1-style sf_* globals.
    // In symfony1 sfPHPView injects these automatically into every template.
    $vars = $instance->getVarHolder()->getAll() + [
      'sf_app'     => $this->app,
      'sf_env'     => $this->env,
      'sf_debug'   => $this->debug,
      'sf_routing' => $this->routing,
      'sf_route'   => $routeName,
    ];

    // ── 6. Render template (sfPHPView::render) ────────────────────────────────
    $content = $this->render($templateFile, $vars);

    // ── 7. Decorate with layout (sfPHPView::decorate) ─────────────────────────
    $layoutFile = $this->appDir.'/templates/layout.php';

    header('Content-Type: text/html; charset=utf-8');
    http_response_code($httpStatus);

    // When the debug toolbar is active, buffer the full HTML response so
    // sfWebDebug::injectToolbar() can splice its CSS/JS/HTML into the page
    // (before </head> for styles, before </body> for the bar itself).
    if ($this->webDebug !== null) {
      ob_start();
    }

    if (is_file($layoutFile)) {
      $this->decorate($layoutFile, $content, $vars);
    } else {
      echo $content;
    }

    if ($this->webDebug !== null) {
      $this->webDebug->setRequestInfo([
        'method'     => $_SERVER['REQUEST_METHOD'] ?? 'GET',
        'status'     => $httpStatus,
        'route'      => $routeName,
        'controller' => $module.'Actions',
        'action'     => $action,
        'uri'        => $uri,
      ]);
      echo $this->webDebug->injectToolbar((string) ob_get_clean());
    }
  }
}
