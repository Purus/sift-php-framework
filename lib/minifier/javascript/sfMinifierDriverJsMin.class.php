<?php

/*
 * This file is part of the Sift PHP framework.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

// load vendor library
require_once dirname(__FILE__).'/../../vendor/js_min/JSMin.php';

/**
 * Minifies javascript using modified JsMin from minify project.
 *
 * @package    Sift
 * @subpackage minifier
 * @see https://github.com/mrclay/minify
 */
class sfMinifierDriverJsMin extends sfMinifier
{
  /**
   * Processes the file
   *
   * @param string $file Path to a file
   * @param boolean $replace Replace the existing file?
   */
  public function doProcessFile($file, $replace = false)
  {
    return $this->compress(file_get_contents($file));
  }

  /**
   * Processes the string
   *
   * @param string $string
   * @return string Processed string
   */
  public function processString($string)
  {
    return $this->compress($string);
  }

  /**
   * Compresses the string using JsMin
   *
   * @param string $string
   * @return string
   */
  protected function compress($string)
  {
    return JsMin::minify($string);
  }

}
