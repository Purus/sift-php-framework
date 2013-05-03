<?php
/*
 * This file is part of the Sift PHP framework.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * sfFactoryException is thrown when an error occurs while attempting to create
 * a new factory implementation instance.
 *
 * @package    Sift
 * @subpackage exception
 */
class sfFactoryException extends sfException
{
  /**
   * Class constructor.
   *
   * @param string The error message
   * @param int    The error code
   */
  public function __construct($message = null, $code = 0)
  {
    $this->setName('sfFactoryException');
    parent::__construct($message, $code);
  }
}
