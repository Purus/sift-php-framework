<?php
/*
 * This file is part of the Sift PHP framework.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * sfPluginManager allows you to manage plugins installation and uninstallation.
 *
 * @package    Sift
 * @subpackage plugin
 */
class sfPearPluginManager extends sfConfigurable {

  protected
    $dispatcher = null,
    $environment = null,
    $logger      = null,
    $installing = array();

  /**
   * Array of default options
   * @var array
   */
  protected $defaultOptions = array(
    // Options for PEAR_Installer class.
    // 
    // * - installroot   : optional prefix directory for installation
    // * - force         : force installation
    // * - register-only : update registry but don't install files
    // * - upgrade       : upgrade existing install
    // * - soft          : fail silently
    // * - nodeps        : ignore dependency conflicts/missing dependencies
    // * - alldeps       : install all dependencies
    // * - onlyreqdeps   : install only required dependencies      
    'pear_installer_options' => array(
        'upgrade' => true
    )
  );
  
  /**
   * Constructs a new sfPluginManager.
   *
   * @param sfEventDispatcher $dispatcher   An event dispatcher instance
   * @param sfPearEnvironment $environment  A sfPearEnvironment instance
   */
  public function __construct(sfEventDispatcher $dispatcher, sfPearEnvironment $environment, sfILogger $logger, $options = array())
  {
    parent::__construct($options);

    $this->initialize($dispatcher, $environment, $logger);
  }

  /**
   * Initializes this sfPluginManager instance.
   *
   * see sfPearEnvironment for available options.
   *
   * @param sfEventDispatcher $dispatcher   An event dispatcher instance
   * @param sfPearEnvironment $environment  A sfPearEnvironment instance
   */
  public function initialize(sfEventDispatcher $dispatcher, sfPearEnvironment $environment, sfILogger $logger)
  {
    $this->dispatcher = $dispatcher;
    $this->environment = $environment;
    $this->logger = $logger;

    // configure this plugin manager
    $this->configure();
  }

  /**
   * Configures this plugin manager.
   */
  public function configure()
  {

  }

  /**
   * Returns the sfPearEnvironment instance.
   *
   * @return sfPearEnvironment The sfPearEnvironment instance
   */
  public function getEnvironment()
  {
    return $this->environment;
  }

  /**
   * Returns a list of installed plugin.
   *
   * @return array An array of installed plugins
   */
  public function getInstalledPlugins($skipSift = true)
  {
    $installed = array();
    foreach($this->environment->getRegistry()->packageInfo(null, null, null) as $channel => $packages)
    {
      foreach($packages as $package)
      {
        $_installed = $this->environment->getRegistry()->getPackage(isset($package['package']) ? $package['package'] : $package['name'], $channel);
        if($skipSift && $_installed->getPackage() == 'Sift')
        {
          continue;
        }
        $installed[] = $_installed;
      }
    }

    return $installed;
  }
  
  /**
   * Is given plugin installed?
   * 
   * @param string $plugin Plugin name
   * @param string $channel Channel name
   * @return boolean
   */
  public function isPluginInstalled($plugin, $channel = '')
  {
    if(empty($channel))
    {
      $channel = $this->environment->getConfig()->get('default_channel');
    }
    
    $installedPlugins = $this->getInstalledPlugins();
    foreach($installedPlugins as $installed)
    {
      if($installed->getPackage() == $plugin 
              && $installed->getChannel() == $channel)
      {
        return true;
      }      
    }    
    return false;
  }

  /**
   * Installs a plugin.
   *
   * If you don't pass a version, it will install the latest version available
   * for the current project symfony version.
   *
   * Available options:
   *
   *  * channel:      The plugin channel name
   *  * version:      The version to install
   *  * stability:    The stability preference
   *  * install-deps: Whether to automatically install dependencies (default to false)
   *
   * @param string $plugin  The plugin name
   * @param array  $options An array of options
   *
   * @return Boolean|string true if the plugin is already installed, the name of the installed plugin otherwise
   */
  public function installPlugin($plugin, $options = array(), $force = false)
  {
    $this->installing = array();
    return $this->doInstallPlugin($plugin, $options, $force);
  }

  /**
   * Upgrades a plugin.
   * 
   * @param string $plugin
   * @param array $options
   * @return Boolean|string true if the plugin is already installed, the name of the installed plugin otherwise
   */
  public function upgradePlugin($plugin, $options = array())
  {
    return $this->installPlugin($plugin, $options, true);    
  }

