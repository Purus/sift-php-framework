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

    $pattern = '/(.*?)(\.(class|interface))?\.php/i';

    if(preg_match($pattern, $filename, $match))
    {
      $retval = $match[1];
    }

    return $retval;
  }

  /**
   * Extracts class names, interface names from the file
   *
   * @param string $file Absolute path to the file
   * @throws sfFileException If the file does not exist
   */
  public static function extractClasses($file)
  {
    if(!is_readable($file))
    {
      throw new sfFileException(sprintf('File "%s" does not exist or is not readable.', $file));
    }

    preg_match_all('~^\s*(?:abstract\s+|final\s+)?(?:class|interface)\s+(\w+)~mi',
                  file_get_contents($file), $matches);

    if(isset($matches[1]))
    {
      return $matches[1];
    }

    return array();
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
   * @param string $path A filesystem path.
   * @return bool true, if the path is absolute, otherwise false.
   */
  public static function isPathAbsolute($path)
  {
    if(empty($path))
    {
      return false;
    }

    if($path[0] == '/' || $path[0] == '\\' ||
      (strlen($path) > 3 && ctype_alpha($path[0]) &&
      $path[1] == ':' && ($path[2] == '\\' || $path[2] == '/')))
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

    $space = $output = '';

    $set = '!"#$&\'()*+,-./:;<=>?@[\]^`{|}';
    $set = array_flip(preg_split('//',$set));

    foreach(token_get_all($source) as $token)
    {
      if(!is_array($token))
      {
        $token = array(0, $token);
      }
      switch($token[0])
      {
        case T_DOC_COMMENT:
          // leave annotations and inject statements in place
          if(preg_match('# (@inject|@annotation)+#i', $token[1]))
          {
            $output .= $token[1];
          }
        break;

        case T_COMMENT:
        case T_WHITESPACE:
          $space = ' ';
        break;

        default:
          if(isset($set[substr($output, -1)]) || isset($set[$token[1]{0}])) $space = '';
          $output .= $space . $token[1];
          $space = '';
      }
    }

    return $output;
  }

  /**
   * Strips slashes
   *
   * @param array|string $value
   * @return array
   */
  public static function stripslashesDeep($value)
  {
    return is_array($value) ? array_map(array('sfToolkit', 'stripslashesDeep'), $value) : stripslashes($value);
  }

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

  /**
   * "Extend" recursively array $a with array $b values (no deletion in $a, just added and updated values)
   *
   * @param array $a
   * @param array $b
   * @return array
   * @see http://stackoverflow.com/questions/6813884/array-merge-on-an-associative-array-in-php
   * @see http://php.net/manual/en/function.array-merge.php#95294
   */
  public static function arrayExtend($a, $b)
  {
    foreach($b as $k => $v)
    {
      if(is_array($v))
      {
        if(!isset($a[$k]))
        {
          $a[$k] = $v;
        }
        else
        {
          $a[$k] = self::arrayExtend($a[$k], $v);
        }
      }
      else
      {
        $a[$k] = $v;
      }
    }
    return $a;
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
   * Replaces constant identifiers in a scalar value with value modifiers.
   * Value with string %SF_CULTURE{0,2}% will be replaced with the configuration
   * value using the substring modifier like:
   *
   * <code>
   * substring(sfConfig::get('sf_culture'), 0, 2).
   * </code>
   *
   * All possible modifiers are:
   *
   *  * %CONFIG_VALUE{x,y}% -> substring(CONFIG_VALUE, x, y)
   *  * %CONFIG_VALUE{slugify}% -> will convert underscores, spaces to dashes
   *
   * Modifiers can be nested and are applied in left to right order:
   *
   * <code>
   * %SF_CULTURE{slugify|0,2}% will apply "slugify" modifier and than "substring"
   * </code>
   *
   * This method calls also replaceConstants().
   *
   * @param string $value the value to perform the replacement on
   * @return string the value with substitutions made
   * @see replaceConstants
   */
  public static function replaceConstantsWithModifiers($value)
  {
    return is_string($value) ? preg_replace_callback('/%(.+?)\{(.+?)\}+%/',
      array('self', 'replaceConstantsWithModifiersCallback'),
      // call replace constants too
      self::replaceConstants($value)) : $value;
  }

  /**
   * Callback for replaceConstantsWithModifiers method.
   *
   * @param array $v Match returned from preg_replace_callback
   * @see replaceConstantsWithModifiers
   */
  protected static function replaceConstantsWithModifiersCallback($v)
  {
    // nothing found
    if(!sfConfig::has(strtolower($v[1])))
    {
      return $v[0];
    }

    $value = sfConfig::get(strtolower($v[1]));
    $expressions = explode('|', $v[2]);

    // we have expressions
    foreach($expressions as $s)
    {
      switch($s)
      {
        case 'slugify':
          $value = str_replace(array(' ', '_'), '-', $value);
        break;

        default:

          if(strpos($s, ',') !== false)
          {
            $parts = explode(',', $s);
            switch(count($parts))
            {
              case 1:
                $value = sfUtf8::sub($value, $parts[0]);
              break;
              case 2:
                $value = sfUtf8::sub($value, $parts[0], $parts[1]);
              break;
            }
          }
          else
          {
            throw new LogicException(sprintf('Modifier exporession "%s" in "%s" not understood.', $s, $v[0]));
          }
        break;
      }
    }
    return $value;
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

  /**
   * Get path to php cli.
   *
   * @throws sfException If no php cli found
   * @return string
   */
  public static function getPhpCli()
  {
    if(getenv('PHP_PATH'))
    {
      $php_cli = getenv('PHP_PATH');
      if(!is_executable($php_cli))
      {
        throw new sfException('The defined PHP_PATH environment variable is not a valid PHP executable.');
      }
    }
    else
    {
      $php_cli = PHP_BINDIR . DIRECTORY_SEPARATOR . 'php';
    }

    if(is_executable($php_cli))
    {
      return $php_cli;
    }

    $path = getenv('PATH') ? getenv('PATH') : getenv('Path');
    $exe_suffixes = DIRECTORY_SEPARATOR == '\\' ? (getenv('PATHEXT') ? explode(PATH_SEPARATOR, getenv('PATHEXT')) : array('.exe', '.bat', '.cmd', '.com')) : array('');
    foreach(array('php5', 'php') as $php_cli)
    {
      foreach($exe_suffixes as $suffix)
      {
        foreach(explode(PATH_SEPARATOR, $path) as $dir)
        {
          $file = $dir . DIRECTORY_SEPARATOR . $php_cli . $suffix;
          if(is_executable($file))
          {
            return $file;
          }
        }
      }
    }

    throw new sfException('Unable to find PHP executable.');
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
   */
  public static function getValue($var)
  {
    return ($var instanceof Closure) ? $var() : $var;
  }

  /**
   * Can call system "exec" method?
   *
   * @return boolean True if yes, false otherwise
   */
  public static function canSystemCall()
  {
    if(function_exists('exec'))
    {
      try
      {
        $canSystemCall = (boolean)sfToolkit::getPhpCli();
      }
      catch(sfException $e)
      {
        $canSystemCall = false;
      }
    }
    else
    {
      $canSystemCall = false;
    }
    return false;
  }

  /**
   * Exports PHP variable
   *
   * @param mixed $var
   * @param boolean $expression Find and replace php expressions?
   * @return stirng
   */
  public static function varExport($var, $expressions = true)
  {
    // Pre-encoding look for function calls and replacing by tmp ids
    $phpExpressions = array();

    if($expressions)
    {
      // strip expressions from the variable
      $var = self::_recursivePhpExprFinder($var, $phpExpressions);
    }

    // export variable
    $var = var_export($var, true);

    if($expressions)
    {
      $count = count($phpExpressions);
      // put expressions back
      if(count($phpExpressions) > 0)
      {
        for($i = 0; $i < $count; $i++)
        {
          $magicKey = $phpExpressions[$i]['magicKey'];
          $value    = $phpExpressions[$i]['value'];
          $var = str_replace(
            // instead of replacing "key:magicKey", we replace directly magicKey by value because "key" never changes.
            "'" . $magicKey . "'",
            $value,
            $var);
        }
      }
    }

    // do some cleanup
    $var = str_replace("\n", '', $var);
    $var = str_replace('array (  ', 'array(', $var);
    $var = str_replace(',)', ')', $var);
    $var = str_replace(', )', ')', $var);
    $var = str_replace('  ', ' ', $var);
    $var = str_replace('=>  ', '=> ', $var);

    return $var;
  }

  /**
   * Check & Replace function calls for tmp ids in the $value
   *
   * Check if the value is a function call, and if replace its value
   * with a magic key and save the php expression in an array.
   *
   * NOTE this method is recursive.
   *
   * NOTE: This method is used internally by the encode method.
   *
   * @param mixed $value a string - object property to be exported
   * @return void
   */
  protected static function _recursivePhpExprFinder(
      &$value, array &$phpExpressions, $currentKey = null)
  {
    if($value instanceof sfPhpExpression)
    {
      $magicKey = '____php_expr_' . $currentKey . '_' . (count($phpExpressions));
      $phpExpressions[] = array(
          'magicKey' => $magicKey,
          'value'    => is_object($value) ? $value->__toString() : $value
      );
      $value = $magicKey;
    }
    elseif(is_array($value))
    {
      foreach ($value as $k => $v)
      {
        $value[$k] = self::_recursivePhpExprFinder($value[$k], $phpExpressions, $k);
      }
    }
    elseif(is_object($value))
    {
      foreach ($value as $k => $v)
      {
        $value->$k = self::_recursivePhpExprFinder($value->$k, $phpExpressions, $k);
      }
    }
    return $value;
  }

  /**
   * Verify that the contents of a variable can be called as a function.
   * Also checks if the callable is not disabled by configuration directive.
   *
   * @param mixed $callback The callback function to check
   * @param boolean $syntaxOnly If set to TRUE the function only verifies that name might be a function or method.
   *                            It will only reject simple variables that are not strings, or an array that does not
   *                            have a valid structure to be used as a callback. The valid ones are supposed to have
   *                            only 2 entries, the first of which is an object or a string, and the second a string.
   * @param string $callableName Receives the "callable name". In the example below it is "someClass::someMethod".
   * @return boolean Returns true if name is callable, false otherwise.
   * @see is
   */
  public static function isCallable($callback, $syntaxOnly = false, &$callableName = '')
  {
    return is_callable($callback, $syntaxOnly, $callableName)
           && !self::isFunctionDisabled($callback);
  }

  /**
   * Checks if given function is disabled by "disable_functions" directive.
   *
   * @param string $callback
   */
  public static function isFunctionDisabled($callback)
  {
    if(!is_string($callback))
    {
      return false;
    }
    return in_array($callback, explode(',', ini_get('disable_functions')));
  }

  /**
   * Check if the value is blank.
   *
   * When you need to accept these as valid, non-empty values:
   *
   *  - 0 (0 as an integer)
   *  - 0.0 (0 as a float)
   *  - "0" (0 as a string)
   *
   *
   * @param mixed $value
   * @return boolean
   */
  public static function isBlank($value)
  {
    return empty($value) && !is_numeric($value);
  }

}
