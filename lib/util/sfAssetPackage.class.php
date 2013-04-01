<?php
/*
 * This file is part of the Sift PHP framework.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * sfAssetPackage class provides a configuration object for asset packages.
 * Assets are registered in aseet_packages.yml configuration and can be altered
 * via event system event name "asset_packages.get_config".
 *
 * @package    Sift
 * @subpackage util
 */
class sfAssetPackage {

  /**
   * Configuration holder
   *
   * @var array
   */
  static $config = array();

  /**
   * Has event system already touched configuration?
   *
   * @var boolean
   */
  static $filtered = false;

  /**
   * Returns configuration array (filtered by event listeners to event
   * "asset_packages.get_config"
   *
   * @return array Configuration array
   */
  public static function getConfig()
  {
    if(!self::$filtered)
    {
      self::$config = sfCore::filterByEventListeners(
                      self::$config, 'asset_packages.get_config');
      self::$filtered = true;
    }
    return self::$config;
  }

  /**
   * Set configuration array
   *
   * @param array $config
   */
  public static function setConfig(array $config)
  {
    self::$config = $config;
    // reset filter flag
    self::$filtered = false;
  }

  /**
   * Returns javascripts for package
   *
   * @param string $name Package name eg. "ui"
   * @param boolen $includeRequired Include required scripts?
   * @return array
   */
  public static function getJavascripts($name, $includeRequired = true)
  {
    if(!is_array($name))
    {
      $name = array($name);
    }

    $result = array();
    foreach($name as $src)
    {
      if(!self::hasPackage($src))
      {
        throw new sfException(sprintf('{sfAssetPackage} Unknown package name "%s" or no javascript files configured for this package.', $src));
      }

      $javascripts = sfArray::get(self::getConfig(), sprintf('packages.%s.javascripts', $src), array());
      if($includeRequired)
      {
        // we include required before
        $result = array_merge($result, self::getRequiredJavascripts($src));
      }
      $result = array_merge($result, $javascripts);
    }
    return self::replaceVariables($result);
  }

  /**
   * Is package present on the configuration?
   *
   * @param string $name Name of the package
   * @return boolean
   */
  public static function hasPackage($name)
  {
    return sfArray::keyExists(self::getConfig(), sprintf('packages.%s', $name));
  }

  /**
   * Returns package stylesheets for package
   *
   * @param string $name Package name eg. "ui"
   * @param boolean $includeRequired Include also stylesheets from required modules?
   * @return array
   */
  public static function getStylesheets($name, $includeRequired = true)
  {
    if(!is_array($name))
    {
      $name = array($name);
    }

    $result = array();

    foreach($name as $src)
    {
      if(!self::hasPackage($src))
      {
        throw new sfException(sprintf('{sfAssetPackage} Unknown package name "%s".', $src));
      }

      $stylesheets = sfArray::get(self::getConfig(), sprintf('packages.%s.stylesheets', $src), array());

      if($includeRequired)
      {
        // get required packages
        foreach(sfArray::get(self::getConfig(), sprintf('packages.%s.require', $src), array()) as $s)
        {
          $result = array_merge($result, self::getStylesheets($s));
        }
      }
      $result = array_merge($result, $stylesheets);
    }

    return self::replaceVariables($result);
  }

  /**
   * Returns other packages javascripts required by package
   *
   * @param type $src
   * @return type array
   */
  public static function getRequiredJavascripts($src)
  {
    return self::getJavascripts(
                    sfArray::get(self::getConfig(), sprintf('packages.%s.require', $src), array())
    );
  }

  /**
   * Returns all configured packages
   *
   * @return array
   */
  public static function getAllPackages()
  {
    return sfArray::get(self::getConfig(), 'packages', array());
  }

  /**
   * Replaces dynamic path values. Supported values:
   *
   * %SF_CULTURE{0,2}% -> current culture substr($culture, 0, 2)
   * %SF_CULTURE%      -> current culture
   * %SF_JAVASCRIPT_PATH% -> javascript path /js (takes care also for relative_root)
   * %SF_STYLESHEET_PATH  -> stylesheet path /css (takes care also for relative_root)
   *
   * @param string $value
   * @param string key
   * @return string
   */
  public static function replaceVariables(&$value)
  {
    if(is_array($value))
    {
      foreach($value as $k => $v)
      {
        if(is_numeric($k))
        {
          $value[$k] = self::replaceVariables($value[$k]);
        }
        elseif(is_string($k))
        {
          $tmp = self::replaceVariables($value[$k]);
          unset($value[$k]);
          $value[self::replaceVariables($k)] = $tmp;
        }
      }
    }
    elseif(is_string($value))
    {
      $value = sfToolkit::replaceConstantsWithModifiers($value);
      $request = sfContext::getInstance()->getRequest();
      $sf_relative_url_root = $request->getRelativeUrlRoot();

      $value = str_replace(
              array(
          '%SF_JAVASCRIPT_PATH%', // BC compat
          '%SF_STYLESHEET_PATH%', // BC compat
          '%SF_JAVASCRIPT_WEB_PATH%',
          '%SF_STYLESHEET_WEB_PATH%',
              ), array(
          $sf_relative_url_root . '/js',
          $sf_relative_url_root . '/css',
          $sf_relative_url_root . '/js',
          $sf_relative_url_root . '/css'
              ), $value);
    }

    return $value;
  }

}
