<?php
/*
 * This file is part of the Sift PHP framework.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
 
/**
 * Converts values to specific types.
 *
 * Methods of this class deal with converting values from one
 * type to another. It removes any XSS attacks from strings and protects
 * against any kind of attacks if used at GET or POST parameters.
 *
 * @package symfony
 * @subpackage security
 * @author hardrock
 * @see http://trac.symfony-project.org/ticket/1201
 */
class sfConvert {

  /**
   * Converts a value to a string
   *
   * Characters will be converted to their HTML pendants, if available
   * (& to &amp; etc.)
   *
   * @param mixed $value
   * @return string
   */
  public static function toString($value)
  {
    return htmlspecialchars(self::toRawString($value), ENT_QUOTES,
            sfConfig::get('sf_charset'), false);
  }

  /**
   * Converts a value to a raw, unescaped string
   *
   * @param mixed $value
   * @return string
   */
  public static function toRawString($value)
  {
    if(is_bool($value))
    {
      return $value ? 'true' : 'false';
    }
    else
    {
      return (string) $value;
    }
  }

  /**
   * Converts a value to a boolean
   *
   * Values like 'on', 'true', '1' or 1 will be converted to TRUE, while '',
   * 'false', '0', 0 or NULL will be converted to false.
   *
   * @param mixed $value
   * @return boolean
   */
  public static function toBool($value)
  {
    if(is_string($value))
    {
      $value = strtolower($value);
    }
    if($value === 0 || $value === '0' || value === 'false' || $value === '' || $value === null)
    {
      return false;
    }
    else
    {
      return true;
    }
  }

  /**
   * Converts a value to an integer.
   *
   * @param mixed $value
   * @return integer
   */
  public static function toInt($value)
  {
    return (int) $value;
  }

  /**
   * Converts a value to a float
   *
   * @param mixed $value
   * @return float
   */
  public static function toFloat($value)
  {
    return (float) $value;
  }

  /**
   * Converts a value to an array
   *
   * If the value is an array, it is returned normally. If the value is a
   * string of format 'xxxxx[key]=value', array(key => value) is returned.
   * Else this method returns array(value).
   *
   * @param mixed $value
   * @return array
   */
  public static function toArray($value)
  {
    if(is_array($value))
    {
      return $value;
    }
    elseif(is_string($value) && preg_match('/\w+\[(\w+)\]=(.*)/', $value, $match))
    {
      return array($match[1] => $match[2]);
    }
    return array($value);
  }

  /**
   * Converts a value to an array with elements of one type
   *
   * The type can be given by mentioning the method in sfConvert used to
   * convert the elements.
   *
   * @param mixed $value
   * @param string $method Method in sfConvert to convert the value
   * @return array
   * @see sfConvert::toArray
   */
  private static function toTypeArray($value, $method)
  {
    $match = array();

    if(is_array($value))
    {
      for($i = 0, $c = count($value); $i < $c; $i++)
      {
        $value[$i] = call_user_func(array('sfConvert', $method), $value[$i]);
      }
      return $value;
    }
    elseif(is_string($value) && preg_match('/^\w+\[(\w+)\]=(.*)$/', $value, $match))
    {
      return array($match[1] => call_user_func(array('sfConvert', $method), $match[2]));
    }
    else
    {
      return array(call_user_func(array('sfConvert', $method), $value));
    }
  }

  /**
   * Converts a value to an array of strings
   *
   * @param mixed $value
   * @param string $method Method in sfConvert to convert the value
   * @return array
   * @see sfConvert::toArray
   */
  public static function toStringArray($value)
  {
    return self::toTypeArray($value, 'toString');
  }

  /**
   * Converts a value to an array of raw strings
   *
   * @param mixed $value
   * @return array
   */
  public static function toRawStringArray($value)
  {
    return self::toTypeArray($value, 'toRawString');
  }

  /**
   * Converts a value to an array of integers
   *
   * @param mixed $value
   * @return array
   */
  public static function toIntArray($value)
  {
    return self::toTypeArray($value, 'toInt');
  }

  /**
   * Converts a value to an array of floats
   *
   * @param mixed $value
   * @return array
   */
  public static function toFloatArray($value)
  {
    return self::toTypeArray($value, 'toFloat');
  }

  /**
   * Converts a value to an array of bools
   *
   * @param mixed $value
   * @return array
   */
  public static function toBoolArray($value)
  {
    return self::toTypeArray($value, 'toBool');
  }

}
