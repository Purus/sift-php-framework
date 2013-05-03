<?php
/*
 * This file is part of the Sift PHP framework.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * sfContextException is thrown when an instance of the context has not been created.
 *
 * @package    Sift
 * @subpackage exception
 */
class sfContextException extends sfException
{
  /**
   * Class constructor.
   *
   * @param string The error message
   * @param int    The error code
   */
  public function __construct($message = null, $code = 0)
  {
    parent::__construct($message, $code);
    $this->setName('sfContextException');
  }
}
