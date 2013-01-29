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
      'sf_root_cache_dir' => $sf_root_cache_dir = ($this->getOption('sf_root_dir') . DS . $this->getOption('sf_cache_dir_name')),
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
    
    if(null === self::$active && $this instanceof sfApplication)
    {
      self::$active = $this;
    }
    
    $this->setup();
  }
  
  
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
    
    if(is_readable($cacheFile))
    {
      $autoload->register();
      return;
    }
    
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
    if($pluginDirs = glob($this->getOption('sf_plugins_dir').DS.'*'.DS.$this->getOption('sf_config_dir_name') 
            .'/autoload.yml'))
    {
      $files = array_merge($files, $pluginDirs);                                    
    }    
    
    $autoload->loadConfiguration($files);
    
    $autoload->saveCache(true);
    $autoload->register();
  }
  
  /**
   * Initializes autoloading features
   * 
   * 
   */
  public function initializeAutoloadOLd()
  {
    // application configrations
    if($this instanceof sfApplication)
    {
      $autoload = new sfClassLoader();
      $autoload->addClassMap(include $this->configCache->checkConfig($this->getOption('sf_config_dir_name').'/autoload.yml'));
    }
    else
    {
      $cacheFile = $this->getOption('sf_root_cache_dir').'/project_autoload.cache';    
      $autoload = sfSimpleAutoload::getInstance($cacheFile);
    
      // sift configuration file
      $files = array(
        $this->getOption('sf_sift_data_dir') . '/config/autoload.yml'
      );

      if(is_readable(
        $file = sprintf('%s/%s/autoload.yml', $this->getOption('sf_root_dir'), $this->getOption('sf_config_dir_name'))))
      {
        $files[] = $file;
      }
      
      $autoload->loadConfiguration($files);
      if(!is_readable($cacheFile))
      {
        $autoload->saveCache(true);
      }
      
    }
    
    // register autoloader
    $autoload->register();
    
    // switch the order of autoloaders, lets core be after the simple autoload
    sfCoreAutoload::unregister();
    // register again as second autoloader
    sfCoreAutoload::register();
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
   * 
   * @return sfEventDispatcher
   */
  public function getEventDispatcher()
  {
    return $this->dispatcher;
  }
  
  /**
   * Setups the current project
   *
   * Override this method if you want to customize your project.
   */
  public function setup()
  {
  }
  
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
   * Returns the plugin instance
   * 
   * @param string $plugin
   * @return sfPlugin
   * @throws RuntimeException
   */
  public function getPlugin($plugin)
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
    return new $plugin(array(
      'root_dir' => $this->getOption('sf_plugins_dir') . '/' . $plugin        
    ));
    
  }

  /**
   * Bootstrap plugin configurations
   *
   * @return unknown_type
   */
  public function loadPluginConfig()
  {
    // load plugin configurations !
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
   * Gets the configuration file paths for a given relative configuration path.
   *
   * @param string The configuration path
   *
   * @return array An array of paths
   */
  public function getConfigPaths($configPath)
  {
    // fix for windows paths
    $configPath = str_replace('/', DS, $configPath);

    $sf_sift_data_dir = $this->getOption('sf_sift_data_dir');
    $sf_root_dir = $this->getOption('sf_root_dir');
    $sf_app_dir = $this->getOption('sf_app_dir');
    $sf_plugins_dir = $this->getOption('sf_plugins_dir');

    $configName = basename($configPath);
    $globalConfigPath = basename(dirname($configPath)) . DS . $configName;

    $files = array(
        // sift
        $sf_sift_data_dir . DS . $globalConfigPath,
         // core modules
        $sf_sift_data_dir . DS . $configPath,
    );

    if($pluginDirs = glob($sf_plugins_dir . DS . '*' . DS . $globalConfigPath))
    {
      // plugins
      $files = array_merge($files, $pluginDirs);
    }

    $files = array_merge($files, array(
        $sf_root_dir . DS . $globalConfigPath, // project
        $sf_root_dir . DS . $configPath, // project
       
        // disable generated module
        // generated modules
        // sfConfig::get('sf_cache_dir').DS.$configPath,
    ));

    if($sf_app_dir)
    {
      $files[] =  $sf_app_dir . DS . $globalConfigPath; // application
    }
    
    if($pluginDirs = glob($sf_plugins_dir . DS . '*' . DS . $configPath))
    {
      // plugins
      $files = array_merge($files, $pluginDirs);
    }

    // module
    $files[] = $sf_app_dir . DS . $configPath;

    // If the configuration file can be overridden with a dimension, inject the appropriate path
    $applicationConfigurationFiles = array(
        'app.yml', 'factories.yml', 'filters.yml', 'i18n.yml', 
        'logging.yml', 'settings.yml', 'databases.yml', 'routing.yml',
        'asset_packages.yml'        
    );
    
    $moduleConfigurationFiles = array('cache.yml', 'module.yml', 'security.yml', 'view.yml');

    $configurationFiles = array_merge($applicationConfigurationFiles, $moduleConfigurationFiles);

    if((in_array($configName, $configurationFiles) || (strpos($configPath, 'validate'))))
    {
      $sf_dimension_dirs = $this->getOption('sf_dimension_dirs');

      if(is_array($sf_dimension_dirs) && !empty($sf_dimension_dirs))
      {
        $sf_dimension_dirs = array_reverse($sf_dimension_dirs);     // reverse dimensions for proper cascading

        $applicationDimensionDirectory = $sf_app_dir . DS . dirname($globalConfigPath) . DS . '%s' . DS . $configName;
        $moduleDimensionDirectory = $sf_app_dir . DS . dirname($configPath) . DS . '%s' . DS . $configName;

        foreach($sf_dimension_dirs as $dimension)
        {
          if(in_array($configName, $configurationFiles))       // application
          {
            foreach($sf_dimension_dirs as $dimension)
            {
              $files[] = sprintf($applicationDimensionDirectory, $dimension);
            }
          }

          if(in_array($configName, $moduleConfigurationFiles) || strpos($configPath, 'validate'))      // module
          {
            foreach($sf_dimension_dirs as $dimension)
            {
              $files[] = sprintf($moduleDimensionDirectory, $dimension);
            }
          }
        }
      }
    }

    $configs = array();
    foreach(array_unique($files) as $file)
    {
      if(is_readable($file))
      {
        $configs[] = $file;
      }
    }

    return $configs;
  }
  
  
//  /**
//   * Calls methods defined via sfEventDispatcher.
//   *
//   * @param string $method The method name
//   * @param array  $arguments The method arguments
//   *
//   * @return mixed The returned value of the called method
//   */
//  public function __call($method, $arguments)
//  {
//    $event = $this->dispatcher->notifyUntil(new sfEvent('configuration.method_not_found',             
//            array('subject' => $this, 'method' => $method, 'arguments' => $arguments)));
//    
//    if (!$event->isProcessed())
//    {
//      throw new sfException(sprintf('Call to undefined method %s::%s.', get_class($this), $method));
//    }
//
//    return $event->getReturnValue();
//  }  
  
}

