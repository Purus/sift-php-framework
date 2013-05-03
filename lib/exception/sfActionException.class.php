<?php
/*
 * This file is part of the Sift PHP framework.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * sfActionException is thrown when an error occurs in an action.
 *
 * @package    Sift
 * @subpackage exception
 */
class sfActionException extends sfException
{
  /**
   * Class constructor.
   *
   * @param string The error message
   * @param int    The error code
   */
  public function __construct($message = null, $code = 0)
  {
    $this->setName('sfActionException');
    parent::__construct($message, $code);
  }

}
