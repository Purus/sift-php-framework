<?php
/*
 * This file is part of the Sift PHP framework.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * Dumps a variable.
 *
 * @package Sift
 * @subpackage debug
 */
class sfDebugDumper {

  /**
   * End of line
   */
  const EOL = "\n";

  /**
   * Is the css and js included in the dump?
   * This prevents multiple inclusion of the assets.
   *
   * @var boolean
   */
  private static $assetsIncluded = false;

  /**
   * Array of default options
   *
   * @var array
   */
  protected static $defaultOptions = array(
    // force colors in the terminal dump?
    'terminal_force_colors' => false,
    // display the location, where was the dump called
    // or array of (file, line, code) where was the dump called
    'location' => true,
    // How many nested levels of array/object properties display (defaults to 4)
    'depth' => 4,
    // How truncate long strings? (defaults to 150)
    'truncate' => 150,
    // Link for the file editor
    'file_url_format' => 'editor://open?file=%file%&line=%line%',
    // the template for html output
    'template' => '<div class="debug-dump-info">%label% %location%</div><pre class="debug-dump" title="%title%">%dump%</pre>',
    // what css stylesheet to include? Will be searched in the template_dir
    'css_stylesheet' => 'dump.min.css',
    // what javascript to include? Will be searched in the template dir
    'javascript' => 'dump.min.js',
    // where are the templates for rendering the dump?
    // Can contain constants
    'template_dir' => '%SF_SIFT_DATA_DIR%/web_debug/dump',
    'template' => 'dump.php',
    // always collapse? (defaults to false)
    'always_collapse' => false,
    // How big array/object are collapsed?
    'collapse_count' => 7
  );

  /**
   * @var string
   */
  protected static $sapi = null;

  /**
   * Get the current value of the debug output environment.
   * This defaults to the value of PHP_SAPI.
   *
   * @return string
   */
  public static function getSapi()
  {
    if(self::$sapi === null)
    {
      self::$sapi = PHP_SAPI;
    }
    return self::$sapi;
  }

  /**
   * Set the debug ouput environment. Setting a value of null causes to use PHP_SAPI.
   *
   * @param string $sapi
   * @return void
   */
  public static function setSapi($sapi)
  {
    self::$sapi = $sapi;
  }

  /**
   * Is the debug running in the cli?
   *
   * @return boolean
   */
  public static function isInCli()
  {
    return (self::getSapi() == 'cli');
  }

  /**
   * Sets options which will be used by all dump() calls
   *
   * @param array $options
   */
  public static function setDefaultOptions(array $options)
  {
    self::$defaultOptions = array_merge(self::$defaultOptions, $options);
  }

  /**
   * Array of terminal colors map
   *
   * @var array
   */
  public static $terminalColors = array(
    'boolean' => '1;33',
    'null' => '1;33',
    'number' => '1;32',
    'string' => '1;36',
    'array' => '1;31',
    'key' => '1;37',
    'object' => '1;31',
    'visibility' => '1;30',
    'resource' => '1;37',
    'indent' => '1;30',
  );

  /**
   * Stream type info callbacks
   *
   * @var array
   */
  public static $resources = array(
    'stream' => 'stream_get_meta_data',
    'stream-context' => 'stream_context_get_options',
    'curl' => 'curl_getinfo'
  );

  /**
   * Dumps variable to the output.
   *
   * @param mixed $var The variable to dump
   * @param array $options Array of options
   * @param boolean $echo Echo the output?
   * @return string
   */
  public static function dump($var, array $options = null, $echo = true)
  {
    // only available in debug mode
    if(class_exists('sfConfig', false) && !sfConfig::get('sf_debug'))
    {
      return;
    }

    $options = array_merge(self::$defaultOptions, (array)$options);

    if(!self::isInCli())
    {
      $dump = self::toHtml($var, $options);
    }
    elseif(self::detectColors() || $options['terminal_force_colors'])
    {
      $dump = self::toTerminal($var, $options);
    }
    else
    {
      $dump = self::toText($var, $options);
    }

    if($echo)
    {
      echo $dump;
    }

    return $dump;
  }

