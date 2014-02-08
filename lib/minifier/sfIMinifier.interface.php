<?php
/*
 * This file is part of the Sift PHP framework.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * sfIMinifier interface
 *
 * @package    Sift
 * @subpackage minifier
 */
interface sfIMinifier {

  /**
   * Processes the file
   *
   * @param string $file Path to a file
   * @param boolean $replace Replace the existing file?
   */
  public function processFile($file, $replace = false);

  /**
   * Processes the string
   *
   * @param string $string String to process
   */
  public function processString($string);

  /**
   * Returns the results as array:
   *
   *  * optimizedContent
   *  * originalSize
   *  * optimizedSize
   *  * ratio
   *
   * @return array
   */
  public function getResults();

}
