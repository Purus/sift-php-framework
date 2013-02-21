<?php
/*
 * This file is part of the Sift PHP framework.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * sfToolkit provides basic utility methods.
 *
 * @package    Sift
 * @subpackage util
 * @author     Fabien Potencier <fabien.potencier@symfony-project.com>
 * @author     Sean Kerr <sean@code-box.org>
 */
class sfToolkit {

  /**
   * Extract the class or interface name from filename.
   *
   * @param string A filename.
   *
   * @return string A class or interface name, if one can be extracted, otherwise null.
   */
  public static function extractClassName($filename)
  {
    $retval = null;

    if(self::isPathAbsolute($filename))
    {
      $filename = basename($filename);
    }

    $pattern = '/(.*?)\.(class|interface)\.php/i';

    if(preg_match($pattern, $filename, $match))
    {
      $retval = $match[1];
    }

    return $retval;
  }

  /**
   * Clear all files in a given directory.
   *
   * @param  string An absolute filesystem path to a directory.
   *
   * @return void
   */
  public static function clearDirectory($directory)
  {
    if(!is_dir($directory))
    {
      return;
    }

    // open a file point to the cache dir
    $fp = opendir($directory);

    // ignore names
    $ignore = array('.', '..', 'CVS', '.svn', '.git');

    while(($file = readdir($fp)) !== false)
    {
      if(!in_array($file, $ignore))
      {
        if(is_link($directory . '/' . $file))
        {
          // delete symlink
          @unlink($directory . '/' . $file);
        }
        else if(is_dir($directory . '/' . $file))
        {
          // recurse through directory
          self::clearDirectory($directory . '/' . $file);

          // delete the directory
          @rmdir($directory . '/' . $file);
        }
        else
        {
          // delete the file
          @unlink($directory . '/' . $file);
        }
      }
    }

    // close file pointer
    closedir($fp);
  }

  /**
   * Clear all files and directories corresponding to a glob pattern.
   *
   * @param  string An absolute filesystem pattern.
   *
   * @return void
   */
  public static function clearGlob($pattern)
  {
    $files = glob($pattern);

    if(!$files)
    {
      return;
    }

    // order is important when removing directories
    sort($files);

    foreach($files as $file)
    {
      if(is_dir($file))
      {
        // delete directory
        self::clearDirectory($file);
      }
      else
      {
        // delete file
        unlink($file);
      }
    }
  }

  /**
   * Determine if a filesystem path is absolute.
   *
   * @param path A filesystem path.
   *
   * @return bool true, if the path is absolute, otherwise false.
   */
  public static function isPathAbsolute($path)
  {
    if($path[0] == '/' || $path[0] == '\\' ||
            (strlen($path) > 3 && ctype_alpha($path[0]) &&
            $path[1] == ':' &&
            ($path[2] == '\\' || $path[2] == '/')
            )
    )
    {
      return true;
    }

    return false;
  }

  /**
   * Determine if a lock file is present.
   *
   * @param integer A max amount of life time for the lock file.
   *
   * @return bool true, if the lock file is present, otherwise false.
   */
  public static function hasLockFile($lockFile, $maxLockFileLifeTime = 0)
  {
    $isLocked = false;
    if(is_readable($lockFile) && ($last_access = fileatime($lockFile)))
    {
      $now = time();
      $timeDiff = $now - $last_access;

      if(!$maxLockFileLifeTime || $timeDiff < $maxLockFileLifeTime)
      {
        $isLocked = true;
      }
      else
      {
        $isLocked = @unlink($lockFile) ? false : true;
      }
    }

    return $isLocked;
  }

  public static function stripComments($source)
  {
    if(!function_exists('token_get_all'))
    {
      return $source;
    }

    $ignore = array(T_COMMENT => true, T_DOC_COMMENT => true);
    $output = '';

    foreach(token_get_all($source) as $token)
    {
      // array
      if(isset($token[1]))
      {
        // no action on comments
        if(!isset($ignore[$token[0]]))
        {
          // anything else -> output "as is"
          $output .= $token[1];
        }
      }
      else
      {
        // simple 1-character token
        $output .= $token;
      }
    }

    return $output;
  }

  public static function stripslashesDeep($value)
  {
    return is_array($value) ? array_map(array('sfToolkit', 'stripslashesDeep'), $value) : stripslashes($value);
  }

