<?php
/*
 * This file is part of the Sift PHP framework.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * sfJson class for encoding to and decoding from JSON.
 *
 * @package    Sift
 * @subpackage json
 */
class sfJson
{
  /**
   * Returns the JSON representation of a value
   *
   * This function only works with UTF-8 encoded data.
   *
   * @param mixed|sfIJsonSerializable $valueToEncode
   * @param boolean $fixExpression Fix javascript expressions?
   * @param int $bitmask Bitmask consisting of JSON_HEX_QUOT, JSON_HEX_TAG,
   *                     JSON_HEX_AMP, JSON_HEX_APOS, JSON_NUMERIC_CHECK,
   *                     JSON_PRETTY_PRINT, JSON_UNESCAPED_SLASHES,
   *                     JSON_FORCE_OBJECT, JSON_UNESCAPED_UNICODE.
   * @return string
   */
  public static function encode($valueToEncode, $fixExpressions = true,
          $bitmask = 0)
  {
    $valueToEncode = self::_recursiveJsonSerializableFinder($valueToEncode);

    // Pre-encoding look for function calls and replacing by tmp ids
    $javascriptExpressions = array();

    if ($fixExpressions) {
      $valueToEncode = self::_recursiveJsonExprFinder($valueToEncode, $javascriptExpressions);
    }

    if (version_compare(PHP_VERSION, '5.3', '>')) {
      $encodedResult = json_encode($valueToEncode, $bitmask);
    } else {
      $encodedResult = json_encode($valueToEncode);
    }

    $error = self::getLastError();
    if ($error) {
      throw new sfException(sprintf('JSON error occured: %s', $error));
    }

    if ($fixExpressions && count($javascriptExpressions) > 0) {
      $count = count($javascriptExpressions);
      for ($i = 0; $i < $count; $i++) {
        $magicKey = $javascriptExpressions[$i]['magicKey'];
        $value    = $javascriptExpressions[$i]['value'];
        $encodedResult = str_replace(
        // instead of replacing "key:magicKey", we replace directly magicKey by value because "key" never changes.
        '"' . $magicKey . '"',
        $value,
        $encodedResult);
      }
    }

    return $encodedResult;
  }

  /**
   * Find all occurences of objects which implements sfIJsonSerializable interface and
   * call their jsonSerialize method
   *
   * @param mixed $value
   */
  protected static function _recursiveJsonSerializableFinder(&$value)
  {
    if ($value instanceof sfIJsonSerializable) {
      $value = $value->jsonSerialize();
    } elseif (is_array($value) || $value instanceof Traversable) {
      // fixes issues with ArrayAccess, ArrayObject under php < 5.3.4
      if ($value instanceof sfIArrayAccessByReference) {
        foreach ($value as $k => $v) {
          $_v = self::_recursiveJsonSerializableFinder($value->offsetGetByReference($k));
          $value->offsetSetByReference($k, $_v);
        }
      } else {
        foreach ($value as $k => $v) {
          $value[$k] = self::_recursiveJsonSerializableFinder($v);
        }
      }
    }

    return $value;
  }

  /**
   * Check & Replace function calls for tmp ids in the valueToEncode
   *
   * Check if the value is a function call, and if replace its value
   * with a magic key and save the javascript expression in an array.
   *
   * NOTE this method is recursive.
   *
   * NOTE: This method is used internally by the encode method.
   *
   * @see encode
   * @param mixed $valueToCheck a string - object property to be encoded
   * @return void
   */
  protected static function _recursiveJsonExprFinder(
      &$value, array &$javascriptExpressions, $currentKey = null)
  {
    if((is_string($value) && preg_match('/^\s?function\s?\(/', $value))
       || $value instanceof sfJsonExpression)
    {
      $magicKey = '____' . $currentKey . '_' . (count($javascriptExpressions));
      $javascriptExpressions[] = array(
          'magicKey' => $magicKey,
          'value'    => is_object($value) ? $value->__toString() : $value
      );
      $value = $magicKey;
    } elseif (is_array($value) || $value instanceof Traversable) {
      // fixes issues with ArrayAccess, ArrayObject under php < 5.3.4
      if ($value instanceof sfIArrayAccessByReference) {
        foreach ($value as $k => $v) {
          $_v = self::_recursiveJsonExprFinder($value->offsetGetByReference($k), $javascriptExpressions, $k);
          $value->offsetSetByReference($k, $_v);
        }
      } else {
        foreach ($value as $k => $v) {
          $value[$k] = self::_recursiveJsonExprFinder($value[$k], $javascriptExpressions, $k);
        }
      }
    } elseif (is_object($value)) {
      foreach ($value as $k => $v) {
        $value->$k = self::_recursiveJsonExprFinder($value->$k, $javascriptExpressions, $k);
      }
    }

    return $value;
  }

  /**
   * Decodes given JSON input. This function only works with UTF-8 encoded data.
   *
   * @param string $json The json string being decoded.
   * @param boolean $toAssoc  When true, returned objects will be converted into associative arrays.
   * @return mixed
   */
  public static function decode($json, $toAssoc = false)
  {
    $result = json_decode($json, $toAssoc);
    $error  = self::getLastError();
    if ($error) {
      throw new sfException(sprintf('JSON error occured: %s', $error));
    }

    return $result;
  }

  /**
   * Returns the last error (if any) occurred during the last JSON encoding/decoding.
   *
   * @return mixed Returns false if no result occured, string with message otherwise
   */
  public static function getLastError()
  {
    if (!function_exists('json_last_error')) {
      return false;
    }

    switch (json_last_error()) {
      case JSON_ERROR_NONE:
        $error = false;
      break;

      case JSON_ERROR_DEPTH:
        $error = 'Maximum stack depth exceeded';
      break;

      case JSON_ERROR_STATE_MISMATCH:
        $error = 'Underflow or the modes mismatch';
      break;

      case JSON_ERROR_CTRL_CHAR:
        $error = 'Unexpected control character found';
      break;

      case JSON_ERROR_SYNTAX:
        $error  = 'Syntax error, malformed JSON';
      break;

      case JSON_ERROR_UTF8:
        $error = 'Malformed UTF-8 characters, possibly incorrectly encoded';
      break;

      default:
        $error = 'Unknown error';
      break;
    }

    return $error;
  }

}
