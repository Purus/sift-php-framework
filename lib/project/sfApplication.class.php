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

  /**
   * Debug environment?
   *
   * @var boolean
   */
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
   * Array of default options
   *
   * @var array
   */
  protected $defaultOptions = array(
    'sf_plugins' => array()
  );

  /**
   * Form enhancer object
   *
   * @var sfFormEnhancer
   */
  protected $formEnhancer;

  /**
   * Constructs the application
   *
   * @param string $environment Environment (prod, dev, cli, staging)
   * @param boolean $debug Turn on debugging features?
   * @param array $options Array of options
   * @param sfEventDispatcher $dispatcher Event dispatcher object
   * @param sfShutdownScheduler $shutdownScheduler Shutdown scheduler
   */
  public function __construct($environment, $debug = false, $options = array(),
      sfEventDispatcher $dispatcher = null, sfShutdownScheduler $shutdownScheduler = null)
  {
    parent::__construct($options, $dispatcher, $shutdownScheduler);

    $this->debug = $debug;
    $this->environment = $environment;

    // initialize core configuration
    $this->initCoreOptions();
    // load all available dimensions
    $this->loadDimensions();
    // initialize current dimension
    $this->initCurrentDimension();
    // initialize options
    $this->initOptions();
    // load plugins
    $this->loadPlugins();
    // initialize config cache
    $this->initConfigCache();
    // initialize autoloading
    $this->initializeAutoload();
    // init include path
    $this->initIncludePath();
    // configures the application
    $this->configure();
    // setup plugins
    $this->setupPlugins();
    // init configuration
    $this->initConfiguration();
    // initialize the application
    $this->initialize();
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

  /**
   * Initializes configuration
   *
   */
  protected function initConfiguration()
  {
    $this->configCache->import($this->getOption('sf_app_config_dir_name') . '/settings.yml', false);

    // detect relative url root, before setting up the request
    if(!$this->hasOption('sf_relative_url_root'))
    {
      $this->setOption('sf_relative_url_root', $this->detectRelativeUrlRoot());
    }

    require ($this->configCache->checkConfig($this->getOption('sf_app_config_dir_name') . '/logging.yml', false));
    $this->configCache->import($this->getOption('sf_app_config_dir_name') . '/php.yml', false);

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

    $this->getShutdownScheduler()->register(array('sfPhpErrorException', 'fatalErrorShutdownHandler'), array(), sfShutdownScheduler::LOW_PRIORITY);

    // get config instance
    $sf_app_config_dir_name = sfConfig::get('sf_app_config_dir_name');
    $sf_debug = sfConfig::get('sf_debug');

    // load base settings
    if($file = $this->configCache->checkConfig($sf_app_config_dir_name.'/app.yml', true))
    {
      include($file);
    }

    if(false !== sfConfig::get('sf_csrf_secret'))
    {
      sfForm::enableCSRFProtection(sfConfig::get('sf_csrf_secret'));
    }

    // provide the dispatcher to the forms
    sfForm::setEventDispatcher($this->getEventDispatcher());

    if(sfConfig::get('sf_i18n'))
    {
      $i18nConfig = include($this->configCache->checkConfig($sf_app_config_dir_name . '/i18n.yml'));

      $this->addOptions($i18nConfig);

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
          return sfContext::getInstance()->getI18N()->__($text, $args, $catalogue);
        }
      }

      // add translation callable to the forms
      sfWidgetFormSchemaFormatter::setTranslationCallable('__');
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

    // set default culture
    $this->setOption('sf_culture', $this->getOption('sf_i18n_default_culture', 'en'));
    // add the culture in the routing default parameters
    $this->setOption('sf_routing_defaults', array(
      'sf_culture' => $this->getOption('sf_i18n_default_culture', 'en')
    ));

    // add autoloading callables
    foreach((array) sfConfig::get('sf_autoloading_functions', array()) as $callable)
    {
      spl_autoload_register($callable);
    }

    error_reporting(sfConfig::get('sf_error_reporting'));

    // required core classes for the framework
    if(!$sf_debug && !sfConfig::get('sf_test'))
    {
      $this->configCache->import($sf_app_config_dir_name . '/core_compile.yml', false);
    }

    $this->configCache->import($sf_app_config_dir_name . '/routing.yml', false);

    // setup generation of html tags (Xhtml vs HTML)
    $this->initHtmlTagConfiguration();

    // initialize plugins
    $this->initializePlugins();

    // import modudes.yml for current application
    $this->configCache->import(sprintf($sf_app_config_dir_name . '/%s/modules.yml', sfConfig::get('sf_app')), true, true);

    // load asset packages
    include($this->configCache->checkConfig($sf_app_config_dir_name . '/asset_packages.yml'));

    // setup form enhancer
    if($enhancer = $this->getFormEnhancer())
    {
      $this->getEventDispatcher()->connect('view.template.variables', array($enhancer,
          'filterTemplateVariables'));
    }

    if(sfConfig::get('sf_environment') != 'cli' || php_sapi_name() != 'cli')
    {
      // start output buffering
      ob_start();
    }
  }

  /**
   * Initializes plugin configuration objects.
   *
   */
  protected function initializePlugins()
  {
    foreach($this->plugins as $name => $plugin)
    {
      if(false === $plugin->initialize() &&
        is_readable($config = $plugin->getRootDir(). DS .
        $this->getOption('sf_app_config_dir_name') . DS . 'config.php'))
      {
        require $config;
      }
    }
  }

  /**
   * Calls bootstrap
   *
   */
  public function callBootstrap()
  {
    $bootstrap = $this->getOption('sf_config_cache_dir').'/config_bootstrap_compile.yml.php';
    if(is_readable($bootstrap))
    {
      $this->setOption('sf_in_bootstrap', true);
      require($bootstrap);
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
  }

  /**
   * Loads dimensions from dimensions.yml file. Refreshes in debug environment
   * if the dimensions.yml file has changed after last compilation time.
   *
   */
  protected function loadDimensions()
  {
    // dimensions are application specific
    // sits in the root
    $cacheFile = $this->getOption('sf_root_cache_dir') . DS
                 . $this->getName() . DS . 'config_dimensions.yml.php';

    $file = $this->getOption('sf_app_config_dir').DS.'dimensions.yml';

    $compile = false;
    // create the file if it does not exits
    if(is_readable($cacheFile))
    {
      if($this->isDebug() && filemtime($file) > filemtime($cacheFile))
      {
        $compile = true;
      }
    }
    else // cache does not exist
    {
      $compile = true;
    }

    if($compile)
    {
      $pluginHandler = new sfDimensionsConfigHandler();
      // application wide setting
      $result = $pluginHandler->execute(array($file));
      // put to cache
      sfConfigCache::writeCacheFile($cacheFile, $result);
    }

    // load cache
    include $cacheFile;

    $dimensions = array(
      'sf_dimension' => $this->getDimensions()->getCurrentDimension(),
      // stores the dimension directories that sift will search through
      'sf_dimension_dirs' => $this->getDimensions()->getDimensionDirs()
    );

    $this->addOptions($dimensions);
  }

  /**
   * Returns form enhancer
   *
   * @return sfFormEnhancer|false False when form enhancer is disabled
   */
  public function getFormEnhancer()
  {
    if($this->formEnhancer === null)
    {
      $config = include $this->configCache->checkConfig('config/forms.yml');

      // form enhancer is disabled
      if(isset($config['enhancer']['enabled'])
          && !$config['enhancer']['enabled'])
      {
        $this->formEnhancer = false;
      }
      else
      {
        $class = 'myFormEnhancer';
        if(isset($config['enhancer']['class']))
        {
          $class = $config['enhancer']['class'];
        }
        $this->formEnhancer = sfFormEnhancer::factory($class, $config);
      }
    }
    return $this->formEnhancer;
  }

  /**
   * Returns current application dimension
   *
   * @return array
   */
  public function getCurrentDimension()
  {
    return $this->getDimensions()->getCurrentDimension();
  }

  /**
   * Sets current dimension
   *
   * @param array $dimension
   */
  public function setCurrentDimension(array $dimension)
  {
    $this->getDimensions()->setCurrentDimension($dimension);

    // add to current configuration
    $dimensions = array(
      'sf_dimension' => $this->getDimensions()->getCurrentDimension(),
      // stores the dimension directories that sift will search through
      'sf_dimension_dirs' => $this->getDimensions()->getDimensionDirs()
    );

    // this will flush the values to sfConfig
    $this->addOptions($dimensions);
  }

  /**
   * Returns an array of available dimensions
   *
   * @return array
   */
  public function getAvailableDimensions()
  {
    return $this->getDimensions()->getAvailableDimensions();
  }

  /**
   * Initialize options
   *
   */
  protected function initOptions()
  {
    $dimension_string = $this->getDimensions()->getDimensionString();
    // create configuration
    $this->addOptions(array(
        'sf_dimension' => $this->getDimensions()->getCurrentDimension(),
        // stores the dimension directories that sift will search through
        'sf_dimension_dirs' => $this->getDimensions()->getDimensionDirs(),
        // SF_APP_DIR directory structure
        'sf_cache_dir' => $sf_cache_dir = ($this->getOption('sf_base_cache_dir') . DS . $this->getEnvironment() . ($dimension_string ? DS . $dimension_string : '')),
        'sf_template_cache_dir' => $sf_cache_dir . DS . 'template',
        'sf_i18n_cache_dir' => $sf_cache_dir . DS . 'i18n',
        'sf_config_cache_dir' => $sf_cache_dir . DS . $this->getOption('sf_config_dir_name'),
        'sf_test_cache_dir' => $sf_cache_dir . DS . 'test',
        'sf_module_cache_dir' => $sf_cache_dir . DS . 'modules',
    ));
  }

  /**
   * Detects the relative url root from the script name
   *
   * @return string The url root
   * @throws sfConfigurationException
   */
  protected function detectRelativeUrlRoot()
  {
    $pathInfo = sfConfig::get('sf_path_info_array');
    switch($pathInfo)
    {
      case 'SERVER':
        $scriptName = $_SERVER['SCRIPT_NAME'];
      break;

      case 'ENV':
        $scriptName = $_ENV['SCRIPT_NAME'];
      break;

      default:
        throw new sfConfigurationException(sprintf('Invalid configuration value "%s" for "sf_path_info_array". Valid values are "SERVER" or "ENV"', $pathInfo));
    }
    return preg_replace('#/[^/]+\.php5?$#', '', $scriptName);
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
    sfConfig::clear();
    sfConfig::add($this->getOptions());
  }

  /**
   * Returns application name
   *
   * @return string The application name
   */
  public function getName()
  {
    return $this->getOption('sf_app');
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
   * Returns an array of core helpers
   *
   * @return array
   */
  public function getCoreHelpers()
  {
    $helpers = parent::getCoreHelpers();
    if($this->isDebug())
    {
      $helpers[] = 'Debug';
    }
    return $helpers;
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
      throw new RuntimeException('Application dimensions are not loaded yet.');
    }
    return $this->dimensions;
  }

  /**
   * Magic __toString() method
   *
   * @return string
   */
  public function __toString()
  {
    return $this->getName();
  }

}
