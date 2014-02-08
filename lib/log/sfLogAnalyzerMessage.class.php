<?php
/*
 * This file is part of the Sift PHP framework.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * The entry from analyzed log file
 *
 * @package    Sift
 * @subpackage log
 */
class sfLogAnalyzerMessage
{
  /**
   * The message
   *
   * @var string
   */
  protected $message;

  /**
   * Extra parameters
   *
   * @var array
   */
  protected $extra = array();

  /**
   * Constructs the
   *
   * @param string $message
   * @param string $extra
   */
  public function __construct($message, $extra = '')
  {
    $this->message = $message;
    $this->extra = (array) json_decode((string) $extra, true);
  }

  /**
   * Returns the message
   *
   * @return string
   */
  public function getMessage()
  {
    return $this->message;
  }

  /**
   * Returns the extra parameters
   *
   * @return array
   */
  public function getExtra()
  {
    return $this->extra;
  }

  /**
   * Converts the message to string
   *
   * @return string
   */
  public function __toString()
  {
    // FIXME: display extra
    return $this->message;
  }

}
