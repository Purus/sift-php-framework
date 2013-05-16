<?php
/*
 * This file is part of the Sift PHP framework.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * Simple minifier which strips only comments and tabs, spaces, newlines...
 *
 * @package    Sift
 * @subpackage minifier
 * @see http://castlesblog.com/2010/august/14/php-javascript-css-minification
 */
class sfMinifierDriverSimple extends sfMinifier {

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
   * Compresses the string
   *
   * @param string $buffer
   * @return string
   */
  protected function compress($buffer)
  {
    /* remove comments */
    $buffer = preg_replace("/((?:\/\*(?:[^*]|(?:\*+[^*\/]))*\*+\/)|(?:\/\/.*))/", "", $buffer);
    /* remove tabs, spaces, newlines, etc. */
    $buffer = str_replace(array("\r\n","\r","\t","\n",'  ','    ','     '), '', $buffer);
    /* remove other spaces before/after ) */
    $buffer = preg_replace(array('(( )+\))','(\)( )+)'), ')', $buffer);
    return $buffer;
  }

}