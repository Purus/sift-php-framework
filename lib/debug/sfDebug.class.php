<?php
/*
 * This file is part of the Sift PHP framework.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * sfDebug provides some method to help debugging a Sift application.
 *
 * @package    Sift
 * @subpackage debug
 */
class sfDebug {

  /**
   * Returns PHP information as an array.
   *
   * @return array An array of php information
   */
  public static function phpInfoAsArray()
  {
    $values = array(
      'php' => phpversion(),
      'os' => php_uname(),
      'extensions' => get_loaded_extensions(),
    );

    return $values;
  }

  /**
   * Returns Sift information as an array.
   *
   * @return array An array of Sift information (version, lib_dir, data_dir)
   */
  public static function frameworkInfoAsArray()
  {
    return array(
      'version' => sfCore::getVersion(),
      'lib_dir' => sfConfig::get('sf_sift_lib_dir'),
      'data_dir' => sfConfig::get('sf_sift_data_dir'),
    );
  }

  /**
   * Shortens a file path by replacing Sift directory constants.
   *
   * @param  string $file
   *
   * @return string
   */
  public static function shortenFilePath($file)
  {
    foreach(array('sf_root_dir', 'sf_sift_lib_dir') as $key)
    {
      if (0 === strpos($file, $value = sfConfig::get($key)))
      {
        $file = str_replace($value, strtoupper($key), $file);
        break;
      }
    }
    return $file;
  }

  /**
   * Returns PHP globals variables as a sorted array.
   *
   * @return array PHP globals
   */
  public static function globalsAsArray()
  {
    $values = array();
    foreach(array('cookie', 'server', 'get', 'post', 'files', 'env', 'session') as $name)
    {
      if(!isset($GLOBALS['_' . strtoupper($name)]))
      {
        continue;
      }

      $values[$name] = array();
      foreach($GLOBALS['_' . strtoupper($name)] as $key => $value)
      {
        $values[$name][$key] = $value;
      }
      ksort($values[$name]);
    }

    ksort($values);

    return $values;
  }

  /**
   * Returns sfConfig variables as a sorted array.
   *
   * @return array sfConfig variables
   */
  public static function settingsAsArray()
  {
    $config = sfConfig::getAll();

    ksort($config);

    return $config;
  }

  /**
   * Returns request parameter holders as an array.
   *
   * @param sfRequest A sfRequest instance
   *
   * @return array The request parameter holders
   */
  public static function requestAsArray($request)
  {
    if($request)
    {
      $values = array(
          'parameterHolder' => self::flattenParameterHolder($request->getParameterHolder()),
          'attributeHolder' => self::flattenParameterHolder($request->getAttributeHolder()),
      );
    }
    else
    {
      $values = array('parameterHolder' => array(), 'attributeHolder' => array());
    }

    return $values;
  }

  /**
   * Returns response parameters as an array.
   *
   * @param sfResponse A sfResponse instance
   *
   * @return array The response parameters
   */
  public static function responseAsArray($response)
  {
    if($response)
    {
      $values = array(
          'cookies' => array(),
          'httpHeaders' => array(),
          'parameterHolder' => self::flattenParameterHolder($response->getParameterHolder()),
      );
      if(method_exists($response, 'getHttpHeaders'))
      {
        foreach($response->getHttpHeaders() as $key => $value)
        {
          $values['httpHeaders'][$key] = $value;
        }
      }

      if(method_exists($response, 'getCookies'))
      {
        $cookies = array();
        foreach($response->getCookies() as $key => $value)
        {
          $values['cookies'][$key] = $value;
        }
      }
    }
    else
    {
      $values = array('cookies' => array(), 'httpHeaders' => array(), 'parameterHolder' => array());
    }

    return $values;
  }

 /**
   * Returns a parameter holder as an array.
   *
   * @param sfParameterHolder $parameterHolder A sfParameterHolder instance
   * @param boolean $removeObjects when set to true, objects are removed. default is false for BC.
   *
   * @return array The parameter holder as an array
   */
  public static function flattenParameterHolder($parameterHolder, $removeObjects = false)
  {
    $values = array();
    if ($parameterHolder instanceof sfFlatParameterHolder)
    {
      foreach ($parameterHolder->getAll() as $key => $value)
      {
        $values[$key] = $value;
      }
    }
    else
    {
      foreach ($parameterHolder->getNamespaces() as $ns)
      {
        $values[$ns] = array();
        foreach ($parameterHolder->getAll($ns) as $key => $value)
        {
          $values[$ns][$key] = $value;
        }
        ksort($values[$ns]);
      }
    }

    if ($removeObjects)
    {
      $values = self::removeObjects($values);
    }

    ksort($values);

    return $values;
  }

