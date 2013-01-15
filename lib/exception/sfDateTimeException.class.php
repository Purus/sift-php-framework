<?php
/*
 * This file is part of the Sift PHP framework.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * sfDateTimeException is thrown when an error occurs while manipulating dates.
 *
 * @package Sift
 * @subpackage exception
 * @author Stephen Riesenberg <sjohnr@gmail.com>
 */
class sfDateTimeException extends sfException {

  /**
   * Class constructor.
   *
   * @param  string  the error message
   * @param  int    the error code
   */
  public function __construct($message = null, $code = 0)
  {
    $this->setName('sfDateTimeException');
    parent::__construct($message, $code);
  }

}
