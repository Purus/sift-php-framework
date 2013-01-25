<?php
/*
 * This file is part of the Sift PHP framework.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * Pre-initialization script.
 *
 * @package    Sift
 * @subpackage core
 * @author     Fabien Potencier <fabien.potencier@sift-project.com>
 * @author     Sean Kerr <sean@code-box.org>
 */
$sf_sift_lib_dir = sfConfig::get('sf_sift_lib_dir');
if(!sfConfig::get('sf_in_bootstrap'))
{
  // YAML support
  require_once($sf_sift_lib_dir . '/yaml/sfYaml.class.php');
  require_once($sf_sift_lib_dir . '/yaml/sfYamlParser.class.php');
  require_once($sf_sift_lib_dir . '/yaml/sfYamlInline.class.php');
  
  // inflector
  require_once($sf_sift_lib_dir . '/util/sfInflector.class.php');

  // configurable
  require_once($sf_sift_lib_dir . '/config/sfIConfigurable.interface.php');
  require_once($sf_sift_lib_dir . '/config/sfConfigurable.class.php');
  
  // cache support
  require_once($sf_sift_lib_dir . '/cache/sfCache.class.php');
  require_once($sf_sift_lib_dir . '/cache/sfFileCache.class.php');

  // config support
  require_once($sf_sift_lib_dir . '/config/sfConfigCache.class.php');
  require_once($sf_sift_lib_dir . '/config/sfConfigHandler.class.php');
  require_once($sf_sift_lib_dir . '/config/sfYamlConfigHandler.class.php');
  require_once($sf_sift_lib_dir . '/config/sfAutoloadConfigHandler.class.php');
  require_once($sf_sift_lib_dir . '/config/sfRootConfigHandler.class.php');
  require_once($sf_sift_lib_dir . '/core/sfLoader.class.php');

  
  // basic exception classes
  require_once($sf_sift_lib_dir . '/exception/sfException.class.php');
  // php error 2 exception converter
  require_once($sf_sift_lib_dir . '/exception/sfPhpErrorException.class.php');
  require_once($sf_sift_lib_dir . '/exception/sfAutoloadException.class.php');
  require_once($sf_sift_lib_dir . '/exception/sfCacheException.class.php');
  require_once($sf_sift_lib_dir . '/exception/sfConfigurationException.class.php');
  require_once($sf_sift_lib_dir . '/exception/sfParseException.class.php');

  // utils
  require_once($sf_sift_lib_dir . '/util/sfParameterHolder.class.php');
}
else
{
  require_once($sf_sift_lib_dir . '/config/sfConfigCache.class.php');
}

// autoloading
sfCore::initAutoload();

