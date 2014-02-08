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
 * @package Sift
 * @subpackage security
 */
class sfInputFilters
{
  // The following constants allow for nice looking callbacks to static methods
  const TO_STRING             = 'sfInputFilters::toString';
  const TO_STRING_ARRAY       = 'sfInputFilters::toStringArray';
  const TO_RAW_STRING         = 'sfInputFilters::toRawString';
  const TO_RAW_STRING_ARRAY   = 'sfInputFilters::toRawStringArray';
  const TO_INTEGER            = 'sfInputFilters::toInt';
  const TO_INTEGER_ARRAY      = 'sfInputFilters::toIntArray';
  const TO_BOOLEAN            = 'sfInputFilters::toBool';
  const TO_BOOLEAN_ARRAY      = 'sfInputFilters::toBoolArray';
  const TO_FLOAT              = 'sfInputFilters::toFloat';
  const TO_FLOAT_ARRAY        = 'sfInputFilters::toFloatArray';
  const TO_ARRAY              = 'sfInputFilters::toArray';
  const STRIP_WHITESPACE      = 'sfInputFilters::stripWhitespace';
  const STRIP_IMAGES          = 'sfInputFilters::stripImages';
  const STRIP_SCRIPTS         = 'sfInputFilters::stripScripts';
  const STRIP_TAGS            = 'sfInputFilters::stripTags';

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
    if (is_bool($value)) {
      return $value ? 'true' : 'false';
    } else {
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
    if (is_string($value)) {
      $value = strtolower($value);
    }
    if ($value === 0 || $value === '0' || $value === 'false' || $value === '' || $value === null) {
      return false;
    } else {
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
    return (float) str_replace(array(','), array('.'), preg_replace('/\s?/', '', $value));
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
    if (is_array($value)) {
      return $value;
    } elseif (is_string($value) && preg_match('/\w+\[(\w+)\]=(.*)/', $value, $match)) {
      return array($match[1] => $match[2]);
    }

    return array($value);
  }

  /**
   * Converts a value to an array of strings
   *
   * @param mixed $value
   * @param string $method Method in sfInputFilters to convert the value
   * @return array
   * @see sfInputFilters::toArray
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

  /**
   * Converts a value to an array with elements of one type
   *
   * The type can be given by mentioning the method in sfInputFilters used to
   * convert the elements.
   *
   * @param mixed $value
   * @param string $method Method in sfInputFilters to convert the value
   * @return array
   * @see sfInputFilters::toArray
   */
  private static function toTypeArray($value, $method)
  {
    $match = array();

    if (is_array($value)) {
      for ($i = 0, $c = count($value); $i < $c; $i++) {
        $value[$i] = call_user_func(array('sfInputFilters', $method), $value[$i]);
      }

      return $value;
    } elseif (is_string($value) && preg_match('/^\w+\[(\w+)\]=(.*)$/', $value, $match)) {
      return array($match[1] => call_user_func(array('sfInputFilters', $method), $match[2]));
    } else {
      return array(call_user_func(array('sfInputFilters', $method), $value));
    }
  }

  /**
   * Cleans provided data using provided filters
   * In filters array should be passed valid callbacks
   *
   * - trim
   * - htmlspecialchars
   *
   * $filters = array('trim', array(sfSanitizer::sanitize, 'strict'), sfUtf8::clean);
   * $filters = array('trim', array(array('sfSanitizer', 'sanitize'), 'strict'), sfUtf8::clean);
   *
   * @param mixed $value
   * @param array $filters
   * @return mixed
   */
  public static function filterVar($value, $filters)
  {
    if (!is_array($filters)) {
      $filters = array($filters);
    }

    foreach ($filters as $filter) {
      if (is_array($filter)) {
        $arguments = array();
        if(is_array($filter[0])
                || strpos($filter[0], '::') !== false)
        {
          $callback = $filter[0];
          array_shift($filter);
        } else {
          $callback = array_shift($filter);
        }

        array_push($arguments, $callback);
        array_push($arguments, $value);

        // push filter arguments to the array
        foreach ($filter as $a => $p) {
          array_push($arguments, $p);
        }

        $value = call_user_func_array(array('sfToolkit', 'arrayMap'), $arguments);

      } else {
        $value = sfToolkit::arrayMap($filter, $value);
      }
    }

    return $value;
  }

  /**
   * Alias for filterVar()
   *
   * @param mixed $value
   * @param array $filters
   * @see   filterVar
   */
  public static function clean($value, $filters = array())
  {
    return self::filterVar($value, $filters);
  }

  /**
   * Strips extra whitespace from output
   *
   * @param string $str String to sanitize
   * @return string whitespace sanitized string
   * @access public
   * @static
   */
  public static function stripWhitespace($str)
  {
    $r = preg_replace('/[\n\r\t]+/', '', $str);

    return preg_replace('/\s{2,}/', ' ', $r);
  }

  /**
   * Strips image tags from output
   *
   * @param string $str String to sanitize
   * @return string Sting with images stripped.
   * @access public
   * @static
   */
  public static function stripImages($str)
  {
    $str = preg_replace('/(<a[^>]*>)(<img[^>]+alt=")([^"]*)("[^>]*>)(<\/a>)/i', '$1$3$5<br />', $str);
    $str = preg_replace('/(<img[^>]+alt=")([^"]*)("[^>]*>)/i', '$2<br />', $str);
    $str = preg_replace('/<img[^>]*>/i', '', $str);

    return $str;
  }

  /**
   * Strips scripts and stylesheets from output
   *
   * @param string $str String to sanitize
   * @return string String with <script>, <style>, <link> elements removed.
   * @access public
   * @static
   */
  public static function stripScripts($str)
  {
    // $str = preg_replace('/(<link[^>]+rel="[^"]*stylesheet"[^>]*>|<img[^>]*>|style="[^"]*")|<script[^>]*>.*?<\/script>|<style[^>]*>.*?<\/style>|<!--.*?-->/i', '', $str);
    $search = array(
               '/(<link[^>]+rel="[^"]*stylesheet"[^>]*>|<img[^>]*>|style="[^"]*")/i',
               '@<script[^>]*?>.*?</script>@si',   // Strip out javascript
               '@<style[^>]*?>.*?</style>@siU',    // Strip style tags properly
               // '@<![\s\S]*?--[ \t\n\r]*>@',     // Strip multi-line comments including CDATA
               // '/<!--.*?-->/i',
    );

    return preg_replace($search, '', $str);
  }

  /**
   * Strips the specified tags from output.
   *
   * @param string $str String to sanitize
   * @param string $tag Tag to remove (add more parameters as needed)
   * @return string sanitized String
   * @access public
   * @static
   * @see http://cz.php.net/manual/en/function.strip-tags.php#102221
   */
  public static function stripTags($text, $tags = '')
  {
    // replace php and comments tags so they do not get stripped
    $text = preg_replace("@<\?@", "#?#", $text);
    $text = preg_replace("@<!--@", "#!--#", $text);

    // strip tags normally
    $text = strip_tags($text, $tags);

    // return php and comments tags to their origial form
    $text = preg_replace("@#\?#@", "<?", $text);
    $text = preg_replace("@#!--#@", "<!--", $text);

    return $text;
  }

}
