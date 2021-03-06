<?php
/*
 * This file is part of the Sift PHP framework.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

// load PEAR
require_once dirname(__FILE__) . '/PEAR_bootstrap.php';

/**
 * sfPearEnvironment represents a PEAR environment.
 *
 * @package    Sift
 * @subpackage plugin_pear
 */
class sfPearEnvironment extends sfConfigurable {

  protected
    $dispatcher = null,
    $config = null,
    $registry = null,
    $rest = null,
    $frontend = null;

  protected $defaultOptions = array(
    'rest_base_class' => 'sfPearRest',
    'downloader_base_class' => 'sfPearDownloader'
  );
  
  protected $requiredOptions = array(
    'plugin_dir', 'cache_dir',
  );
  
  /**
   * Constructs a new sfPluginManager.
   *
   * @param sfEventDispatcher $dispatcher   An event dispatcher instance
   * @param array             $options      An array of options
   */
  public function __construct(sfEventDispatcher $dispatcher, $options)
  {
    parent::__construct($options);
    $this->initialize($dispatcher, $options);
  }

  /**
   * Initializes this sfPluginManager instance.
   *
   * Available options:
   *
   * * plugin_dir:            The directory where to put plugins
   * * cache_dir:             The local PEAR cache directory
   * * rest_base_class:       The base class for REST calls (default to sfPearRest)
   *                          (mainly used for testing)
   * * downloader_base_class: The base class for downloads (default to sfPearDownloader)
   *                          (mainly used for testing)
   *
   * @param sfEventDispatcher $dispatcher   An event dispatcher instance
   * @param array             $options      An array of options
   */
  public function initialize(sfEventDispatcher $dispatcher, $options = array())
  {
    $this->dispatcher = $dispatcher;

    if(!is_dir($options['cache_dir']))
    {
      mkdir($options['cache_dir'], 0777, true);
    }

    // initialize some PEAR objects
    $this->initializeConfiguration($options['plugin_dir'], $options['cache_dir']);
    $this->initializeRegistry();
    $this->initializeFrontend();

    // initializes the REST object
    $this->rest = new sfPearRestPlugin($this->config, 
                      array('base_class' => $this->getOption('rest_base_class')));
    
    $this->rest->setChannel($this->config->get('default_channel'));
  }

  /**
   * Returns the PEAR Rest instance.
   *
   * @return object The PEAR Rest instance
   */
  public function getRest()
  {
    return $this->rest;
  }

  /**
   * Returns the PEAR Config instance.
   *
   * @return object The PEAR Config instance
   */
  public function getConfig()
  {
    return $this->config;
  }

  /**
   * Returns the PEAR Frontend instance.
   *
   * @return object The PEAR Frontend instance
   */
  public function getFrontend()
  {
    return $this->frontend;
  }

  /**
   * Returns the PEAR Registry instance.
   *
   * @return object The PEAR Registry instance
   */
  public function getRegistry()
  {
    return $this->registry;
  }

  /**
   * Registers a PEAR channel.
   *
   * @param string  $channel    The channel name
   * @param Boolean $isDefault  true if this is the default PEAR channel, false otherwise
   */
  public function addChannel($channel, $isDefault = false)
  {    
    $this->config->set('auto_discover', true);

    if(!$this->registry->channelExists($channel, true))
    {
      $class = $this->getOption('downloader_base_class');
      $downloader = new $class($this->frontend, array(), $this->config);
      if(!$downloader->discover($channel))
      {
        throw new sfPluginException(sprintf('Unable to register channel "%s"', $channel));
      }
    }

    if($isDefault)
    {
      $this->config->set('default_channel', $channel);
      $this->rest->setChannel($channel);
    }
    
    return true;
  }

  /**
   * Unregisters a PEAR channel.
   *
   * @param string  $channel    The channel name   
   */
  public function removeChannel($channel)
  {    
    if(!$this->registry->channelExists($channel, true))
    {
      throw new sfPluginException(sprintf('Channel "%s" does not exist', $channel));
    }
    
    return $this->registry->deleteChannel($channel);
  }
  
  /**
   * Initializes the PEAR Frontend instance.
   */
  protected function initializeFrontend()
  {
    $this->frontend = PEAR_Frontend::singleton('sfPearFrontendPlugin');
    if(PEAR::isError($this->frontend))
    {
      throw new sfPluginException(sprintf('Unable to initialize PEAR Frontend object: %s', $this->frontend->getMessage()));
    }

    $this->frontend->setEventDispatcher($this->dispatcher);
  }

  /**
   * Initializes the PEAR Registry instance.
   */
  protected function initializeRegistry()
  {
    $this->registry = $this->config->getRegistry();
    if(PEAR::isError($this->registry))
    {
      throw new sfPluginException(sprintf('Unable to initialize PEAR registry: %s', $this->registry->getMessage()));
    }
  }

  /**
   * Registers the PEAR Configuration instance.
   *
   * @param string $pluginDir   The plugin path
   * @param string $cacheDir    The cache path
   */
  public function initializeConfiguration($pluginDir, $cacheDir)
  {
    $this->config = $GLOBALS['_PEAR_Config_instance'] = new sfPearConfig();

    // change the configuration for use
    $this->config->set('php_dir', $pluginDir);
    $this->config->set('data_dir', $pluginDir);
    $this->config->set('test_dir', $pluginDir);
    $this->config->set('doc_dir', $pluginDir);
    $this->config->set('bin_dir', $pluginDir);

    if($this->hasOption('preferred_state'))
    {
      $this->config->set('preferred_state', $this->getOption('preferred_state'));
    }

    // change the PEAR temp dirs
    $this->config->set('cache_dir', $cacheDir);
    $this->config->set('download_dir', $cacheDir);
    $this->config->set('temp_dir', $cacheDir);

    $this->config->set('verbose', 1);
  }

}
