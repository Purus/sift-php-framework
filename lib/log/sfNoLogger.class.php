<?php
/*
 * This file is part of the Sift PHP framework.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * Logs to nowhere.
 *
 * @package    Sift
 * @subpackage log
 * @author     Fabien Potencier <fabien.potencier@symfony-project.com>
 */
class sfNoLogger
{
  /**
   * Initializes the logger.
   *
   * @param array Options for the logger
   */
  public function initialize($options = array())
  {
  }

  /**
   * Logs a message.
   *
   * @param string Message
   * @param string Message priority
   * @param string Message priority name
   */
  public function log($message, $priority, $priorityName, $appName = 'Sift')
  {
  }

  /**
   * Executes the shutdown method.
   */
  public function shutdown()
  {
  }
  
}
