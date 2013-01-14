<?php
/*
 * This file is part of the Sift PHP framework.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * sfDebug provides some method to help debugging a symfony application.
 *
 * @package    Sift
 * @subpackage debug
 * @author     Fabien Potencier <fabien.potencier@symfony-project.com>
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
   * @param sfParameterHolder A sfParameterHolder instance
   *
   * @return array The parameter holder as an array
   */
  public static function flattenParameterHolder($parameterHolder)
  {
    $values = array();
    foreach($parameterHolder->getNamespaces() as $ns)
    {
      $values[$ns] = array();
      foreach($parameterHolder->getAll($ns) as $key => $value)
      {
        $values[$ns][$key] = $value;
      }
      ksort($values[$ns]);
    }

    ksort($values);

    return $values;
  }

  /**
   * 
   * *  * @see http://redotheoffice.com/?p=65
   */

  /**
   * Dumps a variable in readable format
   * 
   * @param mixed $var 
   * @param boolean $exit Exit after dumping the variable (default is false)
   */
  public static function dump($var, $exit = false, $info = false)
  {
    $scope = false;
    $prefix = 'unique';
    $suffix = 'value';

    if($scope)
      $vals = $scope;
    else
      $vals = $GLOBALS;

    $old = $var;
    $var = $new = $prefix . rand() . $suffix;
    $vname = FALSE;
    foreach($vals as $key => $val)
      if($val === $new)
        $vname = $key;
    $var = $old;

    $html = '<pre class="sfVarDump">';

    if($info != false)
    {
      $html .= '<strong class="sfVarDumpInfo">' . htmlspecialchars($info) . ':</strong><br />';
    }
    else
    {
      $stack = debug_backtrace();
      $caller = $stack[1];
      $fileinfo = $stack[0];

      $html .= 'in <strong>';
      if(isset($caller['class']) && isset($caller['type']))
      {
        $html .= "{$caller['class']}{$caller['type']}";
      }

      $html .= "{$caller['function']}</strong> on line {$fileinfo['line']}: ";
      $code = explode("\n", preg_replace('/\r\n|\r/', "\n", file_get_contents($fileinfo['file'])));
      $vname = "<strong>" . htmlspecialchars(preg_replace('/(.*?)dump\((.*?)\);(.*)/i', '$2', $code[$fileinfo['line'] - 1])) . "</strong>";
    }

    $html .= self::doDump($var, $vname);

    $html .= "</pre>";

    echo $html;

    if($exit)
    {
      exit;
    }
  }

  protected static function doDump(&$var, $var_name = null, $indent = null, $reference = null)
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
