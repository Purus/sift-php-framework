<?php
/*
 * This file is part of the Sift PHP framework.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * sfCoreTextFilter class
 *
 * @package    Sift
 * @subpackage text

 */
class sfCoreTextFilter implements sfTextFilter {

  /**
   * Filters fiven text, also applied shortcodes
   *
   * @param string $content
   * @param array $params
   * @return string
   */
  public static function filter($content, $params = array())
  {
    return $content;
  }
  
 }
