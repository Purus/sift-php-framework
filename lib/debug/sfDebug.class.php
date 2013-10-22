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
    return array(
        'Version' => PHP_VERSION,
        'Ini location' => get_cfg_var('cfg_file_path'),
        'Extensions' => join(', ', self::getPhpLoadedExtensions()),
        'Disabled functions' => ini_get('disable_functions'),
        'Disabled classes' => ini_get('disable_classes'),
        'Session Hash function' => ini_get('session.hash_function'),
        'Session Hash bit/character' => ini_get('session.hash_bits_per_character'),
        'Log errors (to system)' => (boolean) ini_get('log_errors'),
        'System error log' => ini_get('error_log'),
        'Can call system executables' => sfToolkit::isCallable('exec') && sfToolkit::isCallable('shell_exec'),
        'Memory limit' => ini_get('memory_limit'),
        'Open base directory (open_basedir)' => ini_get('open_basedir'),
        'Max execution time (max_execution_time)' => ini_get('max_execution_time'),
        'Post max size (post_max_size)' => ini_get('post_max_size'),
        'PDO installed' => class_exists('PDO'),
        'PDO Drivers' => join(', ', class_exists('PDO') ? PDO::getAvailableDrivers() : array()),
        'GD' => function_exists('gd_info'),
        'GD info' => function_exists('gd_info') ? gd_info() : '',
        'Imagick' => class_exists('Imagick'),
        'Imagick info' => class_exists('Imagick') ? Imagick::getVersion() : ''
    );
  }

  /**
   * Returns plugin information
   *
   * @return array
   */
  public static function pluginsInfoAsArray()
  {
    $plugins = array();
    foreach(sfContext::getInstance()->getApplication()->getPlugins() as $plugin)
    {
      $plugins[] = array(
          'name' => $plugin->getName(),
          'version' => $plugin->getVersion(),
          'root_dir' => self::shortenFilePath($plugin->getRootDir())
      );
    }
    return $plugins;
  }

  /**
   * Returns an array of loaded extensions
   *
   * @return array
   */
  protected static function getPhpLoadedExtensions()
  {
    $extensions = get_loaded_extensions();
    asort($extensions);
    return $extensions;
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
        'php' => phpversion(),
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
    foreach(array('sf_plugins_dir', 'sf_root_dir', 'sf_sift_lib_dir') as $key)
    {
      if(0 === strpos($file, $value = sfConfig::get($key)))
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
    if($parameterHolder instanceof sfFlatParameterHolder)
    {
      foreach($parameterHolder->getAll() as $key => $value)
      {
        $values[$key] = $value;
      }
    }
    else
    {
      foreach($parameterHolder->getNamespaces() as $ns)
      {
        $values[$ns] = array();
        foreach($parameterHolder->getAll($ns) as $key => $value)
        {
          $values[$ns][$key] = $value;
        }
        ksort($values[$ns]);
      }
    }

    if($removeObjects)
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
    foreach($values as $key => $value)
    {
      if(is_array($value))
      {
        $nvalues[$key] = self::removeObjects($value);
      }
      else if(is_object($value))
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
    if(!$user)
    {
      return array();
    }

    $data = array(
        'attributeHolder' => self::flattenParameterHolder($user->getAttributeHolder(), true),
        'culture' => $user->getCulture(),
    );

    if($user instanceof sfBasicSecurityUser)
    {
      $data = array_merge($data, array(
          'authenticated' => $user->isAuthenticated(),
          'credentials' => $user->getCredentials(),
          'lastRequest' => $user->getLastRequestTime(),
      ));
    }

    return $data;
  }

}
