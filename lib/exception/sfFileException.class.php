<?php
/*
 * This file is part of the Sift PHP framework.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * sfFileException is thrown when an error occurs while moving an uploaded file,
 * when file is not readable or does not exist.
 *
 * @package    Sift
 * @subpackage exception
 */
class sfFileException extends sfException
{
  /**
   * Class constructor.
   *
   * @param string The error message
   * @param int    The error code
   */
  public function __construct($message = null, $code = 0)
  {
    $this->setName('sfFileException');
    parent::__construct($message, $code);
  }
}
