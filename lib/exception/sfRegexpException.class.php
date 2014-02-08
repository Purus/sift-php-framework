<?php
/*
 * This file is part of the Sift PHP framework.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * The exception that indicates error of the last Regexp execution.
 *
 * @package Sift
 * @subpackage exception
 */
class sfRegexpException extends sfException
{
  /**
   * Array of error messages
   *
   * @var array
   */
  public static $messages = array(
    PREG_INTERNAL_ERROR => 'Internal error',
    PREG_BACKTRACK_LIMIT_ERROR => 'Backtrack limit was exhausted',
    PREG_RECURSION_LIMIT_ERROR => 'Recursion limit was exhausted',
    PREG_BAD_UTF8_ERROR => 'Malformed UTF-8 data',
    5 => 'Offset didn\'t correspond to the begin of a valid UTF-8 code point', // PREG_BAD_UTF8_OFFSET_ERROR
  );

  /**
   * Constructor
   *
   * @param string $message The exception message
   * @param integer $code The error code (preg_last_error())
   * @param string $pattern The pattern which caused the error
   */
  public function __construct($message, $code = null, $pattern = null)
  {
    if (!$message) {
      $message = (isset(self::$messages[$code]) ? self::$messages[$code] : 'Unknown error') .
                 ($pattern ? " (pattern: $pattern)" : '');
    }
    parent::__construct($message, $code);
  }

}
