<?php
/*
 * This file is part of the Sift PHP framework.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * sfArray provides utility methods for dealing with arrays or objects which implements ArrayAccess interface
 *
 * @package    Sift
 * @subpackage util
 */
class sfArray
{
  /**
   * Gets a dot-notated key from an array, with a default value if it does
   * not exist.
   *
   * @param   array   $array    The search array
   * @param   mixed   $key      The dot-notated key or array of keys
   * @param   string  $default  The default value
   * @return  mixed
   * @throws  InvalidArgumentException
   */
  public static function get($array, $key, $default = null)
  {
    if (!is_array($array) && !$array instanceof ArrayAccess) {
      throw new InvalidArgumentException('First parameter must be an array or ArrayAccess object.');
    }

    if (is_null($key)) {
      return $array;
    }

    if (is_array($key)) {
      $return = array();
      foreach ($key as $k) {
        $return[$k] = self::get($array, $k, $default);
      }

      return $return;
    }

    foreach (explode('.', $key) as $key_part) {
      if (($array instanceof ArrayAccess && isset($array[$key_part])) === false) {
        if (!is_array($array) || !array_key_exists($key_part, $array)) {
          return sfToolkit::getValue($default);
        }
      }

      $array = $array[$key_part];
    }

    return $array;
  }

  /**
   * Set an array item (dot-notated) to the value.
   *
   * @param   array   $array  The array to insert it into
   * @param   mixed   $key    The dot-notated key to set or array of keys
   * @param   mixed   $value  The value
   * @return  void
   */
  public static function set(&$array, $key, $value = null)
  {
    if (is_null($key)) {
      $array = $value;

      return;
    }

    if (is_array($key)) {
      foreach ($key as $k => $v) {
        self::set($array, $k, $v);
      }
    } else {
      $keys = explode('.', $key);
      while (count($keys) > 1) {
        $key = array_shift($keys);
        if (!isset($array[$key]) || !is_array($array[$key])) {
          $array[$key] = array();
        }
        $array = &$array[$key];
      }

      $array[array_shift($keys)] = $value;
    }
  }

  /**
   * Array_key_exists with support for a dot-notated key from an array.
   *
   * @param   array   $array    The search array
   * @param   mixed   $key      The dot-notated key or array of keys
   * @return  mixed
   */
  public static function keyExists($array, $key)
  {
    if (strpos($key, '.') === false) {
      return array_key_exists($key, $array);
    }

    foreach (explode('.', $key) as $key_part) {
      if (!is_array($array) || !array_key_exists($key_part, $array)) {
        return false;
      }

      $array = $array[$key_part];
    }

    return true;
  }

}
