<?php
/*
 * This file is part of the Sift PHP framework.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * sfActions executes all the logic for the current request.
 *
 * @package    Sift
 * @subpackage action
 */
abstract class sfActions extends sfAction {

  /**
   * Dispatches to the action defined by the 'action' parameter of the sfRequest object.
   *
   * This method try to execute the executeXXX() method of the current object where XXX is the
   * defined action name.
   *
   * @return string A string containing the view name associated with this action
   * @throws sfInitializationException
   * @see sfAction
   */
  public function execute()
  {
    // dispatch action
    $actionToRun = 'execute' . ucfirst($this->getActionName());

    if($actionToRun === 'execute')
    {
      // no action given
      throw new sfInitializationException(sprintf('sfAction initialization failed for module "%s". There was no action given.', $this->getModuleName()));
    }

    if(!is_callable(array($this, $actionToRun)))
    {
      // action not found
      throw new sfInitializationException(sprintf('sfAction initialization failed for module "%s", action "%s". You must create a "%s" method.', $this->getModuleName(), $this->getActionName(), $actionToRun));
    }

    if(sfConfig::get('sf_logging_enabled'))
    {
      sfLogger::getInstance()->info('{sfAction} Call "' . get_class($this) . '->' . $actionToRun . '()' . '"');
    }

    return $this->$actionToRun();
  }

}
