<?php
/*
 * This file is part of the Sift PHP framework.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * Php version of perl's MIME::Base64::URLSafe, that provides an 
 * url-safe base64 string encoding/decoding 
 * (compatible with python base64's urlsafe methods)
 * 
 * @see http://cz2.php.net/manual/en/function.base64-encode.php#63543
 * @package    Sift
 * @subpackage util
 */
class sfSafeUrl {

  /**
   * Encodes the string for usage in urls
   *
   * @param string $string
   * @return string
   */
  public static function encode($string)
  {
    $data = base64_encode($string);
    $data = str_replace(array('+','/','='), array('-','_',''), $data);
    return $data;
  }

  /**
   * Decodes the string back from encoded version
   *
   * @param string $string Encoded string
   * @return string
   */
  public static function decode($string)
  {
    $data = str_replace(array('-','_'), array('+','/'), $string);
    $mod4 = strlen($data) % 4;
    if($mod4)
    {
      $data .= substr('====', $mod4);
    }
    return base64_decode($data, true);
  }

}
