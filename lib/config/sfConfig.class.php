<?php
/*
 * This file is part of the Sift PHP framework.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * sfConfig stores all configuration information for a Sift application.
 *
 * @package    Sift
 * @subpackage config
 */
class sfConfig
{
  protected static $config = array();

  /**
   * Retrieves a config parameter.
   *
   * @param string A config parameter name
   * @param mixed  A default config parameter value
   *
   * @return mixed A config parameter value, if the config parameter exists, otherwise null
   */
  public static function get($name, $default = null)
  {
    if(isset(self::$config[$name]))
    {
      return self::$config[$name];
    }

    // no dot notation
    if(strpos($name, '.') === false)
    {
      return $default;
    }

    return sfArray::get(self::$config, $name, $default);
  }

  /**
   * Indicates whether or not a config parameter exists.
   *
   * @param string A config parameter name
   *
   * @return bool true, if the config parameter exists, otherwise false
   */
  public static function has($name)
  {
    if(strpos($name, '.') === false)
    {
      return array_key_exists($name, self::$config);
    }

    return sfArray::keyExists(self::$config, $name);
  }

  /**
   * Sets a config parameter.
   *
   * If a config parameter with the name already exists the value will be overridden.
   *
   * @param string A config parameter name
   * @param mixed  A config parameter value
   */
  public static function set($name, $value)
  {
    // not dot notation
    if(strpos($name, '.') === false)
    {
      self::$config[$name] = $value;
    }
    else
    {
      sfArray::set(self::$config, $name, $value);
    }
  }

  /**
   * Sets an array of config parameters.
   *
   * If an existing config parameter name matches any of the keys in the supplied
   * array, the associated value will be overridden.
   *
   * @param array An associative array of config parameters and their associated values
   */
  public static function add($parameters = array())
  {
    foreach($parameters as $p => $v)
    {
      self::set($p, $v);
    }
  }

  /**
   * Retrieves all configuration parameters.
   *
   * @return array An associative array of configuration parameters.
   */
  public static function getAll()
  {
    return self::$config;
  }

  /**
   * Clears all current config parameters.
   */
  public static function clear()
  {
    self::$config = null;
    self::$config = array();
  }

}