  /**
   * Packages a plugin
   * 
   * @param string $pluginPackageXml Path to package.xml file
   * @param array $options
   * @throws sfException
   */
  public function packagePlugin($pluginPackageXml, $options = array())
  {
    $dir = dirname($pluginPackageXml);

    $cwd = getcwd();

    chdir($dir);

    $packager = new sfPearPackager();
    $packager->setLogger($this->logger);

    $package = $packager->package($pluginPackageXml, !$options['nocompress']);

    if(PEAR::isError($package))
    {
      throw new sfException($package->getMessage());
    }

    // change back where we were
    chdir($cwd);
  }

  /**
   * Installs a plugin
   *
   * @see installPlugin()
   */
  protected function doInstallPlugin($plugin, $options = array(), $force = false)
  {
    $channel = isset($options['channel']) ? $options['channel'] : $this->environment->getConfig()->get('default_channel');
    $stability = isset($options['stability']) ? $options['stability'] : $this->environment->getConfig()->get('preferred_state', null, $channel);
    $version = isset($options['version']) ? $options['version'] : null;

    $isPackage = true;
    if(0 === strpos($plugin, 'http://') || file_exists($plugin))
    {
      $download = $plugin;
      $isPackage = false;
    }
    else if(false !== strpos($plugin, '/'))
    {
      list($channel, $plugin) = explode('/', $plugin);
    }

    $this->dispatcher->notify(new sfEvent('plugin.pre_install',
                                    array('manager' => $this,
                                          'channel' => $channel,
                                          'plugin' => $plugin,
                                          'version' => $version,
                                          'is_package' => $isPackage)));

    if($isPackage)
    {
      $this->environment->getRest()->setChannel($channel);

      if(!preg_match(PEAR_COMMON_PACKAGE_NAME_PREG, $plugin))
      {
        throw new sfPluginException(sprintf('Plugin name "%s" is not a valid package name', $plugin));
      }

      if(!$version)
      {
        $version = $this->getPluginVersion($plugin, $stability);
      }
      else
      {
        if(!$this->isPluginCompatible($plugin, $version))
        {
          throw new sfPluginDependencyException(sprintf('Plugin "%s" in version "%s" is not compatible with the current application', $plugin, $version));
        }
      }

      if(!preg_match(PEAR_COMMON_PACKAGE_VERSION_PREG, $version))
      {
        throw new sfPluginException(sprintf('Plugin version "%s" is not a valid version', $version));
      }

      $existing = $this->environment->getRegistry()->packageInfo($plugin, 'version', $channel);
      if(version_compare($existing, $version) === 0)
      {
        $this->logger->log('Plugin is already installed');
        return true;
      }

      // skip if the plugin is already installing and we are here through a dependency)
      if(isset($this->installing[$channel . '/' . $plugin]))
      {
        return true;
      }

      // convert the plugin package into a discrete download URL
      $download = $this->environment->getRest()->getPluginDownloadURL($plugin, $version, $stability);
      if(PEAR::isError($download))
      {
        throw new sfPluginException(sprintf('Problem downloading the plugin "%s": %s', $plugin, $download->getMessage()));
      }
    }

    // download the plugin and install
    $class = $this->environment->getOption('downloader_base_class');
    $downloader = new $class($this, array('upgrade' => true), $this->environment->getConfig());

    $this->installing[$channel . '/' . $plugin] = true;

    if($isPackage)
    {
      $this->checkPluginDependencies($plugin, $version, array(
          'install_deps' => isset($options['install_deps']) ? (bool) $options['install_deps'] : false,
          'stability' => $stability,
      ));
    }

    // download the actual URL to the plugin
    $downloaded = $downloader->download(array($download));
    if(PEAR::isError($downloaded))
    {
      throw new sfPluginException(sprintf('Problem when downloading "%s": %s', $download, $downloaded->getMessage()));
    }
    $errors = $downloader->getErrorMsgs();

    if(count($errors))
    {
      $err = array();
      foreach($errors as $error)
      {
        $err[] = $error;
      }

      if(!count($downloaded))
      {
        throw new sfPluginException(sprintf('Plugin "%s" installation failed. %s', $plugin, implode("\n", $err)));
      }
    }

    $pluginPackage = $downloaded[0];

    $installer = new PEAR_Installer($this);
    
    // * - installroot   : optional prefix directory for installation
    // * - force         : force installation
    // * - register-only : update registry but don't install files
    // * - upgrade       : upgrade existing install
    // * - soft          : fail silently
    // * - nodeps        : ignore dependency conflicts/missing dependencies
    // * - alldeps       : install all dependencies
    // * - onlyreqdeps   : install only required dependencies

    $installer->setOptions(array('upgrade' => true, 'force' => $force));
    
    $packages = array($pluginPackage);
    $installer->sortPackagesForInstall($packages);
    PEAR::staticPushErrorHandling(PEAR_ERROR_RETURN);
    $err = $installer->setDownloadedPackages($packages);
    if(PEAR::isError($err))
    {
      PEAR::staticPopErrorHandling();
      throw new sfPluginException($err->getMessage());
    }

    $info = $installer->install($pluginPackage, array('upgrade' => true));
    PEAR::staticPopErrorHandling();
    
    if(PEAR::isError($info) || !is_array($info))
    {
      $this->dispatcher->notify(new sfEvent('plugin.post_install.failure', array(
          'channel' => $channel,
          'manager' => $this,
          'plugin' => $pluginPackage->getPackage())));      
      
      throw new sfPluginException(sprintf('Installation of "%s" plugin failed: %s', $plugin, $info->getMessage()));
    }

    $this->logger->log(sprintf('Installation successful for plugin "%s"', $plugin));

    $this->dispatcher->notify(new sfEvent('plugin.post_install.success', array(
        'channel' => $channel,
        'manager' => $this,
        'plugin' => $pluginPackage->getPackage())));

    unset($this->installing[$channel . '/' . $plugin]);

    return $pluginPackage->getPackage();
    
  }