  // code from php at moechofe dot com (array_merge comment on php.net)
  /*
   * array arrayDeepMerge ( array array1 [, array array2 [, array ...]] )
   *
   * Like array_merge
   *
   *  arrayDeepMerge() merges the elements of one or more arrays together so
   * that the values of one are appended to the end of the previous one. It
   * returns the resulting array.
   *  If the input arrays have the same string keys, then the later value for
   * that key will overwrite the previous one. If, however, the arrays contain
   * numeric keys, the later value will not overwrite the original value, but
   * will be appended.
   *  If only one array is given and the array is numerically indexed, the keys
   * get reindexed in a continuous way.
   *
   * Different from array_merge
   *  If string keys have arrays for values, these arrays will merge recursively.
   */
  public static function arrayDeepMerge()
  {
    switch(func_num_args())
    {
      case 0:
        return false;
      case 1:
        return func_get_arg(0);
      case 2:
        $args = func_get_args();
        $args[2] = array();
        if(is_array($args[0]) && is_array($args[1]))
        {
          foreach(array_unique(array_merge(array_keys($args[0]), array_keys($args[1]))) as $key)
          {
            $isKey0 = array_key_exists($key, $args[0]);
            $isKey1 = array_key_exists($key, $args[1]);
            if($isKey0 && $isKey1 && is_array($args[0][$key]) && is_array($args[1][$key]))
            {
              $args[2][$key] = self::arrayDeepMerge($args[0][$key], $args[1][$key]);
            }
            else if($isKey0 && $isKey1)
            {
              $args[2][$key] = $args[1][$key];
            }
            else if(!$isKey1)
            {
              $args[2][$key] = $args[0][$key];
            }
            else if(!$isKey0)
            {
              $args[2][$key] = $args[1][$key];
            }
          }
          return $args[2];
        }
        else
        {
          return $args[1];
        }
      default :
        $args = func_get_args();
        $args[1] = sfToolkit::arrayDeepMerge($args[0], $args[1]);
        array_shift($args);
        return call_user_func_array(array('sfToolkit', 'arrayDeepMerge'), $args);
        break;
    }
  }