  /**
   * Removes objects from the array by replacing them with a String containing the class name.
   *
   * @param array $values an array
   *
   * @return array The array without objects
   */
  public static function removeObjects($values)
  {
    $nvalues = array();
    foreach ($values as $key => $value)
    {
      if (is_array($value))
      {
        $nvalues[$key] = self::removeObjects($value);
      }
      else if (is_object($value))
      {
        $nvalues[$key] = sprintf('%s Object()', get_class($value));
      }
      else
      {
        $nvalues[$key] = $value;
      }
    }

    return $nvalues;
  }

  /**
   * Returns user parameters as an array.
   *
   * @param sfUser $user A sfUser instance
   *
   * @return array The user parameters
   */
  public static function userAsArray(sfUser $user = null)
  {
    if (!$user)
    {
      return array();
    }

    $data = array(
      'attributeHolder' => self::flattenParameterHolder($user->getAttributeHolder(), true),
      'culture'         => $user->getCulture(),
    );

    if ($user instanceof sfBasicSecurityUser)
    {
      $data = array_merge($data, array(
          'authenticated'   => $user->isAuthenticated(),
          'credentials'     => $user->getCredentials(),
          'lastRequest'     => $user->getLastRequestTime(),
      ));
    }

    return $data;
  }

  /**
   * Dumps a variable in readable format
   *
   * @param mixed $var
   * @param boolean $exit Exit after dumping the variable (default is false)
   * @param array $stack Custom backtrace stack
   * @link http://redotheoffice.com/?p=65
   */
  public static function dump($var, $exit = false, $stack = null)
  {
    static $dumpCssIncluded;

    $cli = PHP_SAPI == 'cli' ? true : false;
    $scope = false;
    $prefix = 'unique';
    $suffix = 'value';

    if($scope)
    {
      $vals = $scope;
    }
    else
    {
      $vals = $GLOBALS;
    }

    $old = $var;
    $var = $new = $prefix . rand() . $suffix;
    $vname = false;

    foreach($vals as $key => $val)
    {
      if($val === $new)
      {
        $vname = $key;
      }
    }

    $var = $old;

    $stack = $stack == null ? debug_backtrace() : $stack;

    $_caller = $stack[1];
    $fileinfo = $stack[0];

    $caller = '';
    if(isset($_caller['class']) && isset($_caller['type']))
    {
      $caller = $_caller['class'].''.$_caller['type'];
    }

    $code = explode("\n", preg_replace('/\r\n|\r/', "\n", file_get_contents($fileinfo['file'])));

    $linkFormat = sfConfig::get('sf_file_link_format', 'editor://open?file=%f&line=%l');

    $output = strtr('<h2>Variable dump in <a href="%EDITOR_OPEN_LINK%">%CALLER%%%function%% on line %%line%%</a>: %%variable%%</h2>
    %DUMP%', array(
        '%EDITOR_OPEN_LINK%' => strtr($linkFormat, array('%f' => $fileinfo['file'], '%l' => $fileinfo['line'])),
        '%CALLER%' => self::getCallerInfo($stack),
        '%%function%%' => $_caller['function'],
        '%%line%%' => $fileinfo['line'],
        '%%variable%%' => htmlspecialchars(preg_replace('/(.*?)dump\((.*?)\);(.*)/i', '$2', $code[$fileinfo['line'] - 1])),
        '%DUMP%' => self::doDump($var, $vname)
    ));

    if(!$cli)
    {
      if(!$dumpCssIncluded)
      {
        $output .= '<style type="text/css">';
        $output .= <<< CSS
  pre.sf-var-dump {
    margin: 0.7em;
    padding: 0.5em;
    width: auto;
    display: block;
    background: #fff;
    font-size: 12px;
    font-family: Courier;
  }

  pre.sf-var-dump h2 {
    display: inline;
    margin: 0;
    padding: 0;
    font-size: 14px;
  }

  .sf-debug-integer {
    color: green;
  }

  .sf-debug-object {
    color: darkblue;
  }
CSS;
        $output .= '</style>';
        $dumpCssIncluded = true;
      }

      $output = sprintf('<pre class="sf-var-dump">%s</pre>', $output);
    }