  /**
   * Dumps variable to HTML.
   *
   * @param mixed $var The variable to dump
   * @param array $options Array of options
   * @param boolean $includeAssets Include css and javascript assets in the dump?
   * @return string
   */
  private static function toHtml($var, array $options = null, $includeAssets = true)
  {
    $options = array_merge(self::$defaultOptions, (array)$options);

    if(!is_array($options['location']) && $options['location'])
    {
      list($file, $line, $code) = self::findLocation();
    }
    else
    {
      list($file, $line, $code) = $options['location'];
    }

    $templateDir = sfToolkit::replaceConstants($options['template_dir']);

    $css = $js = '';
    if($includeAssets && !self::$assetsIncluded)
    {
      $css = file_get_contents($templateDir . '/' . $options['css_stylesheet']);
      $js = file_get_contents($templateDir . '/' . $options['javascript']);
      self::$assetsIncluded = true;
    }

    return self::render(
      $templateDir . '/' . $options['template'],
      array(
        'dump' => self::dumpVar($var, $options),
        'code' => $code,
        'css' => $css,
        'js' => $js,
        'title' => $code . ($file ? htmlspecialchars(sprintf(' in file %s on line %s', $file, $line), ENT_QUOTES) : ''),
        'file_edit_url' => self::getFileEditUrl($file, $line),
        'location' => $file ? sprintf('<a href="%s">in file %s (%s)</a>',
                              htmlspecialchars(self::getFileEditUrl($file, $line)), self::shortenFilePath($file), $line) : '',
        'label' => $code
    ));
  }

  /**
   * Returns the file editor url
   *
   * @param string $file The asbolute path to a file
   * @param string $line The line number
   * @return string
   */
  protected static function getFileEditUrl($file, $line)
  {
    return strtr(self::$defaultOptions['file_url_format'], array(
      '%file%' => $file,
      '%line%' => $line
    ));
  }

  /**
   * Shortens file path
   *
   * @param string $file
   * @return string
   */
  protected static function shortenFilePath($file)
  {
    return sfDebug::shortenFilePath($file);
  }

  /**
   * Dumps variable to plain text.
   *
   * @return string
   */
  private static function toText($var, array $options = null)
  {
    return htmlspecialchars_decode(strip_tags(self::toHtml($var, $options, false)), ENT_QUOTES);
  }

  /**
   * Dumps variable to x-terminal.
   *
   * @return string
   */
  private static function toTerminal($var, array $options = null)
  {
    return htmlspecialchars_decode(
            strip_tags(
              preg_replace_callback('#<span class="debug-dump-(\w+)">|</span>#',
                array('sfDebugDumper', 'terminalReplacementCallback'),
                self::toHtml($var, $options, false))), ENT_QUOTES);
  }

  /**
   * Replaces color for terminal usage
   *
   * @param $match
   * @return string
   */
  private static function terminalReplacementCallback($match)
  {
    return "\033[" . (isset($match[1], sfDebugDumper::$terminalColors[$match[1]]) ? sfDebugDumper::$terminalColors[$match[1]] : '0') . "m";
  }

  /**
   * Internal toHtml() dump implementation.
   *
   * @param mixed $var variable to dump
   * @param array $options Array of options
   * @param integer $level The current recursion level
   * @return string
   */
  private static function dumpVar(&$var, array $options, $level = 0)
  {
    if(method_exists(__CLASS__, $m = 'dump' . gettype($var)))
    {
      return self::$m($var, $options, $level);
    }
    else
    {
      return '<span class="debug-dump-unknown">unknown type</span>';
    }
  }

  /**
   * Dumps null
   *
   * @return string
   */
  private static function dumpNull()
  {
    return '<span class="debug-dump-null">null</span>' . self::EOL;
  }

  /**
   * Dumps boolean
   *
   * @param integer $var
   * @return string
   */
  private static function dumpBoolean(&$var)
  {
    return sprintf('<span class="debug-dump-boolean">%s</span>', ($var ? 'true' : 'false')) . self::EOL;
  }

  /**
   * Dumps integer
   *
   * @param integer $var
   * @return string
   */
  private static function dumpInteger(&$var)
  {
    return sprintf('<span class="debug-dump-number debug-dump-integer">%s</span>', $var) . self::EOL;
  }