  public static function stringToArray($string)
  {
    preg_match_all('/
      \s*(\w+)              # key                               \\1
      \s*=\s*               # =
      (\'|")?               # values may be included in \' or " \\2
      (.*?)                 # value                             \\3
      (?(2) \\2)            # matching \' or " if needed        \\4
      \s*(?:
        (?=\w+\s*=) | \s*$  # followed by another key= or the end of the string
      )
    /x', $string, $matches, PREG_SET_ORDER);

    $attributes = array();
    foreach($matches as $val)
    {
      $attributes[$val[1]] = self::literalize($val[3]);
    }

    return $attributes;
  }

  /**
   * Finds the type of the passed value, returns the value as the new type.
   *
   * @param  string
   * @return mixed
   */
  public static function literalize($value, $quoted = false)
  {
    // lowercase our value for comparison
    $value = trim($value);
    $lvalue = strtolower($value);

    if(in_array($lvalue, array('null', '~', '')))
    {
      $value = null;
    }
    else if(in_array($lvalue, array('true', 'on', '+', 'yes')))
    {
      $value = true;
    }
    else if(in_array($lvalue, array('false', 'off', '-', 'no')))
    {
      $value = false;
    }
    else if(ctype_digit($value))
    {
      $value = (int) $value;
    }
    else if(is_numeric($value))
    {
      $value = (float) $value;
    }
    else
    {
      $value = self::replaceConstants($value);
      if($quoted)
      {
        $value = '\'' . str_replace('\'', '\\\'', $value) . '\'';
      }
    }

    return $value;
  }

  /**
   * Replaces constant identifiers in a scalar value.
   *
   * @param string the value to perform the replacement on
   * @return string the value with substitutions made
   */
  public static function replaceConstants($value)
  {
    return is_string($value) ? preg_replace_callback('/%(.+?)%/', create_function('$v', 'return sfConfig::has(strtolower($v[1])) ? sfConfig::get(strtolower($v[1])) : "%{$v[1]}%";'), $value) : $value;
  }

  /**
   * Returns subject replaced with regular expression matchs
   *
   * @param mixed subject to search
   * @param array array of search => replace pairs
   */
  public static function pregtr($search, $replacePairs)
  {
    return preg_replace(array_keys($replacePairs), array_values($replacePairs), $search);
  }

  public static function isArrayValuesEmpty($array)
  {
    static $isEmpty = true;
    foreach($array as $value)
    {
      $isEmpty = (is_array($value)) ? self::isArrayValuesEmpty($value) : (strlen($value) == 0);
      if(!$isEmpty)
      {
        break;
      }
    }

    return $isEmpty;
  }

  /**
   * Checks if a string is an utf8.
   *
   * @param string
   * @return bool true if $string is valid UTF-8 and false otherwise.
   */
  public static function isUTF8($string)
  {
    return sfUtf8::isUtf8($string);
  }

  public static function &getArrayValueForPathByRef(&$values, $name, $default = null)
  {
    if(false !== ($offset = strpos($name, '[')))
    {
      if(isset($values[substr($name, 0, $offset)]))
      {
        $array = &$values[substr($name, 0, $offset)];

        while($pos = strpos($name, '[', $offset))
        {
          $end = strpos($name, ']', $pos);
          if($end == $pos + 1)
          {
            // reached a []
            break;
          }
          else if(!isset($array[substr($name, $pos + 1, $end - $pos - 1)]))
          {
            return $default;
          }
          else if(is_array($array))
          {
            $array = &$array[substr($name, $pos + 1, $end - $pos - 1)];
            $offset = $end;
          }
          else
          {
            return $default;
          }
        }

        return $array;
      }
    }

    return $default;
  }

  public static function getArrayValueForPath($values, $name, $default = null)
  {
    if(false !== ($offset = strpos($name, '[')))
    {
      if(isset($values[substr($name, 0, $offset)]))
      {
        $array = $values[substr($name, 0, $offset)];

        while($pos = strpos($name, '[', $offset))
        {
          $end = strpos($name, ']', $pos);
          if($end == $pos + 1)
          {
            // reached a []
            break;
          }
          else if(!isset($array[substr($name, $pos + 1, $end - $pos - 1)]))
          {
            return $default;
          }
          else if(is_array($array))
          {
            $array = $array[substr($name, $pos + 1, $end - $pos - 1)];
            $offset = $end;
          }
          else
          {
            return $default;
          }
        }

        return $array;
      }
    }

    return $default;
  }

  public static function getPhpCli()
  {
    $path = getenv('PATH') ? getenv('PATH') : getenv('Path');
    $suffixes = DIRECTORY_SEPARATOR == '\\' ? (getenv('PATHEXT') ? explode(PATH_SEPARATOR, getenv('PATHEXT')) : array('.exe', '.bat', '.cmd', '.com')) : array('');
    foreach(array('php5', 'php') as $phpCli)
    {
      foreach($suffixes as $suffix)
      {
        foreach(explode(PATH_SEPARATOR, $path) as $dir)
        {
          $file = $dir . DIRECTORY_SEPARATOR . $phpCli . $suffix;
          if(is_executable($file))
          {
            return $file;
          }
        }
      }
    }

    throw new sfException('Unable to find PHP executable');
  }

  /**
   *  Returns directory path used for temporary files
   *
   * @return string Path of the temporary directory.
   */
  public static function getTmpDir()
  {
    return sys_get_temp_dir();
  }

  /**
   * Converts strings to UTF-8 via iconv. NB, the result may not by UTF-8 if the conversion failed.
   *
   * This file comes from Prado (BSD License)
   *
   * @param  string $string string to convert to UTF-8
   * @param  string $from   current encoding
   *
   * @return string UTF-8 encoded string, original string if iconv failed.
   */
  static public function i18NtoUTF8($string, $from)
  {
    $from = strtoupper($from);
    if($from != 'UTF-8')
    {
      $s = iconv($from, 'UTF-8', $string);  // to UTF-8

      return $s !== false ? $s : $string; // it could return false
    }

    return $string;
  }

  /**
   * Converts UTF-8 strings to a different encoding. NB.
   * The result may not have been encoded if iconv fails.
   *
   * This file comes from Prado (BSD License)
   *
   * @param  string $string  the UTF-8 string for conversion
   * @param  string $to      new encoding
   *
   * @return string encoded string.
   */
  static public function i18NtoEncoding($string, $to)
  {
    $to = strtoupper($to);
    if($to != 'UTF-8')
    {
      $s = iconv('UTF-8', $to, $string);
      return $s !== false ? $s : $string;
    }

    return $string;
  }

  /**
   * Adds a path to the PHP include_path setting.
   *
   * @param   mixed  $path     Single string path or an array of paths
   * @param   string $position Either 'front' or 'back'
   *
   * @return  string The old include path
   */
  static public function addIncludePath($path, $position = 'front')
  {
    if(is_array($path))
    {
      foreach('front' == $position ? array_reverse($path) : $path as $p)
      {
        self::addIncludePath($p, $position);
      }

      return;
    }

    $paths = explode(PATH_SEPARATOR, get_include_path());

    // remove what's already in the include_path
    if(false !== $key = array_search(realpath($path), array_map('realpath', $paths)))
    {
      unset($paths[$key]);
    }

    switch($position)
    {
      case 'front':
        array_unshift($paths, $path);
        break;
      case 'back':
        $paths[] = $path;
        break;
      default:
        throw new InvalidArgumentException(sprintf('Unrecognized position: "%s"', $position));
    }

    return set_include_path(join(PATH_SEPARATOR, $paths));
  }

  /**
   * Recursive version of array_map function.
   * Customized array_map function which preserves keys/associate array indexes.
   * Note that this costs a descent amount more memory (eg. 1.5k per call)
   *
   * @param string or array $callback
   * @param array $arr1
   * @return array
   * @see http://cz.php.net/manual/en/function.array-map.php#94053
   */
  public static function arrayMap($callback, $arr1)
  {
    $results = array();
    $args = array();
    if(func_num_args() > 2)
    {
      $args = array_slice(func_get_args(), 2);
    }
    foreach($arr1 as $key => $value)
    {
      $temp = $args;
      array_unshift($temp, $value);
      if(is_array($value))
      {
        array_unshift($temp, $callback);
        $results[$key] = call_user_func_array(array('sfToolkit', 'arrayMap'), $temp);
      }
      else
      {
        $results[$key] = call_user_func_array($callback, $temp);
      }
    }
    return $results;
  }

  /**
   * Generic tlds (source: http://en.wikipedia.org/wiki/Generic_top-level_domain)
   *
   * @access protected
   */
  protected static $G_TLD = array(
      'biz', 'com', 'edu', 'gov', 'info', 'int', 'mil', 'name', 'net', 'org',
      'aero', 'asia', 'cat', 'coop', 'jobs', 'mobi', 'museum', 'pro', 'tel', 'travel',
      'arpa', 'root',
      'berlin', 'bzh', 'cym', 'gal', 'geo', 'kid', 'kids', 'lat', 'mail', 'nyc', 'post', 'sco', 'web', 'xxx',
      'nato',
      'example', 'invalid', 'localhost', 'test',
      'bitnet', 'csnet', 'ip', 'local', 'onion', 'uucp',
      'co' // note: not technically, but used in things like co.uk
  );

  /**
   * Country tlds (source: http://en.wikipedia.org/wiki/Country_code_top-level_domain)
   *
   * @var array
   */
  public static $C_TLD = array(
      // active
      'ac', 'ad', 'ae', 'af', 'ag', 'ai', 'al', 'am', 'an', 'ao', 'aq', 'ar', 'as', 'at', 'au', 'aw', 'ax', 'az',
      'ba', 'bb', 'bd', 'be', 'bf', 'bg', 'bh', 'bi', 'bj', 'bm', 'bn', 'bo', 'br', 'bs', 'bt', 'bw', 'by', 'bz',
      'ca', 'cc', 'cd', 'cf', 'cg', 'ch', 'ci', 'ck', 'cl', 'cm', 'cn', 'co', 'cr', 'cu', 'cv', 'cx', 'cy', 'cz',
      'de', 'dj', 'dk', 'dm', 'do', 'dz', 'ec', 'ee', 'eg', 'er', 'es', 'et', 'eu', 'fi', 'fj', 'fk', 'fm', 'fo',
      'fr', 'ga', 'gd', 'ge', 'gf', 'gg', 'gh', 'gi', 'gl', 'gm', 'gn', 'gp', 'gq', 'gr', 'gs', 'gt', 'gu', 'gw',
      'gy', 'hk', 'hm', 'hn', 'hr', 'ht', 'hu', 'id', 'ie', 'il', 'im', 'in', 'io', 'iq', 'ir', 'is', 'it', 'je',
      'jm', 'jo', 'jp', 'ke', 'kg', 'kh', 'ki', 'km', 'kn', 'kr', 'kw', 'ky', 'kz', 'la', 'lb', 'lc', 'li', 'lk',
      'lr', 'ls', 'lt', 'lu', 'lv', 'ly', 'ma', 'mc', 'md', 'mg', 'mh', 'mk', 'ml', 'mm', 'mn', 'mo', 'mp', 'mq',
      'mr', 'ms', 'mt', 'mu', 'mv', 'mw', 'mx', 'my', 'mz', 'na', 'nc', 'ne', 'nf', 'ng', 'ni', 'nl', 'no', 'np',
      'nr', 'nu', 'nz', 'om', 'pa', 'pe', 'pf', 'pg', 'ph', 'pk', 'pl', 'pn', 'pr', 'ps', 'pt', 'pw', 'py', 'qa',
      're', 'ro', 'ru', 'rw', 'sa', 'sb', 'sc', 'sd', 'se', 'sg', 'sh', 'si', 'sk', 'sl', 'sm', 'sn', 'sr', 'st',
      'sv', 'sy', 'sz', 'tc', 'td', 'tf', 'tg', 'th', 'tj', 'tk', 'tl', 'tm', 'tn', 'to', 'tr', 'tt', 'tv', 'tw',
      'tz', 'ua', 'ug', 'uk', 'us', 'uy', 'uz', 'va', 'vc', 've', 'vg', 'vi', 'vn', 'vu', 'wf', 'ws', 'ye', 'yu',
      'za', 'zm', 'zw',
      // inactive
      'eh', 'kp', 'me', 'rs', 'um', 'bv', 'gb', 'pm', 'sj', 'so', 'yt', 'su', 'tp', 'bu', 'cs', 'dd', 'zr'
  );

  /**
   * Returns base domain
   *
   * @see http://phosphorusandlime.blogspot.com/2007/08/php-get-base-domain.html
   *
   */
  public static function getBaseDomain($domain)
  {
    // break up domain, reverse
    $_domain = explode('.', $domain);
    $_domain = array_reverse($_domain);

    // first check for ip address
    if(count($_domain) == 4 && is_numeric($_domain[0]) && is_numeric($_domain[3]))
    {
      return $domain;
    }

    // if only 2 domain parts, that must be our domain
    if(count($_domain) <= 2)
    {
      return $domain;
    }

    /* finally, with 3+ domain parts: obviously D0 is tld now,
     * if D0 = ctld and D1 = gtld, we might have something like com.uk
     * so, if D0 = ctld && D1 = gtld && D2 != 'www', domain = D2.D1.D0
     * else if D0 = ctld && D1 = gtld && D2 == 'www', domain = D1.D0
     * else domain = D1.D0
     * these rules are simplified below
     */
    if(in_array($_domain[0], self::$C_TLD) && in_array($_domain[1], self::$G_TLD) && $_domain[2] != 'www')
    {
      return $_domain[2] . '.' . $_domain[1] . '.' . $_domain[0];
    }
    else
    {
      return $_domain[1] . '.' . $_domain[0];
    }
    return $domain;
  }

  public static function collect($collection, $property)
  {
    $values = array();
    foreach($collection as $item)
    {
      $values[] = $item[$property];
    }
    return $values;
  }

  /**
   * This function will return max upload size in bytes
   *
   * @param void
   * @return integer
   */
  public static function getMaxUploadSize()
  {
    return min(
      self::convertPhpConfigValueToBytes(ini_get('upload_max_filesize')),
      self::convertPhpConfigValueToBytes(ini_get('post_max_size'))
    );
  }

  /**
   * Convert PHP config value (2M, 8M, 200K...) to bytes
   *
   * This function was taken from PHP documentation
   *
   * @param string $val
   * @return integer
   */
  public static function convertPhpConfigValueToBytes($val)
  {
    $val = trim($val);
    $last = strtolower($val{strlen($val) - 1});
    switch($last)
    {
      // The 'G' modifier is available since PHP 5.1.0
      case 'g':
        $val *= 1024;
      case 'm':
        $val *= 1024;
      case 'k':
        $val *= 1024;
    }
    return $val;
  }

  /**
   * Sets time limit for php execution
   *
   * @param integer $time Time limit
   * @return boolean
   * @author Jan Kuchař (http://mujserver.net)
   */
  public static function setTimeLimit($time = 0)
  {
    if(!function_exists('ini_get'))
    {
      return false;
    }

    if((int) @ini_get('max_execution_time') === $time)
    {
      return true;
    }

    if(function_exists('set_time_limit'))
    {
      @set_time_limit($time);
    }
    elseif(function_exists('ini_set'))
    {
      @ini_set('max_execution_time', $time);
    }

    if((int) @ini_get('max_execution_time') === $time)
    {
      return true;
    }

    return false;
  }

  /**
   * Returns available memory which can be consumed by php script
   *
   * @return null|integer
   */
  public static function getAvailableMemory()
  {
    $mem = self::convertPhpConfigValueToBytes(ini_get('memory_limit'));
    if($mem == 0)
    {
      return null;
    }
    return $mem - memory_get_usage();
  }

  /**
   * Takes a value and checks if it is a Closure or not, if it is it
   * will return the result of the closure, if not, it will simply return the
   * value.
   *
   * @param   mixed  $var  The value to get
   * @return  mixed
   * @author  Fuel Development Team
   */
  public static function getValue($var)
  {
    return ($var instanceof Closure) ? $var() : $var;
  }

}
