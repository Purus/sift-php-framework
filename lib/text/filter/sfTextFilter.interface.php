<?php
/*
 * This file is part of the Sift PHP framework.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * sfTextFilter interface
 *
 * @package    Sift
 * @subpackage text
 */
interface sfTextFilter {

  /**
   * Filter given text
   *
   * @param string $text
   * @param array  $params
   */
  public static function filter($text, $params = array());
  
}
