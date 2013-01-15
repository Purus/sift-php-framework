<?php
/*
 * This file is part of the Sift PHP framework.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
 
/**
 * Encodes integer valus to be used by shortening services or request parameters
 *
 * @package Sift
 * @subpackage util
 * @link http://stackoverflow.com/questions/959957/php-short-hash 
 */
class sfIntegerEncoder {

  /**
   * Code set
   * Readable character set excluded (0,O,1,l)
   */ 
  const CODESET = '23456789abcdefghijkmnopqrstuvwxyzABCDEFGHIJKLMNPQRSTUVWXYZ';

  /**
   * Encodes the integer
   * 
   * @param integer $n
   * @return string
   */
  public static function encode($n)
  {
    $n = (string)$n;    
    $base       = strlen(self::CODESET);
    $converted  = '';
    while($n > 0)
    {
      $converted = substr(self::CODESET, bcmod($n, $base), 1) . $converted;
      $n = self::bcFloor(bcdiv($n, $base));
    }
    return (string)$converted;
  }

  /**
   * Decodes the string 
   * 
   * @param string $code
   * @return integer
   */
  public static function decode($code)
  {
    $base = strlen(self::CODESET);
    $c    = '0';
    for($i = strlen($code); $i; $i--)
    {
      $c = bcadd($c, bcmul(strpos(self::CODESET, substr($code, (-1 * ( $i - strlen($code) )), 1)), bcpow($base, $i - 1)));
    }
    return (integer)bcmul($c, 1, 0);
  }

  /**
   * Floors the integer
   * 
   * @param integer $x
   * @return integer
   */
  private static function bcFloor($x)
  {
    return bcmul($x, '1', 0);
  }

}
