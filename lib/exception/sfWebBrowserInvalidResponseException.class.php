<?php
/*
 * This file is part of the Sift PHP framework.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * This exception is thrown when response sent to browser is not in a valid format.
 *
 * @package Sift
 * @subpackage exception
 */
class sfWebBrowserInvalidResponseException extends sfException {

  /**
   * Class constructor.
   *
   * @param string The error message
   * @param int    The error code
   */
  public function __construct($message = null, $code = 0)
  {
    $this->setName('sfWebBrowserInvalidResponseException');
    parent::__construct($message, $code);
  }

}
