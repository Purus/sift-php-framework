<?php
/*
 * This file is part of the Sift PHP framework.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * sfViewException is thrown when an error occurs in a view.
 *
 * @package    Sift
 * @subpackage exception
 */
class sfViewException extends sfException
{
  /**
   * Class constructor.
   *
   * @param string The error message
   * @param int    The error code
   */
  public function __construct($message = null, $code = 0)
  {
    $this->setName('sfViewException');
    parent::__construct($message, $code);
  }
}
