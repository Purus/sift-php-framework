<?php
/*
 * This file is part of the Sift PHP framework.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
 
if(!function_exists('http_build_url'))
{
  define('HTTP_URL_REPLACE', 1);          // Replace every part of the first URL when there's one of the second URL
	define('HTTP_URL_JOIN_PATH', 2);        // Join relative paths
	define('HTTP_URL_JOIN_QUERY', 4);       // Join query strings
	define('HTTP_URL_STRIP_USER', 8);       // Strip any user authentication information
	define('HTTP_URL_STRIP_PASS', 16);      // Strip any password authentication information
	define('HTTP_URL_STRIP_AUTH', 32);      // Strip any authentication information
	define('HTTP_URL_STRIP_PORT', 64);      // Strip explicit port numbers
	define('HTTP_URL_STRIP_PATH', 128);     // Strip complete path
	define('HTTP_URL_STRIP_QUERY', 256);    // Strip query string
	define('HTTP_URL_STRIP_FRAGMENT', 512);	// Strip any fragments (#identifier)
	define('HTTP_URL_STRIP_ALL', 1024);			// Strip anything but scheme and host
}

// shortcut
define('DS', DIRECTORY_SEPARATOR);

/**
 * Core class
 *
 * @package    Sift
 * @subpackage core
 * @author     Fabien Potencier <fabien.potencier@symfony-project.com>
 */
class sfCore
{
  /**
   * Shorthand for carrige return.
   */
  const CR = "\r";

  /**
   * Shorthand for directory separator.
   */
  const DS = DIRECTORY_SEPARATOR;

  /**
   * Shorthand for line feed.
   */
  const LF = "\n";

  /**
   * Shorthand for path separator.
   */
  const PS = PATH_SEPARATOR;

  static protected
    $autoloadCallables = array(),
    $classes           = array(),
    $dispatcher        = null,
    // content filters
    $filters           = array();

  /**
   * Bootstraps the framework
   * 
   * @param string $sf_sift_lib_dir
   * @param string $sf_sift_data_dir
   */
  public static function bootstrap($sf_sift_lib_dir, $sf_sift_data_dir)
  {   
    require_once $sf_sift_lib_dir.'/autoload/sfCoreAutoload.class.php';
    require_once($sf_sift_lib_dir.'/util/sfToolkit.class.php');
    require_once($sf_sift_lib_dir.'/config/sfConfig.class.php');
    require_once($sf_sift_lib_dir.'/core/sfDimensions.class.php');

    sfCore::init($sf_sift_lib_dir, $sf_sift_data_dir);    
    sfCore::initIncludePath();
    sfCore::callBootstrap();

    if(sfConfig::get('sf_check_lock'))
    {
      sfCore::checkLock();
    }
    
    if(sfConfig::get('sf_check_sift_version'))
    {
      sfCore::checkSiftVersion();
    }
  }

  /**
   * Displays error page 
   * 
   * @param sfException $exception
   * @param string $error
   */
  public static function displayErrorPage($exception, $error = 'error500')
  {
    $files = array(
      sprintf(sfConfig::get('sf_app_config_dir').'/%s.php', $error),  
      sprintf(sfConfig::get('sf_config_dir').'/%s.php', $error),
      sfConfig::get('sf_web_dir').'/errors/error500.php',
      sfConfig::get('sf_sift_data_dir').'/errors/error500.php'
    );
        
    foreach($files as $file)
    {
      $file = str_replace('/', DIRECTORY_SEPARATOR, $file);
      if(is_readable($file))
      {
        include $file;
        break;
      }
    }
    if(!sfConfig::get('sf_test'))
    {
      exit(1);
    }   
  }

  public static function callBootstrap()
  {    
    $bootstrap = sfConfig::get('sf_config_cache_dir').'/config_bootstrap_compile.yml.php';
    if(is_readable($bootstrap))
    {
      sfConfig::set('sf_in_bootstrap', true);
      require($bootstrap);
    }
    else
    {
      require(sfConfig::get('sf_sift_lib_dir').'/sift.php');
    }
  }