try
{
  // we set different modes for production environment
  // if we're in a prod environment we want E_ALL, but not to fail on E_NOTICE, E_WARNING or E_STRICT
  if(!sfConfig::get('sf_debug'))
  {
    set_error_handler(array('sfPhpErrorException', 'handleErrorCallback'), sfConfig::get('sf_error_reporting', E_ALL & ~E_NOTICE & ~E_WARNING));
  }
  else
  {
    // get from config or default to E_ALL without E_NOTICE (those E_NOTICEs can get annoying...)
    set_error_handler(array('sfPhpErrorException', 'handleErrorCallback'), sfConfig::get('sf_error_reporting', E_ALL & ~E_NOTICE));
  }

  register_shutdown_function(array('sfPhpErrorException', 'fatalErrorShutdownHandler'));

  $configCache = sfConfigCache::getInstance();

  // force setting default timezone if not set
  if(function_exists('date_default_timezone_get'))
  {
    date_default_timezone_set(@date_default_timezone_get());
  }

  // get config instance
  $sf_app_config_dir_name = sfConfig::get('sf_app_config_dir_name');

  $sf_debug = sfConfig::get('sf_debug');

  // load timer classes if in debug mode
  if($sf_debug)
  {
    require_once($sf_sift_lib_dir . '/debug/sfTimerManager.class.php');
    require_once($sf_sift_lib_dir . '/debug/sfTimer.class.php');
  }

  // load base settings
  include($configCache->checkConfig($sf_app_config_dir_name . '/settings.yml'));

  if(sfConfig::get('sf_logging_enabled', true))
  {
    include($configCache->checkConfig($sf_app_config_dir_name . '/logging.yml'));
  }
  if($file = $configCache->checkConfig($sf_app_config_dir_name . '/app.yml', true))
  {
    include($file);
  }
  if(sfConfig::get('sf_i18n'))
  {
    $i18nConfig = include($configCache->checkConfig($sf_app_config_dir_name . '/i18n.yml'));
    sfConfig::add($i18nConfig);

    /**
     * Translate function
     *
     * @staticvar sfI18n $i18n
     * @param string $text
     * @param array $args
     * @param string $catalogue
     * @return string
     */
    function __($text, $args = array(), $catalogue = 'messages')
    {
      static $i18n;
      if(!isset($i18n))
      {
        $i18n = sfContext::getInstance()->getI18N();
      }
      return $i18n->__($text, $args, $catalogue);
    }

  }
  else
  {
    /**
     * Translate function
     *
     * @param string $text
     * @param array $args
     * @param string $catalogue
     * @return string
     */
    function __($string, $args = array(), $catalogue = 'messages')
    {
      if(empty($args))
      {
        $args = array();
      }
      // replace object with strings
      foreach($args as $key => $value)
      {
        if(is_object($value) && method_exists($value, '__toString'))
        {
          $args[$key] = $value->__toString();
        }
      }
      return strtr($string, $args);
    }

  }

  // add autoloading callables
  foreach((array) sfConfig::get('sf_autoloading_functions', array()) as $callable)
  {
    sfCore::addAutoloadCallable($callable);
  }

  // error settings
  ini_set('display_errors', $sf_debug ? 'on' : 'off');
  error_reporting(sfConfig::get('sf_error_reporting'));

  // create bootstrap file for next time
  if(!sfConfig::get('sf_in_bootstrap') && !$sf_debug && !sfConfig::get('sf_test'))
  {
    $configCache->checkConfig($sf_app_config_dir_name . '/bootstrap_compile.yml');
  }

  // required core classes for the framework
  // create a temp var to avoid substitution during compilation
  if(!$sf_debug && !sfConfig::get('sf_test'))
  {
    $core_classes = $sf_app_config_dir_name . '/core_compile.yml';
    $configCache->import($core_classes, false);
  }

  
  $configCache->import($sf_app_config_dir_name . '/php.yml', false);
  $configCache->import($sf_app_config_dir_name . '/routing.yml', false);

  // setup generation of html tags (Xhtml vs HTML)
  sfCore::initHtmlTagConfiguration();
  // include all config.php from plugins
  sfCore::loadPluginConfig();
  // enable modules
  sfCore::enableModules();

  // import text macros configuration
  include(sfConfigCache::getInstance()->checkConfig('config/text_macros.yml'));
  
  include(sfConfigCache::getInstance()->checkConfig('config/asset_packages.yml'));
  
  // force setting default timezone if not set
  if(function_exists('date_default_timezone_set'))
  {
    if($default_timezone = sfConfig::get('sf_default_timezone'))
    {
      date_default_timezone_set($default_timezone);
    }
  }

  if(sfConfig::get('sf_environment') != 'cli' || php_sapi_name() != 'cli')
  {
    // start output buffering
    ob_start();
  }
}
catch(sfException $e)
{
  $e->printStackTrace();
}
catch(Exception $e)
{
  if(sfConfig::get('sf_test'))
  {
    throw $e;
  }

  try
  {
    // wrap non sift exceptions
    $sfException = new sfException();
    $sfException->printStackTrace($e);
  }
  catch(Exception $e)
  {
    header('HTTP/1.0 500 Internal Server Error');
  }
}
