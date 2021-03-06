<?php
/*
 * This file is part of the Sift PHP framework.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * sfPluginManager allows you to manage Sift plugins installation and uninstallation.
 *
 * @package    Sift
 * @subpackage plugin
 */
class sfPluginManager extends sfPearPluginManager {

  /**
   * Array of required options
   *
   * @var array
   */
  protected $requiredOptions = array(
      'web_dir',
      'sift_version',
      'sift_pear_channel'
  );

  /**
   * Configures this plugin manager.
   */
  public function configure()
  {
    // register channel
    $this->environment->addChannel($this->getOption('sift_pear_channel'), true);
    $this->registerSift();

    // register callbacks
    $this->dispatcher->connect('plugin.pre_uninstall', array($this, 'listenToPluginPreUninstall'));
    $this->dispatcher->connect('plugin.post_uninstall.success', array($this, 'listenToPluginPostUninstall'));

    $this->dispatcher->connect('plugin.pre_install', array($this, 'listenToPluginPreInstall'));
    $this->dispatcher->connect('plugin.post_install.success', array($this, 'listenToPluginPostInstall'));
  }

  /**
   * Listens to the plugin.pre_install event.
   *
   * @param sfEvent $event An sfEvent instance
   */
  public function listenToPluginPreInstall($event)
  {
    // $plugin = $event['plugin'];
  }

  /**
   * Listens to the plugin.post_install event.
   *
   * @param sfEvent $event An sfEvent instance
   */
  public function listenToPluginPostInstall($event)
  {
    // $plugin = $event['plugin'];
  }

  /*
   * Listens to the plugin.pre_uninstall event.
   *
   * @param sfEvent $event An sfEvent instance
   */
  public function listenToPluginPreUninstall($event)
  {
    $plugin  = $event['plugin'];
    // plugin version which will be uninstalled
    $version = $event['version'];
    // check plugin migration for downgrades
  }

  /**
   * Listens to the plugin.post_uninstall event.
   *
   * @param sfEvent $event An sfEvent instance
   */
  public function listenToPluginPostUninstall($event)
  {
  }

  /**
   * Registers the Sift package for the current version.
   *
   */
  protected function registerSift()
  {
    $sift = new PEAR_PackageFile_v2_rw();
    $sift->setPackage('Sift');
    $sift->setChannel($this->getOption('sift_pear_channel', 'pear.lab'));
    $sift->setConfig($this->environment->getConfig());
    $sift->setPackageType('php');
    $sift->setAPIVersion(preg_replace('/\d+(\-\w+)?$/', '0', $this->getOption('sift_version')));
    $sift->setAPIStability(false === strpos($this->getOption('sift_version'), 'DEV') ? 'stable' : 'beta');
    $sift->setReleaseVersion(preg_replace('/\-\w+$/', '', $this->getOption('sift_version')));
    $sift->setReleaseStability(false === strpos($this->getOption('sift_version'), 'DEV') ? 'stable' : 'beta');
    $sift->setDate(date('Y-m-d'));
    $sift->setDescription('Sift PHP framework');
    $sift->setSummary('Sift PHP framework');
    $sift->setLicense('MIT License');
    $sift->clearContents();
    $sift->resetFilelist();
    $sift->addMaintainer('lead', 'mishal', 'Michal Moravec', 'michi.m@gmail.com');
    $sift->setNotes('-');
    $sift->setPearinstallerDep('1.4.3');
    $sift->setPhpDep('5.2.4');

    $this->environment->getRegistry()->deletePackage('sift',
            $this->environment->getOption('sift_pear_channel', 'pear.lab'));
    if(!$this->environment->getRegistry()->addPackage2($sift))
    {
      throw new sfPluginException('Unable to register the Sift package');
    }
  }

  /**
   * Returns true if the plugin is comptatible with the dependency.
   *
   * @param  array   $dependency A dependency array
   *
   * @return Boolean true if the plugin is compatible, false otherwise
   */
  protected function isPluginCompatibleWithDependency($dependency)
  {
    if(isset($dependency['channel']) && 'sift' == $dependency['name']
            && $this->getOption('sift_pear_channel') == $dependency['channel'])
    {
      return $this->checkDependency($dependency);
    }

    return true;
  }

}