  /**
   * Dumps double
   *
   * @param double $var
   * @return string
   */
  private static function dumpDouble(&$var)
  {
    return sprintf('<span class="number double">%s%s</span>', var_export($var, true), (strpos($var, '.') === false ? '.0' : '')) . self::EOL;
  }

  /**
   * Dumps string
   *
   * @param integer $var
   * @param array $options
   * @return string
   */
  private static function dumpString(&$var, $options)
  {
    return sprintf('<span class="debug-dump-string">%s</span>%s',
      self::encodeString($options['truncate'] && strlen($var) > $options['truncate'] ? substr($var, 0, $options['truncate']) . ' ... ' : $var),
      (strlen($var) > 1 ? ' (' . strlen($var) . ')' : '')
    ) . self::EOL;
  }

  /**
   * Dumps array
   *
   * @staticvar string $marker
   * @param array $var
   * @param array $options
   * @param integer $level
   * @return string
   */
  private static function dumpArray(&$var, $options, $level)
  {
    static $marker;
    if($marker === null)
    {
      $marker = uniqid("\x00", true);
    }

    $out = '<span class="debug-dump-array">array</span>(';
    if(empty($var))
    {
      return $out . ')' . self::EOL;
    }
    elseif(isset($var[$marker]))
    {
      return $out . (count($var) - 1) . ') [ <span class="debug-dump-recursion">-recursion-</span> ]' . self::EOL;
    }
    elseif(!$options['depth'] || $level < $options['depth'])
    {
      $collapsed = $level ? count($var) >= $options['collapse_count'] : $options['always_collapse'];
      $out = '<a href="#" class="debug-dump-toggler level-' . $level . (!$collapsed ? ' opened' : '') . '">' . $out . count($var) . ")</a>\n<div class=\"debug-dump-collapsable level-" . $level . ($collapsed ? ' collapsed' : '') . "\">";
      $var[$marker] = true;
      foreach($var as $k => & $v)
      {
        if($k !== $marker)
        {
          $out .= '<span class="debug-dump-indent">   ' . str_repeat('|  ', $level) . '</span>'
              . '<span class="debug-dump-key">' . (preg_match('#^\w+\z#', $k) ? $k : self::encodeString($k)) . '</span> => '
              . self::dumpVar($v, $options, $level + 1);
        }
      }
      unset($var[$marker]);
      return $out . '</div>';
    }
    else
    {
      return $out . count($var) . ') [ ... ]'. self::EOL;
    }
  }

  /**
   * Dumps object
   *
   * @staticvar array $list
   * @param mixed $var
   * @param array $options
   * @param integer $level
   * @return string
   */
  private static function dumpObject(&$var, $options, $level)
  {
    if($var instanceof Closure)
    {
      $rc = new ReflectionFunction($var);
      $fields = array();
      foreach($rc->getParameters() as $param)
      {
        $fields[] = '$' . $param->getName();
      }
      $fields = array(
        'file' => $rc->getFileName(), 'line' => $rc->getStartLine(),
        'variables' => $rc->getStaticVariables(), 'parameters' => implode(', ', $fields)
      );
    }
    elseif($var instanceof SplFileInfo)
    {
      $fields = array('path' => $var->getPathname());
    }
    elseif($var instanceof SplObjectStorage)
    {
      $fields = array();
      foreach(clone $var as $obj)
      {
        $fields[] = array('object' => $obj, 'data' => $var[$obj]);
      }
    }
    else
    {
      $fields = (array) $var;
    }

    static $list = array();

    $out = '<span class="debug-dump-object">' . get_class($var) . '</span> <span class="debug-dump-hash">(#' . spl_object_hash($var) . ')</span>';

    if(empty($fields))
    {
      return $out . self::EOL;
    }
    elseif(in_array($var, $list, true))
    {
      return $out . ' { <span class="debug-dump-recursion">-recursion-</span> }' . self::EOL;
    }
    elseif(!$options['depth'] || $level < $options['depth'] || $var instanceof Closure)
    {
      $collapsed = $level ? count($fields) >= $options['collapse_count'] : $options['always_collapse'];
      $out = '<a href="#" class="debug-dump-toggler level-' . $level . (!$collapsed ? ' opened' : '') . "\">". $out . "</a>\n<div class=\"debug-dump-collapsable level-" . $level . ($collapsed ? ' collapsed' : '') . "\">";
      $list[] = $var;
      foreach($fields as $k => & $v)
      {
        $vis = '';
        if($k[0] === "\x00")
        {
          $vis = ' <span class="debug-dump-access">' . ($k[1] === '*' ? 'protected' : 'private') . '</span>';
          $k = substr($k, strrpos($k, "\x00") + 1);
        }
        $out .= '<span class="debug-dump-indent">   ' . str_repeat('|  ', $level) . '</span>'
            . '<span class="debug-dump-key">' . (preg_match('#^\w+\z#', $k) ? $k : self::encodeString($k)) . "</span>$vis => "
            . self::dumpVar($v, $options, $level + 1);
      }
      array_pop($list);
      return $out . '</div>';
    }
    else
    {
      return $out . ' { ... }' . self::EOL;
    }
  }

