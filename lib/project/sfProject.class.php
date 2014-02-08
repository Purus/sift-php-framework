<?php
/*
 * This file is part of the Sift PHP framework.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * sfProject represents the whole project
 *
 * @package Sift
 * @subpackage project
 */
abstract class sfProject extends sfConfigurable
{
  /**
   * Array of initialized plugins
   *
   */
  protected $plugins = array();

  /**
   * Default options
   *
   * @var array
   */
  protected $defaultOptions = array(
    'sf_data_dir_name' => 'data',
    'sf_cache_dir_name' => 'cache',
    'sf_config_dir_name' => 'config',
    'sf_apps_dir_name' => 'apps',
    'sf_test_dir_name' => 'test',
    'sf_doc_dir_name' => 'doc',
    'sf_bin_dir_name' => 'batch',
    'sf_log_dir_name' => 'log',
    'sf_lib_dir_name' => 'lib',
    'sf_plugins_dir_name' => 'plugins',
    'sf_web_dir_name' => 'web',
    'sf_model_dir_name' => 'model',
    'sf_upload_dir_name' => 'files',
    'sf_i18n_dir_name' => 'i18n',

    // application wide configuration
    'sf_app_i18n_dir_name' => 'i18n',
    'sf_app_config_dir_name' => 'config',
    'sf_app_lib_dir_name' => 'lib',
    'sf_app_module_dir_name' => 'modules',
    'sf_app_template_dir_name' => 'templates',

    // module wide configuration
    'sf_app_module_action_dir_name' => 'actions',
    'sf_app_module_template_dir_name' => 'templates',
    'sf_app_module_lib_dir_name' => 'lib',
    'sf_app_module_view_dir_name' => 'views',
    'sf_app_module_config_dir_name' => 'config',
    'sf_app_module_i18n_dir_name' => 'i18n',
  );

  /**
   * Event dispatcher object
   *
   * @var sfEventDispatcher
   */
  protected $dispatcher;

  /**
   * Shutdown scheduler
   *
   * @var sfShutdownScheduler
   */
  protected $shutdownScheduler;

  /**
   * Array of required options
   *
   * @var array
   */
  protected $requiredOptions = array(
    'sf_sift_lib_dir', 'sf_sift_data_dir', 'sf_root_dir',
  );

  /**
   * Have plugins been already loaded?
   *
   * @var boolean
   */
  protected $pluginsLoaded = false;

  /**
   * Active application
   *
   * @var sfApplication
   */
  protected static $active = null;

  /**
   * Constructor.
   *
   * @param array $options The options
   * @param sfEventDispatcher $dispatcher The event dispatcher
   * @param sfShutdownScheduler $shutdownScheduler The shutdown scheduler
   */
  public function __construct($options = array(), sfEventDispatcher $dispatcher = null, sfShutdownScheduler $shutdownScheduler = null)
  {
    $this->dispatcher = is_null($dispatcher) ? new sfEventDispatcher() : $dispatcher;
    $this->shutdownScheduler = is_null($shutdownScheduler) ? new sfShutdownScheduler() : $shutdownScheduler;

    parent::__construct($options);

    if (null === self::$active && $this instanceof sfApplication) {
      self::$active = $this;
    }

    // register the shutdown
    $this->shutdownScheduler->register(array($this, 'shutdown'));

    $this->configure();
  }

  /**
   * Configures the current project
   *
   * Override this method if you want to customize your project.
   */
  public function configure()
  {
  }

