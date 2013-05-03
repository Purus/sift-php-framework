<?php
/*
 * This file is part of the Sift PHP framework.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * sfValidatorException is thrown when an error occurs in a validator.
 *
 * @package    Sift
 * @subpackage exception
 * @deprecated
 */
class sfValidatorException extends sfException
{
  /**
   * Class constructor.
   *
   * @param string The error message
   * @param int    The error code
   */
  public function __construct($message = null, $code = 0)
  {
    $this->setName('sfValidatorException');
    parent::__construct($message, $code);
  }
}