  /**
   * Dumps resource
   *
   * @param resource $var
   * @param array $options
   * @param integer $level
   * @return string
   */
  private static function dumpResource(&$var, $options, $level)
  {
    $type = get_resource_type($var);
    $out = '<span class="debug-dump-resource">' . htmlspecialchars($type) . ' resource</span>';
    if(isset(self::$resources[$type]))
    {
      $out = sprintf('<a href="#" class="debug-dump-toggler">%s</a><div class="debug-dump-collapsable">', $out);
      foreach(call_user_func(self::$resources[$type], $var) as $k => $v)
      {
        $out .= '<span class="debug-dump-indent">   ' . str_repeat('|  ', $level) . '</span>'
            . '<span class="debug-dump-key">' . htmlspecialchars($k) . "</span> => " . self::dumpVar($v, $options, $level + 1);
      }
      return $out . '</div>';
    }
    return $out . self::EOL;
  }

  private static function encodeString($s)
  {
    static $table;
    if($table === null)
    {
      foreach(array_merge(range("\x00", "\x1F"), range("\x7F", "\xFF")) as $ch)
      {
        $table[$ch] = '\x' . str_pad(dechex(ord($ch)), 2, '0', STR_PAD_LEFT);
      }
      $table["\\"] = '\\\\';
      $table["\r"] = '\r';
      $table["\n"] = '\n';
      $table["\t"] = '\t';
    }

    if(preg_match('#[^\x09\x0A\x0D\x20-\x7E\xA0-\x{10FFFF}]#u', $s) || preg_last_error())
    {
      $s = strtr($s, $table);
    }
    return '"' . htmlSpecialChars($s, ENT_NOQUOTES) . '"';
  }

  /**
   * Finds the location where dump was called.
   *
   * @return array [file, line, code]
   */
  private static function findLocation()
  {
    $dir = dirname(__FILE__);
    foreach(debug_backtrace(PHP_VERSION_ID >= 50306 ? DEBUG_BACKTRACE_IGNORE_ARGS : false) as $item)
    {
      if(isset($item['file']) && strpos($item['file'], $dir) === 0)
      {
        continue;
      }
      elseif(!isset($item['file'], $item['line']) || !is_file($item['file']))
      {
        break;
      }
      else
      {
        $lines = file($item['file']);
        $line = $lines[$item['line'] - 1];
        return array(
          $item['file'],
          $item['line'],
          preg_match('#\w*dump(er::\w+)?\((.*)\)#i', $line, $match) ? $match[2] : $line
        );
      }
    }
  }

  /**
   *
   * @return boolean
   */
  private static function detectColors()
  {
    return self::$terminalColors &&
        (getenv('ConEmuANSI') === 'ON' || getenv('ANSICON') !== FALSE ||
        (defined('STDOUT') && function_exists('posix_isatty') && posix_isatty(STDOUT)));
  }

  /**
   * Renders the template
   *
   * @return string
   * @see sfLimitedScope::render
   */
  private static function render(/*$template, $vars = array()*/)
  {
    return call_user_func_array(array(
      'sfLimitedScope', 'render'
    ), func_get_args());
  }

}
