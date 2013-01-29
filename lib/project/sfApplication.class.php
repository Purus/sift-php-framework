<?php
/*
 * This file is part of the Sift PHP framework.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * sfApplication represents an application
 *
 * @package    Sift
 * @subpackage project
 */
abstract class sfApplication extends sfProject {

  protected $name;
  protected $debug = false;

  /**
   * Array of content filters
   * 
   * @var array 
   */
  protected $filters = array();
    
  /**
   * Array of available application dimensions
   * 
   * @var array 
   * @link https://bitbucket.org/mishal/sift-php-framework/wiki/Dimensions
   */
  protected $applicationDimensions = array();

  /**
   * Instance of config cache
   * 
   * @var sfConfigCache 
   */
  protected $configCache;

  /**
   * Dimensions
   * 
   * @var sfDimensions 
   */
  protected $dimensions;

  /**
   * Constructs the application
   * 
   * @param string $environment Environment (prod, dev, cli, staging)
   * @param boolean $debug Turn on debugging features?
   * @param array $options Array of options
   * @param sfEventDispatcher $dispatcher Event dispatcher object
   */
  public function __construct($environment, $debug = false, $options = array(), sfEventDispatcher $dispatcher = null)
  {
    parent::__construct($options, $dispatcher);

    // get application name from the classname
    preg_match('/(my|sf)+(.*)Application/', get_class($this), $matches);

    if(isset($matches[2]))
    {
      $this->name = sfInflector::tableize($matches[2]);
    }
    else
    {
      $this->name = get_class($this);
    }
   
    $this->debug = $debug;
    $this->environment = $environment;

    // initialize core configuration
    $this->initCoreOptions();

    // initialize config cache
    $this->initConfigCache();
    
    $this->initializeAutoload();
    
    // init include path
    $this->initIncludePath();
    
    // load all available dimensins
    $this->loadDimensions();

    // initialize current dimension
    $this->initCurrentDimension();
    
    // configures the application
    $this->configure();

    // initialize options
    $this->initOptions();
    
    // initialize autoload for the application

    $this->initConfiguration();

    $this->initialize();
    
    $this->activate();
  }

  /**
   * Configures the current configuration.
   *
   * Override this method if you want to customize your application configuration.
   */
  public function configure()
  {    
  }

  /**
   * Initialized the current application.
   *
   * Override this method if you want to customize your application initialization.
   */
  public function initialize()
  {    
  }
  
  /**
   * Easly stage dimension initialization. Beware that this is called before 
   * the sfContext gets created, so there is no access to internal objects.  
   */
  public function initCurrentDimension()
  {
  }

