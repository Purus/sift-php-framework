<?php
/*
 * This file is part of the Sift PHP framework.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * sfActionStackEntry represents information relating to a single sfAction request during a single HTTP request.
 *
 * @package    Sift
 * @subpackage action
 */
class sfActionStackEntry
{
  protected
    $actionInstance = null,
    $actionName     = null,
    $moduleName     = null,
    $presentation   = null,
    $viewInstance   = null;

  /**
   * Class constructor.
   *
   * @param string A module name
   * @param string An action name
   * @param sfAction An sfAction implementation instance
   */
  public function __construct($moduleName, $actionName, $actionInstance)
  {
    $this->actionName     = $actionName;
    $this->actionInstance = $actionInstance;
    $this->moduleName     = $moduleName;
  }

  /**
   * Retrieves this entry's action name.
   *
   * @return string An action name
   */
  public function getActionName()
  {
    return $this->actionName;
  }

  /**
   * Retrieves this entry's action instance.
   *
   * @return sfAction An sfAction implementation instance
   */
  public function getActionInstance()
  {
    return $this->actionInstance;
  }

  /**
   * Retrieves this entry's view instance.
   *
   * @return sfView A sfView implementation instance.
   */
  public function getViewInstance()
  {
    return $this->viewInstance;
  }

  /**
   * Sets this entry's view instance.
   *
   * @param sfView A sfView implementation instance.
   */
  public function setViewInstance($viewInstance)
  {
    $this->viewInstance = $viewInstance;
  }

  /**
   * Retrieves this entry's module name.
   *
   * @return string A module name
   */
  public function getModuleName()
  {
    return $this->moduleName;
  }

  /**
   * Retrieves this entry's rendered view presentation.
   *
   * This will only exist if the view has processed and the render mode is set to sfView::RENDER_VAR.
   *
   * @return string Rendered view presentation
   */
  public function & getPresentation()
  {
    return $this->presentation;
  }

  /**
   * Sets the rendered presentation for this action.
   *
   * @param string A rendered presentation.
   */
  public function setPresentation(&$presentation)
  {
    $this->presentation =& $presentation;
  }
}