  /**
   * Setups the project
   *
   */
  public function setup()
  {
    if (!$this->getOption('sf_apps_dir')) {
      $this->setOption('sf_apps_dir', $this->getOption('sf_root_dir').DS.$this->getOption('sf_apps_dir_name'));
    }

    if (!$this->getOption('sf_config_dir')) {
      $this->setOption('sf_config_dir', $this->getOption('sf_root_dir').DS.$this->getOption('sf_config_dir_name'));
    }

    if (!$this->getOption('sf_lib_dir')) {
      $this->setOption('sf_lib_dir', $this->getOption('sf_root_dir').DS.$this->getOption('sf_lib_dir_name'));
    }

    if (!$this->getOption('sf_bin_dir')) {
      $this->setOption('sf_bin_dir', $this->getOption('sf_root_dir').DS.$this->getOption('sf_bin_dir_name'));
    }

    if (!$this->getOption('sf_log_dir')) {
      $this->setOption('sf_log_dir', $this->getOption('sf_root_dir').DS.$this->getOption('sf_log_dir_name'));
    }

    if (!$this->getOption('sf_model_lib_dir')) {
      $this->setOption('sf_model_lib_dir', $this->getOption('sf_lib_dir').DS.$this->getOption('sf_model_dir_name'));
    }

    if (!$this->getOption('sf_image_font_dir')) {
      $this->setOption('sf_image_font_dir', $this->getOption('sf_root_dir').DS.$this->getOption('sf_data_dir_name') . DS . 'fonts');
    }

    $sf_root_dir = $this->getOption('sf_root_dir');

    $this->addOptions(array(
      'sf_web_dir' => $sf_root_dir . DS . $this->getOption('sf_web_dir_name'),
      'sf_upload_dir' => $sf_root_dir . DS . $this->getOption('sf_web_dir_name') . DS . $this->getOption('sf_upload_dir_name'),
      'sf_root_cache_dir' => $this->getOption('sf_root_dir') . DS . $this->getOption('sf_cache_dir_name'),
      'sf_log_dir' => $sf_root_dir . DS . $this->getOption('sf_log_dir_name'),
      'sf_data_dir' => $sf_root_dir . DS . $this->getOption('sf_data_dir_name'),
      'sf_config_dir' => $sf_root_dir . DS . $this->getOption('sf_config_dir_name'),
      'sf_test_dir' => $sf_root_dir . DS . $this->getOption('sf_test_dir_name'),
      'sf_doc_dir' => $sf_root_dir . DS . $this->getOption('sf_doc_dir_name'),
      'sf_plugins_dir' => $sf_root_dir . DS . $this->getOption('sf_plugins_dir_name'),
      // image font directory
      'sf_image_font_dir' => $this->getOption('sf_root_dir') . DS . $this->getOption('sf_data_dir_name') . DS . 'fonts',
    ));
  }

  /**
   * Sets option. Options are automatically exported to sfConfig
   *
   * @param string $name
   * @param mixed $value
   * @return sfProject
   */
  public function setOption($name, $value)
  {
    parent::setOption($name, $value);
    sfConfig::set($name, $value);

    return $this;
  }

  /**
   * Sets options. Options are automatically exported to sfConfig
   *
   * @param array $options
   * @return sfProject
   */
  public function setOptions($options)
  {
    parent::setOptions($options);
    // clear
    sfConfig::clear();
    sfConfig::add($this->getOptions());

    return $this;
  }

  /**
   * Adds options. Options are automatically exported to sfConfig
   *
   * @param array $options
   * @return sfProject
   */
  public function addOptions($options)
  {
    parent::addOptions($options);
    sfConfig::add($this->getOptions());

    return $this;
  }

  /**
   * Initializes the autoloading feature
   *
   * @param boolean $reload Force the reload?
   */
  public function initializeAutoload($reload = false)
  {
    if ($this instanceof sfApplication) {
      // environment specific autoload
      $cacheFile = $this->getOption('sf_base_cache_dir') . DS .
              $this->getOption('sf_environment') . DS . 'autoload.cache';
    } else {
      if (!$cacheDir = $this->getOption('sf_root_cache_dir')) {
        throw new sfInitializationException('Cannot initialize autoload. Missing "sf_root_cache_dir" setting.');
      }

      // project wide autoloading
      $cacheFile = $cacheDir.'/project_autoload.cache';
    }

    // force the reload
    // clear the cache
    if ($reload && is_readable($cacheFile)) {
      $finder = sfFinder::type('file')->name('*autoload.cache');

      if ($this instanceof sfApplication) {
        $where = $this->getOption('sf_base_cache_dir');
      } else {
        $where = $this->getOption('sf_root_cache_dir');
      }

      $filesystem = new sfFilesystem();
      $filesystem->remove($finder->in($where));
    }

    $autoload = sfSimpleAutoload::getInstance($cacheFile);

    if (!is_readable($cacheFile)) {
      // Sift
      $files = array(
        $this->getOption('sf_sift_data_dir') . '/config/autoload.yml'
      );

      // project
      if(is_readable(
        $file = sprintf('%s/%s/autoload.yml', $this->getOption('sf_root_dir'), $this->getOption('sf_config_dir_name'))))
      {
        $files[] = $file;
      }

      // plugins
      foreach ($this->getPlugins() as $plugin) {
        // load from plugins
        if (is_readable($file = $plugin->getRootDir().'/'.$this->getOption('sf_config_dir_name').'/autoload.yml')) {
          $files[] = $file;
        }
      }

      $autoload->loadConfiguration($files);
      $autoload->saveCache(true);
    }

    // switch the order of autoloaders, lets core be after the simple autoload
    sfCoreAutoload::unregister();

    $autoload->register();

     // register again as second autoloader
    sfCoreAutoload::register();
  }

