<?php
/*
 * This file is part of the Sift PHP framework.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * sfDatabaseException is thrown when a database related error occurs.
 *
 * @package    Sift
 * @subpackage exception
 */
class sfDatabaseException extends sfException
{
  /**
   * Class constructor.
   *
   * @param string The error message
   * @param int    The error code
   */
  public function __construct($message = null, $code = 0)
  {
    $this->setName('sfDatabaseException');
    parent::__construct($message, $code);
  }
}
