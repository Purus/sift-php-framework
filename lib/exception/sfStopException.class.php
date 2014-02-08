<?php
/*
 * This file is part of the Sift PHP framework.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * sfStopException is thrown when you want to stop action flow.
 *
 * @package    Sift
 * @subpackage exception
 */
class sfStopException extends sfException
{
  /**
   * Class constructor.
   *
   * @param string The error message
   * @param int    The error code
   */
  public function __construct($message = null, $code = 0)
  {
    // disable xdebug to avoid backtrace in error log
    if (function_exists('xdebug_disable')) {
      xdebug_disable();
    }

    parent::__construct($message, $code);
  }

  /**
   * Stops the current action.
   */
  public function printStackTrace(Exception $exception = null)
  {
  }
}
