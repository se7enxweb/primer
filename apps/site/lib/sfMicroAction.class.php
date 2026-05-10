<?php

/*
 * This file is part of the symfony package.
 * (c) 2004-2026 7x <info@se7enx.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * sfMicroAction — lightweight sfAction-compatible base class.
 *
 * Mirrors the symfony1 sfAction / sfComponent API for standalone use.
 * In a full symfony1 project every module action class extends sfActions:
 *
 *   class fooActions extends sfActions { ... }
 *
 * Key symfony1 conventions replicated here
 * ────────────────────────────────────────
 *   execute{Action}()   naming convention for action methods
 *   $this->varName      assigns a template variable (via __set / __get)
 *   getVarHolder()      returns the sfParameterHolder of template vars
 *   initialize()        lifecycle hook called before execute*()
 *   sfView::SUCCESS     return value that signals which template to render
 *
 * @see lib/action/sfComponent.class.php  (symfony1 framework source)
 * @see lib/action/sfAction.class.php     (symfony1 framework source)
 * @see lib/action/sfActions.class.php    (symfony1 framework source)
 */
abstract class sfMicroAction
{
  /** @var sfParameterHolder Template variable bag — mirrors sfComponent::$varHolder */
  protected sfParameterHolder $varHolder;

  // ── Lifecycle ──────────────────────────────────────────────────────────────

  /**
   * Initialises the component.
   *
   * Called automatically by sfMicroDispatcher before execute*().
   * In symfony1 this is sfComponent::initialize(sfContext, module, action).
   */
  public function initialize(): void
  {
    $this->varHolder = new sfParameterHolder();
  }

  // ── Template variable assignment ───────────────────────────────────────────

  /**
   * Assigns a template variable.
   *
   * symfony1 convention: any property assigned inside execute*() becomes
   * a local variable available directly in the template:
   *
   *   // In actions.class.php:
   *   $this->title = 'Hello world';
   *
   *   // In indexSuccess.php:
   *   <?php echo $title ?>
   *
   * Mirrors sfComponent::__set().
   */
  public function __set(string $name, mixed $value): void
  {
    $this->varHolder->set($name, $value);
  }

  /**
   * Reads a template variable back inside execute*().
   * Mirrors sfComponent::__get().
   */
  public function __get(string $name): mixed
  {
    return $this->varHolder->get($name);
  }

  /**
   * Explicit template variable setter.
   * Mirrors sfComponent::setVar().
   */
  public function setVar(string $name, mixed $value): void
  {
    $this->varHolder->set($name, $value);
  }

  /**
   * Explicit template variable getter.
   * Mirrors sfComponent::getVar().
   */
  public function getVar(string $name): mixed
  {
    return $this->varHolder->get($name);
  }

  /**
   * Returns the full template variable holder.
   *
   * The dispatcher calls this to extract all vars into template scope.
   * Mirrors sfComponent::getVarHolder().
   */
  public function getVarHolder(): sfParameterHolder
  {
    return $this->varHolder;
  }

  // ── Navigation ────────────────────────────────────────────────────────────

  /**
   * Issues an HTTP redirect and halts execution.
   *
   * In symfony1 sfAction provides $this->redirect($url, $statusCode = 302)
   * which notifies the 'controller.change_action' event then calls
   * sfWebResponse::setRedirect() before returning sfView::NONE.
   *
   * Here we mirror that contract: emit the Location header, set the status
   * code, and return sfView::NONE so sfMicroDispatcher skips template
   * rendering — exactly as sfAction::redirect() does.
   *
   * @see lib/action/sfAction.class.php ::redirect()  (symfony1 framework)
   */
  public function redirect(string $url, int $statusCode = 302): string
  {
    http_response_code($statusCode);
    header('Location: '.$url);
    return sfView::NONE; // signals dispatcher: no template rendering
  }
}
