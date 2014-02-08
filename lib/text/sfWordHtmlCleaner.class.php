<?php
/*
 * This file is part of the Sift PHP framework.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * sfWordHtmlCleaner - cleans up Word Html! Damn Micro$oft!
 * HTML source code cleaner (great help for cleaning MS Word content)
 *
 * @package    Sift
 * @subpackage text
 */
class sfWordHtmlCleaner {

  // The following constant allow for nice looking callbacks to static methods
  const CLEAN = 'sfWordHtmlCleaner::clean';

  /**
   * Cleans up word html, also convert to utf8 (second argument)
   *
   * @param string $html
   * @param boolean $convertToUtf8
   * @return string
   */
  public static function clean($html, $convertToUtf8 = true)
  {
    if($convertToUtf8)
    {
      $html = self::convertToUtf8($html);
    }

    return self::fixNewLines(
      sfSanitizer::sanitize($html, 'word')
    );
  }

  /**
   * Converts the html to utf-8
   *
   * @param string $html
   * @return string
   */
  protected static function convertToUtf8($html)
  {
    return sfUtf8::convertToUtf8($html);
  }

  /**
   * Fixes new lines
   *
   * @param string $html
   * @return string
   */
  protected static function fixNewLines($html)
  {
    return str_replace(array("\r\n", "\r"), array("\n", "\n"), $html);
  }

}
