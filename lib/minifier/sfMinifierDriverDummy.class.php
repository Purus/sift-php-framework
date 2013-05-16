<?php
/*
 * This file is part of the Sift PHP framework.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * Dummy minifier which does nothing with the files.
 *
 * @package    Sift
 * @subpackage minifier
 */
class sfMinifierDriverDummy extends sfMinifier {

  /**
   * Processes the file
   *
   * @param string $file Path to a file
   * @param boolean $replace Replace the existing file?
   */
  public function doProcessFile($file, $replace = false)
  {
    return file_get_contents($file);
  }

  /**
   * Processes the string
   *
   * @param string $string
   * @return string Processed string
   */
  public function processString($string)
  {
    return $string;
  }

}