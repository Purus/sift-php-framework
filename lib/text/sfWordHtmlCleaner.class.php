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
      $html = sfUtf8::convertToUtf8($html);
    }
    $html = sfSanitizer::clean($html, 'word');
    // fix newlines
    return str_replace(array("\r\n", "\r"), array("\n", "\n"), $html);
  }
  
}