  static public function init($sf_sift_lib_dir, $sf_sift_data_dir, $test = false)
  {
    // start timer
    if(SF_DEBUG)
    {
      sfConfig::set('sf_timer_start', microtime(true));
    }

    // main configuration
    sfConfig::add(array(
      'sf_root_dir'         => SF_ROOT_DIR,
      'sf_app'              => SF_APP,
      'sf_environment'      => SF_ENVIRONMENT,
      'sf_debug'            => SF_DEBUG,
      'sf_sift_lib_dir'     => $sf_sift_lib_dir,
      'sf_sift_data_dir'    => $sf_sift_data_dir,
      'sf_test'             => $test,
    ));

    $dimensionFile = sprintf('%s/config/dimension.php', sfConfig::get('sf_root_dir'), SF_APP);
    
    if(is_readable($dimensionFile))
    {
      include $dimensionFile;
    }    
    
    self::initConfiguration();
  }

  public static function initConfiguration()
  {
    $sf_root_dir    = sfConfig::get('sf_root_dir');
    $sf_app         = sfConfig::get('sf_app');
    $sf_environment = sfConfig::get('sf_environment');

    sfConfig::add(array(
      // dimensions
      // the current dimension as an array
      'sf_dimension'        => $sf_dimension = sfDimensions::getDimension(),
      // stores the dimension directories that sift will search through
      'sf_dimension_dirs'   => $sf_dimension = sfDimensions::getDimensionDirs(),
      // root directory names
      'sf_bin_dir_name'     => $sf_bin_dir_name     = 'batch',
      'sf_cache_dir_name'   => $sf_cache_dir_name   = 'cache',
      'sf_log_dir_name'     => $sf_log_dir_name     = 'log',
      'sf_lib_dir_name'     => $sf_lib_dir_name     = 'lib',
      'sf_web_dir_name'     => defined('SF_WEB_DIR_NAME') ? $sf_web_dir_name = SF_WEB_DIR_NAME : $sf_web_dir_name = 'web',
      'sf_upload_dir_name'  => defined('SF_UPLOAD_DIR_NAME') ? $sf_upload_dir_name  = SF_UPLOAD_DIR_NAME : $sf_upload_dir_name = 'files',
      'sf_data_dir_name'    => $sf_data_dir_name    = 'data',
      'sf_config_dir_name'  => $sf_config_dir_name  = 'config',
      'sf_apps_dir_name'    => $sf_apps_dir_name    = 'apps',
      'sf_test_dir_name'    => $sf_test_dir_name    = 'test',
      'sf_doc_dir_name'     => $sf_doc_dir_name     = 'doc',
      'sf_plugins_dir_name' => $sf_plugins_dir_name = 'plugins',

      'sf_apps_dir'         => $sf_apps_dir = $sf_root_dir.DS.$sf_apps_dir_name,

      // global directory structure
      'sf_app_dir'        => $sf_app_dir = $sf_apps_dir.DS.$sf_app,
      'sf_lib_dir'        => $sf_lib_dir = $sf_root_dir.DS.$sf_lib_dir_name,
      'sf_bin_dir'        => $sf_root_dir.DS.$sf_bin_dir_name,
      'sf_web_dir'        => $sf_root_dir.DS.$sf_web_dir_name,
      'sf_upload_dir'     => $sf_root_dir.DS.$sf_web_dir_name.DS.$sf_upload_dir_name,
      // this is usually project/cache, but with dimensions this is changed to project/cache/dimension
      //'sf_root_cache_dir' => $sf_root_cache_dir = $sf_root_dir.DS.$sf_cache_dir_name.(sfDimensions::getDimensionString() ? DS . sfDimensions::getDimensionString() : ''),
      'sf_root_cache_dir' => $sf_root_cache_dir = $sf_root_dir.DS.$sf_cache_dir_name,
      'sf_base_cache_dir' => $sf_base_cache_dir = $sf_root_cache_dir.DS.$sf_app,
      'sf_cache_dir'      => $sf_cache_dir      = $sf_base_cache_dir.DS.$sf_environment.(sfDimensions::getDimensionString() ? DS . sfDimensions::getDimensionString() : ''),
      'sf_log_dir'        => $sf_root_dir.DS.$sf_log_dir_name,
      'sf_data_dir'       => $sf_root_dir.DS.$sf_data_dir_name,
      'sf_config_dir'     => $sf_root_dir.DS.$sf_config_dir_name,
      'sf_test_dir'       => $sf_root_dir.DS.$sf_test_dir_name,
      'sf_doc_dir'        => $sf_root_dir.DS.'data'.DS.$sf_doc_dir_name,
      'sf_plugins_dir'    => $sf_root_dir.DS.$sf_plugins_dir_name,

      // lib directory names
      'sf_model_dir_name'      => $sf_model_dir_name = 'model',

      // lib directory structure
      'sf_model_lib_dir'  => $sf_lib_dir.DS.$sf_model_dir_name,

      // directory structure
      'sf_template_cache_dir' => $sf_cache_dir.DS.'template',
      'sf_i18n_cache_dir'     => $sf_cache_dir.DS.'i18n',
      'sf_config_cache_dir'   => $sf_cache_dir.DS.$sf_config_dir_name,
      'sf_test_cache_dir'     => $sf_cache_dir.DS.'test',
      'sf_module_cache_dir'   => $sf_cache_dir.DS.'modules',

      // SF_APP_DIR sub-directories names
      'sf_app_i18n_dir_name'     => $sf_app_i18n_dir_name     = 'i18n',
      'sf_app_config_dir_name'   => $sf_app_config_dir_name   = 'config',
      'sf_app_lib_dir_name'      => $sf_app_lib_dir_name      = 'lib',
      'sf_app_module_dir_name'   => $sf_app_module_dir_name   = 'modules',
      'sf_app_template_dir_name' => $sf_app_template_dir_name = 'templates',

      // SF_APP_DIR directory structure
      'sf_app_config_dir'   => $sf_app_dir.DS.$sf_app_config_dir_name,
      'sf_app_lib_dir'      => $sf_app_dir.DS.$sf_app_lib_dir_name,
      'sf_app_module_dir'   => $sf_app_dir.DS.$sf_app_module_dir_name,
      'sf_app_template_dir' => $sf_app_dir.DS.$sf_app_template_dir_name,
      'sf_app_i18n_dir'     => $sf_app_dir.DS.$sf_app_i18n_dir_name,

      // SF_APP_MODULE_DIR sub-directories names
      'sf_app_module_action_dir_name'   => 'actions',
      'sf_app_module_template_dir_name' => 'templates',
      'sf_app_module_lib_dir_name'      => 'lib',
      'sf_app_module_view_dir_name'     => 'views',
      'sf_app_module_validate_dir_name' => 'validate',
      'sf_app_module_config_dir_name'   => 'config',
      'sf_app_module_i18n_dir_name'     => 'i18n',
      // image font directory
      'sf_image_font_dir'                => $sf_root_dir.DS.$sf_data_dir_name.DS.'fonts',
    ));

  }
  
