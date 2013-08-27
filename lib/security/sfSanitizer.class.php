<?php
/*
 * This file is part of the Sift PHP framework.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * sfSanitizer sanitizes string using HTML Purifier. This is a utility wrapper
 * around sfHtmlSanitizer.
 *
 * @package    Sift
 * @subpackage security
 */
class sfSanitizer {

  // The following constant allow for nice looking callbacks to static methods
  const sanitize = 'sfSanitizer::sanitize';

  /**
   * Returns the instance of sfHTMLPurifier, configured for given type.
   *
   * @param string $type Type of the configuration setting to use (see sanitize.yml for more info)
   * @return sfHtmlPurifier
   */
  public static function getHtmlPurifier($type = 'strict')
  {
    return sfHtmlPurifier::instance($type);
  }

  /**
   * @see xssClean()
   */
  public static function sanitize($string, $type = 'strict')
  {
    return self::xssClean($string, $type);
  }

  /**
   * Removes broken HTML and XSS from text using [HTMLPurifier](http://htmlpurifier.org/).
   *
   * $text = sfSanitizer::xssClean('message');
   *
   * The original content is returned with all broken HTML and XSS removed.
   *
   * @param string|array $string Text to clean, or an array to clean recursively
   * @return mixed
   */
  public static function xssClean($string, $type = 'strict')
  {
    return self::getHtmlPurifier($type)->purify($string);
  }

  /**
   * __callstatic only on PHP 5.3+
   *
   * Provides methods like: sfSanitizer::strict('string') which is the same as
   * sfSanitizer::sanitize('string', 'strict');
   *
   * @param string $m method
   * @param string $a arguments
   */
  static function __callstatic($m, $a)
  {
    array_push($a, $m);
    return call_user_func_array(array('sfSanitizer', 'sanitize'), $a);
  }

}