  /**
   * Uninstalls a plugin.
   *
   * @param string $plugin  The plugin name
   * @param string $channel The channel name
   */
  public function uninstallPlugin($plugin, $channel = null)
  {
    if(false !== strpos($plugin, '/'))
    {
      list($channel, $plugin) = explode('/', $plugin);
    }

    $channel = null === $channel ? $this->environment->getConfig()->get('default_channel') : $channel;

    $existing = $this->environment->getRegistry()->packageInfo($plugin, 'version', $channel);
    if(null === $existing)
    {
      $this->logger->log(sprintf('Plugin "%s" is not installed', $plugin));
      return false;
    }

    $this->dispatcher->notify(new sfEvent('plugin.pre_uninstall',
            array('channel' => $channel,
                  'manager' => $this,
                  'version' => $existing,
                  'plugin' => $plugin)));

    $package = $this->environment->getRegistry()->parsePackageName($plugin, $channel);

    $installer = new PEAR_Installer($this);
    
    $packages = array($this->environment->getRegistry()->getPackage($plugin, $channel));
    $installer->setUninstallPackages($packages);
    $ret = $installer->uninstall($package);

    if(PEAR::isError($ret))
    {
      throw new sfPluginException(sprintf('Problem uninstalling plugin "%s": %s', $plugin, $ret->getMessage()));
    }

    if($ret)
    {
      $this->dispatcher->notify(new sfEvent('plugin.post_uninstall.success', array(
          'channel' => $channel,
          'manager' => $this,
          'version' => $existing,
          'plugin' => $plugin)));

      $this->logger->log(sprintf('Uninstallation successful for plugin "%s"', $plugin));
    }
    else
    {
      $this->dispatcher->notify(new sfEvent('plugin.post_uninstall.failure', array(
          'channel' => $channel,
          'manager' => $this,
          'version' => $existing,
          'plugin' => $plugin)));

      throw new sfPluginException(sprintf('Uninstallation of "%s" plugin failed', $plugin));
    }

    return $ret;
  }