  static public function initIncludePath()
  {
    set_include_path(
      sfConfig::get('sf_lib_dir').PATH_SEPARATOR.
      sfConfig::get('sf_root_dir').PATH_SEPARATOR.
      sfConfig::get('sf_app_lib_dir').PATH_SEPARATOR.
      sfConfig::get('sf_sift_lib_dir').DIRECTORY_SEPARATOR.'vendor'.PATH_SEPARATOR.
      get_include_path()
    );
  }

  /**
   * Checks if is the application locked. If yes, tries to display error message in the following order:
   * 
   *  * sfConfig::get('sf_app_config_dir').'/unavailable.php',
   *  * sfConfig::get('sf_config_dir').'/unavailable.php',
   *  * sfConfig::get('sf_web_dir').'/errors/unavailable.php',
   *  * sfConfig::get('sf_sift_data_dir').'/errors/unavailable.php',
   * 
   * @return void
   */
  public static function checkLock()
  {
    if(sfToolkit::hasLockFile(sfConfig::get('sf_data_dir').DIRECTORY_SEPARATOR.SF_APP.'_'.SF_ENVIRONMENT.'-cli.lck', 5)
      ||
      sfToolkit::hasLockFile(sfConfig::get('sf_data_dir').DIRECTORY_SEPARATOR.SF_APP.'_'.SF_ENVIRONMENT.'.lck'))
    {
      // application is not available - we'll find the most specific unavailable page...
      $files = array(
        sfConfig::get('sf_app_config_dir').'/unavailable.php',
        sfConfig::get('sf_config_dir').'/unavailable.php',
        sfConfig::get('sf_web_dir').'/errors/unavailable.php',
        sfConfig::get('sf_sift_data_dir').'/errors/unavailable.php',
      );
      
      foreach($files as $file)
      {
        if(is_readable($file))
        {
          header("HTTP/1.1 503 Service Temporarily Unavailable");
          header("Status: 503 Service Temporarily Unavailable");
          include $file;
          break;
        }
      }
      
      die(1);      
    }
  }