  /**
   * Sets up plugins
   *
   * Override this method if you want to customize plugin behaviors.
   */
  public function setupPlugins()
  {
  }

  /**
   * Initialize config cache
   *
   */
  protected function initConfigCache()
  {
    // create new config cache instance
    $this->configCache = sfConfigCache::getInstance($this);
  }

  /**
   * Returns the configuration cache instance
   *
   * @return sfConfigCache
   */
  protected function getConfigCache()
  {
    return $this->configCache;
  }

  /**
   * Returns the dispatcher instance
   *
   * @return sfEventDispatcher
   */
  public function getEventDispatcher()
  {
    return $this->dispatcher;
  }

  /**
   * Returns the shutdown scheduler instance
   *
   * @return sfShutdownScheduler
   */
  public function getShutdownScheduler()
  {
    return $this->shutdownScheduler;
  }

  /**
   * Return an array of core helpers
   *
   * @return array
   */
  public function getCoreHelpers()
  {
    return array('Helper', 'Url', 'Asset', 'Tag', 'Escaping');
  }

  /**
   * Shutdown method. Calls shutdown() for each active plugin
   *
   */
  public function shutdown()
  {
    foreach ($this->getPlugins() as $plugin) {
      $plugin->shutdown();
    }
  }

  /**
   * Returns sfApplication instance
   *
   * @param string $application The application name
   * @param string $environment The environment
   * @param boolean $debug Turn on debug features?
   * @param boolea $forceReload Force reloading of the application?
   * @return sfApplication
   * @throws RuntimeException
   */
  public function getApplication($application, $environment, $debug = false, $forceReload = false)
  {
    if (!isset($this->applications[$application]) || $forceReload) {
      $class = sprintf('my%sApplication', sfInflector::camelize($application));
      if (!class_exists($class, false)) {
        $appFile = sprintf('%s/%s/%s/%s.class.php', $this->getOption('sf_apps_dir'),
                           $application, $this->getOption('sf_lib_dir_name'),
                           $class);
        if (is_readable($appFile)) {
          require_once $appFile;
          if (!class_exists($class, false)) {
            throw new RuntimeException(sprintf('The application "%s" does not exist.', $application));
          }
        } else {
          $class = 'sfGenericApplication';
        }
      }

      if (!is_dir($this->getOption('sf_apps_dir') . '/' . $application)) {
        throw new sfConfigurationException(sprintf('Application "%s" does not exist in the app directory "%s"',
                  $application, $this->getOption('sf_apps_dir') . '/' . $application));
      }

      $this->applications[$application] = new $class($environment, $debug,
              array_merge($this->getOptions(), array(
                  // required options
                  'sf_app' => $application,
                  'sf_app_dir' => $this->getOption('sf_apps_dir') . '/' . $application
              )),
              $this->getEventDispatcher(),
              $this->getShutdownScheduler());
    }

    return $this->applications[$application];
  }

  /**
   * Returns active application
   *
   * @return type
   * @throws RuntimeException
   */
  public function getActiveApplication()
  {
    if (!$this->hasActive()) {
      throw new RuntimeException('There is no active application.');
    }

    return self::$active;
  }

  /**
   * Returns true if these is an active configuration.
   *
   * @return boolean
   */
  public function hasActive()
  {
    return null !== self::$active;
  }

  /**
   * Returns an array of plugins
   *
   * @return array
   */
  public function getPlugins()
  {
    return $this->plugins;
  }

  /**
   * Returns the plugin instance
   *
   * @param string $plugin
   * @param array $options Options for the plugin
   * @return sfPlugin
   * @throws RuntimeException
   */
  public function getPlugin($plugin, $options = array())
  {
    if (!isset($this->plugins[$plugin])) {
      if (!is_dir($this->getOption('sf_plugins_dir') . '/' . $plugin)) {
        throw new RuntimeException(sprintf('The plugin "%s" does not exists', $plugin));
      }

      $class = $plugin;
      $pluginFile = $this->getOption('sf_plugins_dir') . '/' . $plugin . '/lib/' . $plugin . '.class.php';

      if (is_readable($pluginFile)) {
        require_once $pluginFile;
      } else {
        $class = 'sfGenericPlugin';
      }

      if (!class_exists($class)) {
        throw new RuntimeException(sprintf('The plugin "%s" does not exists', $plugin));
      }

      // plugin
      $this->plugin[$plugin] = new $class($this, $plugin, $this->getOption('sf_plugins_dir') . DS . $plugin,
              $options);
    }

    return $this->plugin[$plugin];
  }