  /**
   * Checks all plugin dependencies.
   *
   * Available options:
   *
   *  * stability:    The stability preference
   *  * install_deps: Whether to automatically install dependencies (default to false)
   *
   * @param string $plugin  The plugin name
   * @param string $version The plugin version
   * @param array  $options An array of options
   */
  public function checkPluginDependencies($plugin, $version, $options = false)
  {
    $dependencies = $this->environment->getRest()->getPluginDependencies($plugin, $version);

    if(!isset($dependencies['required']) || !isset($dependencies['required']['package']))
    {
      return;
    }

    $deps = $dependencies['required']['package'];
    if(!isset($deps[0]))
    {
      $deps = array($deps);
    }

    foreach($deps as $dependency)
    {
      if(!$this->checkDependency($dependency))
      {
        $version = (isset($dependency['min']) ? ' >= ' . $dependency['min'] : '') . (isset($dependency['max']) ? ' <= ' . $dependency['max'] : '') . (isset($dependency['exclude']) ? ' exclude ' . $dependency['exclude'] : '');

        if(isset($options['install-deps']) && $options['install-deps'])
        {
          try
          {
            $this->doInstallPlugin($dependency['name'], array_merge($options, array('channel' => $dependency['channel'])));
          }
          catch(sfException $e)
          {
            throw new sfPluginRecursiveDependencyException(sprintf('Unable to install plugin "%s" (version %s) because it depends on plugin "%s" which cannot be installed automatically: %s', $plugin, $version, $dependency['name'], $e->getMessage()));
          }

          continue;
        }

        throw new sfPluginDependencyException(sprintf('Unable to install plugin "%s" (version %s) because it depends on plugin "%s" which is not installed (install dependencies by hand or use the --install-deps option for automatic installation).', $plugin, $version, $dependency['name']));
      }
    }
  }

  /**
   * Gets the "best" version available for a given plugin.
   *
   * @param  string $plugin     The plugin name
   * @param  string $stability  The stability name
   *
   * @return string The version
   */
  public function getPluginVersion($plugin, $stability = null)
  {
    $versions = $this->environment->getRest()->getPluginVersions($plugin, $stability);
    foreach($versions as $version)
    {
      if(!$this->isPluginCompatible($plugin, $version))
      {
        continue;
      }

      return $version;
    }

    throw new sfPluginDependencyException(sprintf('No release available for plugin "%s" in state "%s" that satisfies the application requirements.', $plugin, $stability));
  }

  /**
   * Returns true if the plugin is comptatible with your environment.
   *
   * @param  string $plugin   The plugin name
   * @param  string $version  The plugin version
   *
   * @return Boolean true if the plugin is compatible, false otherwise
   */
  public function isPluginCompatible($plugin, $version)
  {
    $dependencies = $this->environment->getRest()->getPluginDependencies($plugin, $version);

    if(!isset($dependencies['required']) || !isset($dependencies['required']['package']))
    {
      return true;
    }

    $deps = $dependencies['required']['package'];
    if(!isset($deps[0]))
    {
      $deps = array($deps);
    }

    foreach($deps as $dependency)
    {
      if(!$this->isPluginCompatibleWithDependency($dependency))
      {
        return false;
      }
    }

    return true;
  }

  /**
   * Returns the license for a given plugin.
   *
   * @param string $plugin    The plugin name
   * @param array  $options   An array of options
   *
   * @return string The license
   *
   * @see installPlugin() for available options
   */
  public function getPluginLicense($plugin, $options = array())
  {
    $channel = isset($options['channel']) ? $options['channel'] : $this->environment->getConfig()->get('default_channel');
    $stability = isset($options['stability']) ? $options['stability'] : $this->environment->getConfig()->get('preferred_state', null, $channel);
    $version = isset($options['version']) ? $options['version'] : null;

    $rest = $this->environment->getRest();
    $rest->setChannel(null === $channel ? $this->environment->getConfig()->get('default_channel') : $channel);

    if(null === $version)
    {
      try
      {
        $version = $this->getPluginVersion($plugin, $stability);
      }
      catch(Exception $e)
      {
        // no release available
        return false;
      }
    }
    else
    {
      if(!$this->isPluginCompatible($plugin, $version))
      {
        throw new sfPluginDependencyException(sprintf('Plugin "%s" in version "%s" is not compatible with the current application', $plugin, $version));
      }
    }

    return $rest->getPluginLicense($plugin, $version);
  }

  /**
   * Returns true if the plugin is comptatible with the dependency.
   *
   * @param  array   $dependency An dependency array
   *
   * @return Boolean true if the plugin is compatible, false otherwise
   */
  protected function isPluginCompatibleWithDependency($dependency)
  {
    return true;
  }

  /**
   * Checks that the dependency is valid.
   *
   * @param  array   $dependency A dependency array
   *
   * @return Boolean true if the dependency is valid, false otherwise
   */
  protected function checkDependency($dependency)
  {
    $dependencyChecker = new PEAR_Dependency2($this->environment->getConfig(), array(), array('package' => '', 'channel' => ''));

    PEAR::staticPushErrorHandling(PEAR_ERROR_RETURN);
    $e = $dependencyChecker->validatePackageDependency($dependency, true, array());
    PEAR::staticPopErrorHandling();
    if(PEAR::isError($e))
    {
      return false;
    }

    return true;
  }

}