  /**
   * Checks if Sift has been currently updated. If yes, clears the cache directory.
   * 
   */
  public static function checkSiftVersion()
  {
    // recent Sift update?
    $last_version    = @file_get_contents(sfConfig::get('sf_config_cache_dir').'/VERSION');
    $current_version = sfCore::getVersion();
    if ($last_version != $current_version)
    {
      // clear cache
      sfToolkit::clearDirectory(sfConfig::get('sf_config_cache_dir'));
    }
  }

  static public function getClassPath($class)
  {
    return isset(self::$classes[$class]) ? self::$classes[$class] : null;
  }

  static public function addAutoloadCallable($callable)
  {
    self::$autoloadCallables[] = $callable;

    if (function_exists('spl_autoload_register'))
    {
      spl_autoload_register($callable);
    }
  }

  static public function getAutoloadCallables()
  {
    return self::$autoloadCallables;
  }

  /**
   * Handles autoloading of classes that have been specified in autoload.yml.
   *
   * @param  string  A class name.
   *
   * @return boolean Returns true if the class has been loaded
   */
  static public function splAutoload($class)
  {
    // load the list of autoload classes
    if (!self::$classes)
    {
      $file = sfConfigCache::getInstance()->checkConfig(sfConfig::get('sf_app_config_dir_name').'/autoload.yml');
      self::$classes = include($file);
    }

    $class = strtolower($class);
    
    // class already exists
    if(class_exists($class, false))
    {
      return true;
    }

    // we have a class path, let's include it
    if(isset(self::$classes[$class]))
    {
      require(self::$classes[$class]);

      return true;
    }

    // see if the file exists in the current module lib directory
    // must be in a module context
    if (sfContext::hasInstance() && ($module = sfContext::getInstance()->getModuleName()) && isset(self::$classes[$module.'/'.$class]))
    {
      require(self::$classes[$module.'/'.$class]);

      return true;
    }

    return false;
  }

  /**
   * Inits autoloading of the classes
   * 
   */
  public static function initAutoload()
  {
    if (function_exists('spl_autoload_register'))
    {
      ini_set('unserialize_callback_func', 'spl_autoload_call');
    }
    else if (!function_exists('__autoload'))
    {
      ini_set('unserialize_callback_func', '__autoload');

      function __autoload($class)
      {
        foreach (sfCore::getAutoloadCallables() as $callable)
        {
          if (call_user_func($callable, $class))
          {
            return true;
          }
        }

        // unspecified class
        // do not print an error if the autoload came from class_exists
        $trace = debug_backtrace();
        if (count($trace) < 1 || ($trace[1]['function'] != 'class_exists' && $trace[1]['function'] != 'is_a'))
        {
          $e = new sfAutoloadException(sprintf('Autoloading of class "%s" failed. Try to clear cache and refresh.', $class));
          $e->printStackTrace();
        }
      }
    }
    self::addAutoloadCallable(array('sfCore', 'splAutoload'));
  }

  static public function splSimpleAutoload($class)
  {
    // class already exists
    if (class_exists($class, false))
    {
      return true;
    }

    // we have a class path, let's include it
    if (isset(self::$classes[$class]))
    {
      require(self::$classes[$class]);

      return true;
    }

    return false;
  }

  /**
   * Setups (X)html generation for sfHtml and sfWidget classes
   * based on sf_html5 setting
   * 
   */
  public static function initHtmlTagConfiguration()
  {
    if(sfConfig::get('sf_html5'))
    {
      sfHtml::setXhtml(false);
      sfWidget::setXhtml(false);
    }    
  }
  
  /**
   * Add a filter call back. Tell sfCore that a filter is to be run on a filter
   * at a certain point.
   *
   * $tag          string  Name of the filter to hook.
   * $function     string  Callable function to be run on the hoook
   * $priority     integer Priority of this filter, default is 10 (higher value, higher priority)
   */
  public static function addFilter($tag, $function, $priority = 10)
  {
    if(!isset(self::$filters[$tag]))
    {
      self::$filters[$tag] = array();
    }

    if(!isset(self::$filters[$tag][$priority]))
    {
      self::$filters[$tag][$priority] = array();
    }

    self::$filters[$tag][$priority][serialize($function)] = $function;
  }

