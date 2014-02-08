<?php
/*
 * This file is part of the Sift PHP framework.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * View interface
 *
 * @package    Sift
 * @subpackage view
 */
interface sfIView {

  /**
   * Initializes the view
   *
   * @param string $moduleName
   * @param string $actionName
   * @param string $viewName
   */
  public function initialize($moduleName, $actionName, $viewName);

  /**
   * Renders the presentation.
   *
   * When the controller render mode is sfView::RENDER_CLIENT, this method will
   * render the presentation directly to the client and null will be returned.
   *
   * @param array $templateVars An array with variables that will be extracted for the template
   *                If empty, the current actions var holder will be extracted
   * @return string A string representing the rendered presentation, if
   *                the controller render mode is sfView::RENDER_VAR, otherwise null
   */
  public function render($templateVars = array());

  /**
   * Retrieves the template engine associated with this view.
   *
   * @return mixed A template engine instance
   */
  public function getEngine();

  /**
   * Executes any presentation logic and set template attributes.
   */
  public function execute();

  /**
   * Configures template.
   */
  public function configure();

  /**
   * Add helpers
   *
   * @param array $helpers
   */
  public function addHelpers($helpers);

}
