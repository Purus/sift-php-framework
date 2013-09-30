<?php
/*
 * This file is part of the Sift PHP framework.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * sfIController interface
 *
 * @package    Sift
 * @subpackage controller
 */
interface sfIController extends sfIService {

  /**
   * Dispatches a request.
   *
   * This will determine which module and action to use by request parameters specified by the user.
   */
  public function dispatch();

  /**
   * Generates an URL from an array of parameters.
   *
   * @param mixed $parameters An associative array of URL parameters or an internal URI as a string.
   * @param boolean $absolute Whether to generate an absolute URL
   * @param array $getParameters Array of get parameters to append to the url
   * @param string $protocol Alternative protocol for absolute URLs (like "webcal")
   *
   * @return string The generated url
   */
  public function genUrl($parameters = array(), $absolute = false, $getParameters = array(), $protocol = null);

  /**
   * Redirects the request to another URL.
   *
   * @param string $url An existing URL
   * @param integer $statusCode The status code
   */
  public function redirect($url, $statusCode = 302);

  /**
   * Forwards the request to another action.
   *
   * @param string $moduleName A module name
   * @param string $actionName An action name
   */
  public function forward($moduleName, $actionName);

  /**
   * Indicates whether or not a module has a specific action.
   *
   * @param string $moduleName A module name
   * @param string $actionName An action name
   * @return boolean true, if the action exists, otherwise false
   */
  public function actionExists($moduleName, $actionName);

  /**
   * Retrieves the presentation rendering mode.
   *
   * @return int One of the following:
   *             - sfView::RENDER_CLIENT
   *             - sfView::RENDER_VAR
   */
  public function getRenderMode();

  /**
   * Sets the presentation rendering mode.
   *
   * @param integer $mode A rendering mode
   * @throws sfRenderException If an invalid render mode has been set
   */
  public function setRenderMode($mode);

  /**
   * Retrieves a sfView implementation instance.
   *
   * @param string $moduleName A module name
   * @param string $actionName An action name
   * @param string $viewName A view name
   * @return sfView A sfView implementation instance, if the view exists, otherwise null
   */
  public function getView($moduleName, $actionName, $viewName);

  /**
   * Returns the rendered view presentation of a given module/action.
   *
   * @param  string $moduleName A module name
   * @param  string $actionName An action name
   * @param  string $viewName A View class name
   *
   * @return string The generated content
   */
  public function getPresentationFor($moduleName, $actionName, $viewName = null);

  /**
   * Indicates whether or not a module has a specific component.
   *
   * @param string $moduleName A module name
   * @param string $componentName An component name
   * @return boolean true, if the component exists, otherwise false
   */
  public function componentExists($moduleName, $componentName);

}