  /**
   * Initializes include path
   *
   */
  protected function initIncludePath()
  {
    set_include_path(
      $this->getOption('sf_lib_dir').PATH_SEPARATOR.
      $this->getOption('sf_root_dir').PATH_SEPARATOR.
      $this->getOption('sf_sift_lib_dir').DS.'vendor'.PATH_SEPARATOR.
      get_include_path()
    );
  }

  /**
   * Load plugins
   *
   * @return false If plugins have been already loaded
   */
  public function loadPlugins()
  {
    if ($this->pluginsLoaded) {
      return false;
    }

    if ($this instanceof sfApplication) {
      $cacheFile = $this->getOption('sf_cache_dir') . DS .
                   $this->getOption('sf_config_dir_name') .
                   DS . 'config_plugins.yml.php';
    } else {
      $cacheFile = $this->getOption('sf_root_cache_dir') .
                   DS . 'config_plugins.yml.php';
    }

    $compile = false;

    // create the file if it does not exits
    if (is_readable($cacheFile)) {
      if ($this instanceof sfApplication && $this->isDebug()) {
        $time = 0;

        // check if the file has been modified
        foreach ($this->getPluginsConfigurationFiles() as $file) {
          $time = max($time, filemtime($file));
        }

        if ($time > filemtime($cacheFile)) {
          $compile = true;
        }
      }
    } else { // cache file does not exist
      $compile = true;
    }

    if ($compile) {
      $pluginHandler = new sfPluginsConfigHandler();
      $result = $pluginHandler->execute($this->getPluginsConfigurationFiles());
      // put to cache
      sfConfigCache::writeCacheFile($cacheFile, $result);
    }

    $result = include $cacheFile;

    $classLoader = new sfClassLoader();
    foreach ($result as $pluginName => $options) {
      $plugin = $this->getPlugin($pluginName, $options);
      $plugin->initializeAutoload($classLoader);
      $this->plugins[$pluginName] = $plugin;
    }

    // register the autoloader
    $classLoader->register();

    $pluginNames = array_keys($this->plugins);

    $this->addOptions(array(
      'sf_plugins' => $pluginNames,
      'sf_plugins_glob_pattern' => sprintf('{%s}', join(',', $pluginNames)),
    ));

    $this->pluginsLoaded = true;
  }

  /**
   * Returns an array of plugins.yml configuration files
   *
   * @return array
   */
  protected function getPluginsConfigurationFiles()
  {
    $files = array();

    // project wide setting
    if(is_readable($file = $this->getOption('sf_root_dir').DS.
                  $this->getOption('sf_config_dir_name').DS.'plugins.yml'))
    {
      $files[] = $file;
    }

    // application wide setting
    if($this->getOption('sf_app_config_dir') &&
      is_readable($file = $this->getOption('sf_app_config_dir').'/plugins.yml'))
    {
      $files[] = $file;
    }

    return $files;
  }

  /**
   * Setups (X)html generation for sfHtml and sfWidget classes
   * based on sf_html5 setting
   */
  public function initHtmlTagConfiguration()
  {
    if (sfConfig::get('sf_html5')) {
      sfHtml::setXhtml(false);
      sfWidget::setXhtml(false);
    }
  }

  /**
   * Dispatches an event using the event system
   *
   * @param string $name event_namespace.event_name
   * @param array $data associative array of data
   */
  public function dispatchEvent($name, $data = array())
  {
    return $this->getEventDispatcher()->notify(new sfEvent($name, $data));
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
  public function filterByEventListeners(&$value, $eventName, $params = array())
  {
    $event = new sfEvent($eventName, $params);
    $this->getEventDispatcher()->filter($event, $value);

    return $event->getReturnValue();
  }

  /**
   * Calls methods defined via sfEventDispatcher.
   *
   * @param string $method The method name
   * @param array  $arguments The method arguments
   *
   * @return mixed The returned value of the called method
   */
  public function __call($method, $arguments)
  {
    $event = $this->dispatcher->notifyUntil(new sfEvent('configuration.method_not_found',
            array('subject' => $this, 'method' => $method, 'arguments' => $arguments)));

    if (!$event->isProcessed()) {
      throw new sfException(sprintf('Call to undefined method %s::%s.', get_class($this), $method));
    }

    return $event->getReturnValue();
  }

}
