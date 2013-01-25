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
    parent::__construct($options);
    
    if(null === self::$active || $this instanceof sfApplication)
    {
      self::$active = $this;
    }
    
    $this->setup();
  }
  
  /**
   * Setups the current project
   *
   * Override this method if you want to customize your project.
   */
  public function setup()
  {
  }
  
  /**
   * Returns project version
   * 
   * @return string
   */
  // Strict standards does not allow to have abstract statis function
  // abstract public static function getVersion();
  
  /**
   * Returns sfApplication instance 
   * 
   * @param string $application
   * @return sfApplication
   * @throws RuntimeException    
   */
  public function getApplication($application)
  {
    $class = sprintf('my%sApplication', sfInflector::camelize($application));
    
    if(!class_exists($class))
    {
      throw new RuntimeException(sprintf('The application "%s" does not exist.', $application));
    }
    
    return new $class(); 
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
        
    if(!is_readable($pluginFile))
    {
      throw new RuntimeException(sprintf(
              'The plugin "%s" is missing specification class in "lib/%s.class.php"', 
              $plugin, $plugin));
    }
    
    require_once $pluginFile;
    
    if(!class_exists($plugin))
    {
      throw new RuntimeException(sprintf('The plugin "%s" does not exists', $plugin));
    }
    
    // plugin
    return new $plugin(array(
      'root_dir' => $this->getOption('sf_plugins_dir') . '/' . $plugin        
    ));
    
  }
  
}

