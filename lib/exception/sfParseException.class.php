<?php
/*
 * This file is part of the Sift PHP framework.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * sfParseException is thrown when a parsing procedure fails to complete
 * successfully.
 *
 * @package    Sift
 * @subpackage exception
 */
class sfParseException extends sfException
{
  /**
   * Class constructor.
   *
   * @param string The error message
   * @param int    The error code
   */
  public function __construct($message = null, $code = 0)
  {
    $this->setName('sfParseException');
    parent::__construct($message, $code);
  }
}
