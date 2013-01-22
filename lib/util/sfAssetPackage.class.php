<?php
/*
 * This file is part of the Sift PHP framework.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * sfAssetPackage class
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
   * @var type boolean 
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

    $config = self::getConfig();
    
    $result = array();
    foreach($name as $src)
    {
      if(!isset($config['packages'][$src]['javascripts']))
      {
        if($config['strict_mode'])
        {
          throw new sfException(sprintf('{sfAssetPackage} Unknown package name "%s" or no javascript files configured for this package.', $src));
        }
        else
        {
          // quess package name, we assume its jquery plugin
          $config['packages'][$src]['javascripts'] = 
            sfConfig::get('sf_environment') == 'dev' ?
              array(sprintf('%1$s/jquery.%1$s.js', $src)) :
              // minimised version in other then dev environments
              array(sprintf('%1$s/jquery.%1$s.min.js', $src));                    
        }
      }

      if($includeRequired)
      {
        // we include required before
        $result = array_merge($result, self::getRequiredJavascripts($src));
      }

      // unset javascripts which are excluded
      if(isset($config['packages'][$src]['i18n']['exclude']))
      {
        foreach($config['packages'][$src]['javascripts'] as $i => $js)
        {
          $culture = sfContext::getInstance()->getUser()->getCulture();
          if(preg_match('~%SF_CULTURE({(\d)+,(\d)+})?%~', $js, $matches))
          {
            if(isset($matches[2]) && isset($matches[3]))
            {
              $culture = substr($culture, $matches[2], $matches[3]);
            }                      
          }
          if(stripos($js, '%SF_CULTURE') !== false 
              && in_array($culture, (array)$config['packages'][$src]['i18n']['exclude']))
          {
            unset($config['packages'][$src]['javascripts'][$i]);
          }
        }
      }
      $result = array_merge($result, array_map(array('sfAssetPackage', 'replaceVariables'), 
                            $config['packages'][$src]['javascripts']));
    }
    return $result;
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
    $config = self::getConfig();
    
    if(!is_array($name))
    {
      $name = array($name);
    }
    
    $result = array();
    foreach($name as $src)
    {
      if(!isset($config['packages'][$src]['stylesheets']))
      {
        if($config['strict_mode'])
        {      
          throw new sfException(sprintf('{sfAssetPackage} Unknown package name "%s" or no stylesheets files configured for this package.', $src));
        }
        else
        {
          // no stylesheets 
          $config['packages'][$src]['stylesheets'] = array();
        }
      }
      if($includeRequired && isset($config['packages'][$src]['require']))
      {
        foreach($config['packages'][$src]['require'] as $s)
        {
          $result = array_merge($result, self::getStylesheets($s, false));
        }        
      }      
      $result =  array_merge($result, array_map(array('sfAssetPackage', 'replaceVariables'),
              $config['packages'][$src]['stylesheets']));      
    }    
    
    return $result;
  }

  /**
   * Returns other packages javascripts required by package
   * 
   * @param type $src
   * @return type array
   */
  public static function getRequiredJavascripts($src)
  {
    $config = self::getConfig();
    if(isset($config['packages'][$src]['require']))
    {
      $require = (array)$config['packages'][$src]['require']; 
      return self::getJavascripts($require);
    }
    return array();    
  }
  
  /**
   * Returns all configured packages
   * 
   * @return array
   */
  public static function getAllPackages()
  {
    $config = self::getConfig();
    return isset($config['packages']) ? (array)$config['packages'] : array();
  }

  /**
   * Replaces dynamic path values. Supported values:
   *
   * %SF_CULTURE{0,2}% -> current culture substr($culture, 0, 2)
   * %SF_CULTURE%      -> current culture
   * %SF_JAVASCRIPT_PATH% -> javascript path /js (takes care also for relative_root)
   * %SF_STYLESHEET_PATH  -> stylesheet path /css (takes care also for relative_root)
   * %SF_JQUERY_WEB_DIR%  -> web directory where jquery sits
   *
   * @param string $value
   * @return string
   */
  protected static function replaceVariables($value)
  {
    // %SF_CULTURE{0,2}% -> substr($culture, 0, 2)
    // %SF_CULTURE%
    if(preg_match('~%SF_CULTURE({(\d)+,(\d)+})?%~', $value, $matches))
    {
      $culture = sfContext::getInstance()->getUser()->getCulture();
      if(isset($matches[2]) && isset($matches[3]))
      {
        $culture = substr($culture, $matches[2], $matches[3]);
      }
      $value = preg_replace('~%SF_CULTURE(.*)?%~', $culture, $value);      
    }

    $request = sfContext::getInstance()->getRequest();
    $sf_relative_url_root = $request->getRelativeUrlRoot();

    $value = str_replace(
    array(
      '%SF_JAVASCRIPT_PATH%', // BC compat
      '%SF_STYLESHEET_PATH%', // BC compat
      '%SF_JAVASCRIPT_WEB_PATH%',
      '%SF_STYLESHEET_WEB_PATH%',
    ),
    array(
      $sf_relative_url_root.'/js',
      $sf_relative_url_root.'/css',
      $sf_relative_url_root.'/js',
      $sf_relative_url_root.'/css'        
    ), $value);

    // absolute path or protocol
    if(strpos($value, '/') !== 0 && strpos($value, '://') == false)
    {
      // we skip stylesheets, these reside in /css directory
      if(strpos($value, '.css') === false)
      {
        // $value = sprintf('%s/%s', self::getPackagePath(), $value);
      }
    }
    
    return $value;
  }
  
}
