<?php
/*
 * This file is part of the Sift PHP framework.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * Console controller
 * 
 * @package    Sift
 * @subpackage controller
 */
class sfConsoleController extends sfController
{
  /**
   * Dispatches a request.
   *
   * @param string A module name
   * @param string An action name
   * @param array  An associative array of parameters to be set
   */
  public function dispatch($moduleName, $actionName, $parameters = array())
  {
    try
    {
      // set parameters
      $this->getContext()->getRequest()->getParameterHolder()->add($parameters);

      // make the first request
      $this->forward($moduleName, $actionName);
    }
    catch (sfException $e)
    {
      $e->printStackTrace();
    }
    catch (Exception $e)
    {
      // wrap non Sift exceptions
      $sfException = new sfException();
      $sfException->printStackTrace($e);
    }
  }
}
