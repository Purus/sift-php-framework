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
abstract class sfProject extends sfConfigurable {

  /**
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
    'sf_app_module_validate_dir_name' => 'validate',
    'sf_app_module_config_dir_name' => 'config',
    'sf_app_module_i18n_dir_name' => 'i18n',
  );

  protected $dispatcher;

  /**
   * Array of required options
   *
   * @var array
   */
  protected $requiredOptions = array(
    'sf_root_dir'
  );

  /**
   * Active application
   *
   * @var sfApplication
   */
  static protected $active = null;

  /**
   * Constructor.
   *
   * @param string              $rootDir    The project root directory
   * @param sfEventDispatcher   $dispatcher The event dispatcher
   */
  public function __construct($options = array(), sfEventDispatcher $dispatcher = null)
  {
    $this->dispatcher = is_null($dispatcher) ? new sfEventDispatcher() : $dispatcher;

    parent::__construct($options);

    if(null === self::$active && $this instanceof sfApplication)
    {
      self::$active = $this;
    }
    
    $this->configure();
  }

  /**
   * Setups the project
   * 
   */
  public function setup()
  {
    if(!$this->getOption('sf_apps_dir'))
    {
      $this->setOption('sf_apps_dir', $this->getOption('sf_root_dir').DS.$this->getOption('sf_apps_dir_name'));
    }

    if(!$this->getOption('sf_config_dir'))
    {
      $this->setOption('sf_config_dir', $this->getOption('sf_root_dir').DS.$this->getOption('sf_config_dir_name'));
    }

    if(!$this->getOption('sf_lib_dir'))
    {
      $this->setOption('sf_lib_dir', $this->getOption('sf_root_dir').DS.$this->getOption('sf_lib_dir_name'));
    }

    if(!$this->getOption('sf_bin_dir'))
    {
      $this->setOption('sf_bin_dir', $this->getOption('sf_root_dir').DS.$this->getOption('sf_bin_dir_name'));
    }

    if(!$this->getOption('sf_log_dir'))
    {
      $this->setOption('sf_log_dir', $this->getOption('sf_root_dir').DS.$this->getOption('sf_log_dir_name'));
    }

    if(!$this->getOption('sf_model_lib_dir'))
    {
      $this->setOption('sf_model_lib_dir', $this->getOption('sf_lib_dir').DS.$this->getOption('sf_model_dir_name'));
    }

    if(!$this->getOption('sf_image_font_dir'))
    {
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

    sfConfig::add($this->getOptions());   
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
   * Initializes the autoloading feature
   *
   * @param boolean $reload Force the reload?
   */
  public function initializeAutoload($reload = false)
  {
    if($this instanceof sfApplication)
    {
      // all environments share the same autoloading file
      $cacheFile = $this->getOption('sf_base_cache_dir').'/autoload.cache';
    }
    else
    {
      // project wide autoloading
      $cacheFile = $this->getOption('sf_root_cache_dir').'/project_autoload.cache';
    }

    // force the reload
    // clear the cache
    if($reload && is_readable($cacheFile))
    {
      $finder = sfFinder::type('file')->name('*autoload.cache');

      if($this instanceof sfApplication)
      {
        $where = $this->getOption('sf_base_cache_dir');
      }
      else
      {
        $where = $this->getOption('sf_root_cache_dir');
      }

      $filesystem = new sfFilesystem();
      $filesystem->remove($finder->in($where));
    }

    $autoload = sfSimpleAutoload::getInstance($cacheFile);

    if(!is_readable($cacheFile))
    {
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
      // FIXME: take only enabled plugins
      if($pluginDirs = glob($this->getOption('sf_plugins_dir').DS.'*'.DS.$this->getOption('sf_config_dir_name')
              .'/autoload.yml'))
      {
        $files = array_merge($files, $pluginDirs);
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

  public function setupPlugins()
  {
    foreach(glob($this->getOption('sf_plugins_dir').DS.'*') as $plugin)
    {
      $pluginName = basename($plugin);
      if(strpos($pluginName, 'Plugin') === false)
      {
        continue;
      }
      $this->plugins[$pluginName] = $this->getPlugin($pluginName);
    }
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
   * Returns the dispatcher instance
   *
   * @return sfEventDispatcher
   */
  public function getEventDispatcher()
  {
    return $this->dispatcher;
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
   * Returns sfApplication instance
   *
   * @param string $application
   * @return sfApplication
   * @throws RuntimeException
   */
  public function getApplication($application, $environment, $debug = false)
  {
    $class = sprintf('my%sApplication', sfInflector::camelize($application));

    $appFile = sprintf('%s/%s/%s/%s.class.php',
                                  $this->getOption('sf_apps_dir'),
                                  $application,
                                  $this->getOption('sf_lib_dir_name'),
                                  $class);
    if(is_readable($appFile))
    {
      require_once $appFile;
      if(!class_exists($class, false))
      {
        throw new RuntimeException(sprintf('The application "%s" does not exist.', $application));
      }
    }
    else
    {
      $class = 'sfGenericApplication';
    }

    return new $class($environment, $debug, array_merge(
            $this->getOptions(), array(
                'sf_app' => $application,
            )), $this->getEventDispatcher());
  }

  /**
   * Returns active application
   *
   * @return type
   * @throws RuntimeException
   */
  public function getActiveApplication()
  {
    if (!$this->hasActive())
    {
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
   * @return sfPlugin
   * @throws RuntimeException
   */
  public function getPlugin($plugin)
  {
    if(!isset($this->plugins[$plugin]))
    {
      if(!is_dir($this->getOption('sf_plugins_dir') . '/' . $plugin))
      {
        throw new RuntimeException(sprintf('The plugin "%s" does not exists', $plugin));
      }

      $pluginFile = $this->getOption('sf_plugins_dir') . '/' . $plugin . '/lib/' . $plugin . '.class.php';

      if(is_readable($pluginFile))
      {
        require_once $pluginFile;
      }
      else
      {
        $plugin = 'sfGenericPlugin';
      }

      if(!class_exists($plugin))
      {
        throw new RuntimeException(sprintf('The plugin "%s" does not exists', $plugin));
      }

      // plugin
      $this->plugin[$plugin] = new $plugin(array(
        'root_dir' => $this->getOption('sf_plugins_dir') . '/' . $plugin
      ));

    }

    return $this->plugin[$plugin];
  }

  /**
   * Bootstrap plugin configurations
   *
   * @return unknown_type
   */
  public function loadPluginConfig()
  {
    // load plugin configurations
    if($pluginConfigs = glob($this->getOption('sf_plugins_dir').'/*/config/config.php'))
    {
      foreach($pluginConfigs as $config)
      {
        include $config;
      }
    }
  }

  /**
   * Setups (X)html generation for sfHtml and sfWidget classes
   * based on sf_html5 setting
   */
  public function initHtmlTagConfiguration()
  {
    if(sfConfig::get('sf_html5'))
    {
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

    if (!$event->isProcessed())
    {
      throw new sfException(sprintf('Call to undefined method %s::%s.', get_class($this), $method));
    }

    return $event->getReturnValue();
  }

}