    echo $output;

    if($exit)
    {
      exit;
    }
  }

  public static function getCallerInfo($trace)
  {
    $c = '';
    $file = '';
    $func = '';
    $class = '';
    if (isset($trace[2])) {
        $file = $trace[1]['file'];
        $func = $trace[2]['function'];
        if ((substr($func, 0, 7) == 'include') || (substr($func, 0, 7) == 'require')) {
            $func = '';
        }
    } else if (isset($trace[1])) {
        $file = $trace[1]['file'];
        $func = '';
    }
    if (isset($trace[3]['class'])) {
        $class = $trace[3]['class'];
        $func = $trace[3]['function'];
        $file = $trace[2]['file'];
    } else if (isset($trace[2]['class'])) {
        $class = $trace[2]['class'];
        $func = $trace[2]['function'];
        $file = $trace[1]['file'];
    }
    if ($file != '') $file = basename($file);
    $c = $file . ": ";
    $c .= ($class != '') ? ":" . $class . "->" : "";
    $c .= ($func != '') ? $func . "(): " : "";
    return($c);
}

  protected static function doDump(&$var, $var_name = null, $indent = null, $reference = null)
  {
    // $do_dump_indent = "<span style='color:#eeeeee;'>|</span> &nbsp;&nbsp; ";
    $do_dump_indent = ' &nbsp;&nbsp; ';
    $reference = $reference . $var_name;
    $keyvar = 'the_do_dump_recursion_protection_scheme';
    $keyname = 'referenced_object_name';

    $html = '';

    if(is_array($var) && isset($var[$keyvar]))
    {
      $real_var = &$var[$keyvar];
      $real_name = &$var[$keyname];
      $type = ucfirst(gettype($real_var));
      $html .= "$indent$var_name <span style=\"color:#a2a2a2\">$type</span> = <span style=\"color:#e87800;\">&amp;$real_name</span><br />";
    }
    else
    {
      $var = array($keyvar => $var, $keyname => $reference);
      $avar = &$var[$keyvar];

      $type = strtolower(gettype($avar));
      if($type == "string")
        $type_color = "<span style='color:green'>";
      elseif($type == "integer")
        $type_color = "<span style='color:red'>";
      elseif($type == "double")
      {
        $type_color = "<span style='color:#0099c5'>";
        $type = "float";
      }
      elseif($type == "boolean")
        $type_color = "<span style='color:#92008d'>";
      elseif($type == "null")
        $type_color = "<span style='color:black'>";

      if(is_array($avar))
      {
        $count = count($avar);
        $html .= "$indent" . ($var_name ? "$var_name => " : "") . "<span style='color:#a2a2a2'>$type ($count)</span><br>";
        if($count > 0)
        {
          $html .= "$indent(<br />";
          $keys = array_keys($avar);
          foreach($keys as $name)
          {
            $value = &$avar[$name];
            $html .= self::doDump($value, is_integer($name) ? "[$name]" : "['$name']", $indent . $do_dump_indent, $reference);
          }
          $html .= "$indent)<br>";
        }
      }
      elseif(is_object($avar))
      {

        // $html .= "$indent$var_name <span style='color:#a2a2a2'>is a</span> <b>" . get_class($avar) . "</b> (";
        $html .= sprintf('%s%s is a <span class="sf-debug-object">%s</span>', $indent, $var_name, get_class($avar));
        $i = 0;
        foreach($avar as $name => $value)
        {
          if($i == 0)
          {
            $html .= '<br />';
          }
          $html .= sprintf('%s', self::doDump($value, $name, $indent.$do_dump_indent, $reference));
          $i++;
        }

        if(method_exists($avar, '__toString'))
        {
          $html .= $indent.$do_dump_indent . '<br /><strong>__toString:</strong><br />';
          $html .= $indent.$do_dump_indent . strip_tags($avar->__toString()) . '<br />';
        }

        // $html .= $indent."<br />";
      }
      elseif(is_int($avar))
      {
        $html .= sprintf('%s%s = <span class="sf-debug-integer">%s</span> %s', $indent, $var_name, $type, $avar);
      }
      elseif(is_string($avar))
      {
        $html .= "$indent$var_name = <span style='color:#a2a2a2'>$type(" . sfUtf8::len($avar) . ")</span> $type_color\"$avar\"</span><br>";
      }

      elseif(is_float($avar))
        $html .= "$indent$var_name = <span style='color:#a2a2a2'>$type</span> $type_color$avar</span><br>";
      elseif(is_bool($avar))
        $html .= "$indent$var_name = <span style='color:#a2a2a2'>$type</span> $type_color" . ($avar == 1 ? "TRUE" : "FALSE") . "</span><br>";
      elseif(is_null($avar))
        $html .= "$indent$var_name = <span style='color:#a2a2a2'>$type</span><br>";
      else
        $html .= "$indent$var_name = <span style='color:#a2a2a2'>$type(" . sfUtf8::len($avar) . ")</span> $avar<br>";

      $var = $var[$keyvar];
    }

    return $html;
  }

  protected static function doDumpOLD(&$var, $var_name = null, $indent = null, $reference = null)
  {
    // $do_dump_indent = "<span style='color:#eeeeee;'>|</span> &nbsp;&nbsp; ";
    $do_dump_indent = "&nbsp;&nbsp;";
    $reference = $reference . $var_name;
    $keyvar = 'the_do_dump_recursion_protection_scheme';
    $keyname = 'referenced_object_name';

    $html = '';

    if(is_array($var) && isset($var[$keyvar]))
    {
      $real_var = &$var[$keyvar];
      $real_name = &$var[$keyname];
      $type = ucfirst(gettype($real_var));
      $html .= "$indent$var_name <span style=\"color:#a2a2a2\">$type</span> = <span style=\"color:#e87800;\">&amp;$real_name</span><br />";
    }
    else
    {
      $var = array($keyvar => $var, $keyname => $reference);
      $avar = &$var[$keyvar];

      $type = strtolower(gettype($avar));
      if($type == "string")
        $type_color = "<span style='color:green'>";
      elseif($type == "integer")
        $type_color = "<span style='color:red'>";
      elseif($type == "double")
      {
        $type_color = "<span style='color:#0099c5'>";
        $type = "float";
      }
      elseif($type == "boolean")
        $type_color = "<span style='color:#92008d'>";
      elseif($type == "null")
        $type_color = "<span style='color:black'>";

      if(is_array($avar))
      {
        $count = count($avar);
        $html .= "$indent" . ($var_name ? "$var_name => " : "") . "<span style='color:#a2a2a2'>$type ($count)</span><br>";
        if($count > 0)
        {
          $html .= "$indent(<br />";
          $keys = array_keys($avar);
          foreach($keys as $name)
          {
            $value = &$avar[$name];
            $html .= self::doDump($value, is_integer($name) ? "[$name]" : "['$name']", $indent . $do_dump_indent, $reference);
          }
          $html .= "$indent)<br>";
        }
      }
      elseif(is_object($avar))
      {
        $html .= "$indent$var_name <span style='color:#a2a2a2'>is a</span> <b>" . get_class($avar) . "</b> (";
        $newLine = false;

        foreach($avar as $name => $value)
        {
          if(!$newLine)
            $html .= "<br />";
          $newLine = true;

          $html .= self::doDump($value, "$name", $indent . $do_dump_indent, $reference);
        }

        if(method_exists($avar, '__toString'))
        {
          $html .= $indent . '<br /><strong>__toString:</strong><br />';
          $html .= $indent . strip_tags($avar->__toString()) . '<br />';
        }

        $html .= ($newLine ? $indent : "") . ")<br />";
      }
      elseif(is_int($avar))
        $html .= "$indent$var_name = <span style='color:#a2a2a2'>$type</span> $type_color$avar</span><br>";
      elseif(is_string($avar))
        $html .= "$indent$var_name = <span style='color:#a2a2a2'>$type(" . strlen($avar) . ")</span> $type_color\"$avar\"</span><br>";
      elseif(is_float($avar))
        $html .= "$indent$var_name = <span style='color:#a2a2a2'>$type</span> $type_color$avar</span><br>";
      elseif(is_bool($avar))
        $html .= "$indent$var_name = <span style='color:#a2a2a2'>$type</span> $type_color" . ($avar == 1 ? "TRUE" : "FALSE") . "</span><br>";
      elseif(is_null($avar))
        $html .= "$indent$var_name = <span style='color:#a2a2a2'>$type</span><br>";
      else
        $html .= "$indent$var_name = <span style='color:#a2a2a2'>$type(" . strlen($avar) . ")</span> $avar<br>";

      $var = $var[$keyvar];
    }

    return $html;
  }

}