  /**
   * Remove a filter added previously. Called with the same arguments as addfilter
   *
   * $tag          string  Name of the filter to remove.
   * $method       string  Name of method to remove
   * $class        string  Name of the class providing the function
   * $priority     integer Prority of this filter, default is 10
   */
  public static function removeFilter($tag, $function, $priority = 10)
  {
    if(isset(self::$filters[$tag][$priority][serialize($function)]))
    {
      unset(self::$filters[$tag][$priority][serialize($function)]);
      return true;
    }
    return false;
  }

  /**
   * Apply filters to a tag.
   *
   * $tag  string Name of the filter
   * $data string The data the filter has to work on
   */
  public static function applyFilters($tag, $data, $optionalArgs = null)
  {
    if(!isset(self::$filters[$tag]))
    {
      return $data;
    }

    $args = func_get_args();
    // remove first parameter
    array_shift($args);
    
    // sort by priority
    krsort(self::$filters[$tag]);

    foreach(self::$filters[$tag] as $priority => $phooks)
    {
      foreach($phooks as $hook)
      {
        // is the method available?
        if(!is_callable($hook))
        {
          throw new Exception(sprintf('{sfCore} "%s" is not callable.', var_export($hook, true)));
        }
        // call the method
        $args[1] = call_user_func_array($hook, $args);
      }
    }
    return $args[1];
  }

  /**
   * Bootstrap plugin configurations
   *
   * @return unknown_type
   */
  public static function loadPluginConfig()
  {
    // load plugin configurations !
    if($pluginConfigs = glob(sfConfig::get('sf_plugins_dir').'/*/config/config.php'))
    {
      foreach($pluginConfigs as $config)
      {
        include $config;
      }
    }
  }

  /**
   * Returns event dispatcher
   *
   * @return sfEventDispatcher
   */
  public static function getEventDispatcher()
  {
    if(!self::$dispatcher)
    {
      self::$dispatcher = new sfEventDispatcher();
    }
    return self::$dispatcher;
  }

  /**
   * Filters variable using event dispatcher. Dispatches
   * an event with given name and parameters passed.
   * Returns modified (is any listener touched it) value
   *
   * @param mixed $value
   * @param string $eventName
   * @param array $params Params for the event
   */
  public static function filterByEventListeners(&$value, $eventName,
          $params = array())
  {
    $event = new sfEvent($eventName, $params);
    self::getEventDispatcher()->filter($event, $value);
    return $event->getReturnValue();
  }

  /**
   * Enables modules given by configuration ('config/SF_APP/modules.yml')
   * 
   */
  public static function enableModules($app = SF_APP)
  {
    try 
    {    
      sfConfigCache::getInstance()->import(sprintf('config/%s/modules.yml', $app), true, true);
    }
    catch(sfConfigurationException $e)
    {
    }
  }

  /**
   * Dispatches an event using the event system
   *
   * @param string $name event_namespace.event_name
   * @param array $data associative array of data
   */
  public static function dispatchEvent($name, $data = array())
  {
    return self::getEventDispatcher()->notify(new sfEvent($name, $data));
  }

  /**
   * Compare the specified version string $version
   * with the current version
   *
   * @param  string  $version  A version string (e.g. "0.7.1").
   * @return boolean           -1 if the $version is older,
   *                           0 if they are the same,
   *                           and +1 if $version is newer.
   *
   */
  public static function compareVersion($version)
  {    
    return version_compare($version, self::getVersion());
  }

  /**
   * Returns core helpers array ('Helper', 'Url', 'Asset', 'Tag', 'Escaping')
   *
   * @return array
   */
  public static function getCoreHelpers()
  {
    return array('Helper', 'Url', 'Asset', 'Tag', 'Escaping');
  }
  
  /**
   * Returns current Sift version
   * 
   * @return string
   */
  public static function getVersion()
  {
    return trim(file_get_contents(sfConfig::get('sf_sift_lib_dir').'/VERSION'));
  }

}