  protected function initConfiguration()
  {
    // load base settings
    include($this->configCache->checkConfig($this->getOption('sf_app_config_dir_name') . '/settings.yml'));
    include($this->configCache->checkConfig($this->getOption('sf_app_config_dir_name') . '/logging.yml'));

    // check locks
    if(sfConfig::get('sf_check_lock'))
    {
      $this->checkLock();
    }
    
    if(sfConfig::get('sf_check_sift_version'))
    {
      $this->checkSiftVersion();
    }
    
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

    // force setting default timezone if not set
    if(function_exists('date_default_timezone_get'))
    {
      date_default_timezone_set(@date_default_timezone_get());
    }

    // get config instance
    $sf_app_config_dir_name = sfConfig::get('sf_app_config_dir_name');
    $sf_debug = sfConfig::get('sf_debug');
        
    // load base settings
    include($this->configCache->checkConfig($sf_app_config_dir_name.'/settings.yml'));
    if($file = $this->configCache->checkConfig($sf_app_config_dir_name.'/app.yml', true))
    {
      include($file);
    }

    if(false !== sfConfig::get('sf_csrf_secret'))
    {
      sfForm::enableCSRFProtection(sfConfig::get('sf_csrf_secret'));
    }
    
    if(sfConfig::get('sf_i18n'))
    {
      $i18nConfig = include($this->configCache->checkConfig($sf_app_config_dir_name . '/i18n.yml'));

      $this->addOptions($i18nConfig);
      sfConfig::add($i18nConfig);

      if(!function_exists('__'))
      {
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
      
    }
    else
    {

      if(!function_exists('__'))
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
      

    }

    // add autoloading callables
    foreach((array) sfConfig::get('sf_autoloading_functions', array()) as $callable)
    {
      spl_autoload_register($callable);
    }

    // error settings
    ini_set('display_errors', $sf_debug ? 'on' : 'off');
    error_reporting(sfConfig::get('sf_error_reporting'));

    // required core classes for the framework   
    if(!$sf_debug && !sfConfig::get('sf_test'))
    {
      // $core_classes = $sf_app_config_dir_name . '/core_compile.yml';
      // $this->configCache->import($core_classes, false);
    }

    $this->configCache->import($sf_app_config_dir_name . '/php.yml', false);
    $this->configCache->import($sf_app_config_dir_name . '/routing.yml', false);

    // setup generation of html tags (Xhtml vs HTML)
    $this->initHtmlTagConfiguration();
    // include all config.php from plugins
    $this->loadPluginConfig();

    // import text macros configuration
    $this->configCache->import(sprintf($sf_app_config_dir_name . '/%s/modules.yml', sfConfig::get('sf_app')), true, true);

    // import text macros configuration
    include($this->configCache->checkConfig($sf_app_config_dir_name . '/text_macros.yml'));

    // load asset packages
    include($this->configCache->checkConfig($sf_app_config_dir_name . '/asset_packages.yml'));

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

  protected function initIncludePath()
  {
    set_include_path(
      $this->getOption('sf_lib_dir').PATH_SEPARATOR.
      $this->getOption('sf_root_dir').PATH_SEPARATOR.      
      $this->getOption('sf_sift_lib_dir').DIRECTORY_SEPARATOR.'vendor'.PATH_SEPARATOR.
      get_include_path()
    );  
  }
  
  public function callBootstrap()
  {    
    $bootstrap = $this->getOption('sf_config_cache_dir').'/config_bootstrap_compile.yml.php';
    if(is_readable($bootstrap))
    {
      sfConfig::set('sf_in_bootstrap', true);
      require($bootstrap);
    }
    else
    {
      // require($this->getOption('sf_sift_lib_dir').'/sift.php');
    }
  }
  
  /**
   * Initializes core options. Exports those options to sfConfig class.
   * 
   * @return void
   */
  protected function initCoreOptions()
  {
    $coreOptions = array(
      'sf_debug' => $this->isDebug(),
      'sf_environment' => $this->getEnvironment(),
      'sf_base_cache_dir' => $this->getOption('sf_root_cache_dir') . DS . $this->getOption('sf_app'),
      'sf_app_dir' => $sf_app_dir = ($this->getOption('sf_apps_dir') . DS . $this->getOption('sf_app')),
      'sf_app_config_dir' => $sf_app_dir . DS . $this->getOption('sf_app_config_dir_name'),
      'sf_app_lib_dir' => $sf_app_dir . DS . $this->getOption('sf_app_lib_dir_name'),
      'sf_app_module_dir' => $sf_app_dir . DS . $this->getOption('sf_app_module_dir_name'),
      'sf_app_template_dir' => $sf_app_dir . DS . $this->getOption('sf_app_template_dir_name'),
      'sf_app_i18n_dir' => $sf_app_dir . DS . $this->getOption('sf_app_i18n_dir_name'),
    );

    $this->addOptions($coreOptions);

    // export to sfConfig
    sfConfig::add($this->getOptions());
  }

  /**
   * Loads dimensions from dimensions.yml file
   * 
   */
  protected function loadDimensions()
  {
    $this->dimensions = new sfDimensions($this->applicationDimensions);

    $dimensions = array(
        'sf_dimension' => $this->getDimensions()->getCurrentDimension(),
        // stores the dimension directories that sift will search through
        'sf_dimension_dirs' => $this->getDimensions()->getDimensionDirs()
    );

    $this->addOptions($dimensions);
    sfConfig::add($dimensions);
  }

  protected function initOptions()
  {
    $dimension_string = $this->getDimensions()->getDimensionString();
    // create configuration    
    $this->addOptions(array(
        'sf_dimension' => $this->getDimensions()->getCurrentDimension(),
        // stores the dimension directories that sift will search through
        'sf_dimension_dirs' => $this->getDimensions()->getDimensionDirs(),
        'sf_cache_dir' => $sf_cache_dir = $this->getOption('sf_base_cache_dir') . DS . $this->getEnvironment() .
        ($dimension_string ? DS . $dimension_string : ''),
        'sf_template_cache_dir' => $sf_cache_dir . DS . 'template',
        'sf_i18n_cache_dir' => $sf_cache_dir . DS . 'i18n',
        'sf_config_cache_dir' => $sf_cache_dir . DS . $this->getOption('sf_config_dir_name'),
        'sf_test_cache_dir' => $sf_cache_dir . DS . 'test',
        'sf_module_cache_dir' => $sf_cache_dir . DS . 'modules',
        // SF_APP_DIR directory structure
        'sf_cache_dir' => $this->getOption('sf_base_cache_dir') . DS . $this->getEnvironment() . ($dimension_string ? DS . $dimension_string : ''),
    ));

    sfConfig::add($this->getOptions());
  }
  
  /**
   * Add a filter call back. Tell sfCore that a filter is to be run on a filter
   * at a certain point.
   *
   * $tag          string  Name of the filter to hook.
   * $function     string  Callable function to be run on the hoook
   * $priority     integer Priority of this filter, default is 10 (higher value, higher priority)
   */
  public function addFilter($tag, $function, $priority = 10)
  {
    if(!isset($this->filters[$tag]))
    {
      $this->filters[$tag] = array();
    }

    if(!isset($this->filters[$tag][$priority]))
    {
      $this->filters[$tag][$priority] = array();
    }

    $this->filters[$tag][$priority][serialize($function)] = $function;
  }
  
  /**
   * Remove a filter added previously. Called with the same arguments as addfilter
   *
   * $tag          string  Name of the filter to remove.
   * $method       string  Name of method to remove
   * $class        string  Name of the class providing the function
   * $priority     integer Prority of this filter, default is 10
   */
  public function removeFilter($tag, $function, $priority = 10)
  {
    if(isset($this->filters[$tag][$priority][serialize($function)]))
    {
      unset($this->filters[$tag][$priority][serialize($function)]);
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
  public function applyFilters($tag, $data, $optionalArgs = null)
  {
    if(!isset($this->filters[$tag]))
    {
      return $data;
    }

    $args = func_get_args();
    // remove first parameter
    array_shift($args);    
    // sort by priority
    krsort($this->filters[$tag]);
    
    foreach($this->filters[$tag] as $priority => $phooks)
    {
      foreach($phooks as $hook)
      {
        // is the method available?
        if(!is_callable($hook))
        {
          throw new Exception(sprintf('{sfApplication} "%s" is not callable.', var_export($hook, true)));
        }
        // call the method
        $args[1] = call_user_func_array($hook, $args);
      }
    }
    return $args[1];
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
  public function checkLock()
  {
    if(sfToolkit::hasLockFile($this->getOption('sf_data_dir').DIRECTORY_SEPARATOR.$this->getOption('sf_app').'_'.$this->getEnvironment().'-cli.lck', 5)
      ||
      sfToolkit::hasLockFile($this->getOption('sf_data_dir').DIRECTORY_SEPARATOR.$this->getOption('sf_app').'_'.$this->getEnvironment().'.lck'))
    {
      // application is not available - we'll find the most specific unavailable page...
      $files = array(
        $this->getOption('sf_app_config_dir').'/unavailable.php',
        $this->getOption('sf_config_dir').'/unavailable.php',
        $this->getOption('sf_web_dir').'/errors/unavailable.php',
        $this->getOption('sf_sift_data_dir').'/errors/unavailable.php',
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
   * Displays error page 
   * 
   * @param sfException $exception
   * @param string $error
   */
  public function displayErrorPage(sfException $exception, $error = 'error500')
  {
    $files = array(
      sprintf($this->getOption('sf_app_config_dir').'/%s.php', $error),  
      sprintf($this->getOption('sf_config_dir').'/%s.php', $error),
      $this->getOption('sf_web_dir').'/errors/error500.php',
      $this->getOption('sf_sift_data_dir').'/errors/error500.php'
    );
    foreach($files as $file)
    {
      if(is_readable($file))
      {
        include $file;
        break;
      }
    }
    if(!$this->getOption('sf_test'))
    {
      exit(1);
    }   
  }
  
  /**
   * Checks if Sift has been currently updated. If yes, clears the cache directory.
   * 
   */
  public function checkSiftVersion()
  {
    // recent Sift update?
    $last_version    = @file_get_contents(sfConfig::get('sf_config_cache_dir').'/VERSION');
    $current_version = sfCore::getVersion();
    if($last_version != $current_version)
    {
      // clear cache
      sfToolkit::clearDirectory(sfConfig::get('sf_cache_dir'));
    }
  }
  
  /**
   * Active the application
   * 
   */
  public function activate()
  {
  }

  /**
   * Returns application name
   * 
   * @return string The application name
   */
  public function getName()
  {
    return $this->name;
  }

  /**
   * Returns the environment name.
   *
   * @return string The environment name
   */
  public function getEnvironment()
  {
    return $this->environment;
  }

  /**
   * Returns true if this configuration has debug enabled.
   *
   * @return Boolean true if the configuration has debug enabled, false otherwise
   */
  public function isDebug()
  {
    return $this->debug;
  }

  /**
   * Returns sfDimensions object
   * 
   * @return sfDimensions
   * @throws RuntimeException If dimensions are not loaded
   */
  public function getDimensions()
  {
    if(is_null($this->dimensions))
    {
      throw new RuntimeException('Dimensions are not loaded.');
    }
    return $this->dimensions;
  }
  
  public function __toString()
  {
    return $this->name;
  }

}
