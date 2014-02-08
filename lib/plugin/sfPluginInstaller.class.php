<?php
/*
 * This file is part of the Sift PHP framework.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * sfPluginInstaller allows you to perform custom installs.
 *
 * @package    Sift
 * @subpackage plugin
 */
class sfPluginInstaller extends sfConfigurable implements sfIPluginInstaller
{
  /**
   * Project instance
   *
   * @var sfCliBaseTask
   */
  protected $task;

  /**
   * Array of required options
   *
   * @var array
   */
  protected $requiredOptions = array(
    'plugin',    // plugin name
    'plugin_dir' // where is the plugin located?
  );

  /**
   * Array of default options
   *
   * @var array
   */
  protected $defaultOptions = array(
    'previous_release' => false
  );

  /**
   * Constructs the installer
   *
   * @param sfCliBaseTask $task Task which invoked the installation
   * @param array $options Options for the installer
   */
  public function __construct(sfCliBaseTask $task, $options = array())
  {
    parent::__construct($options);
    $this->task = $task;

    $this->configure();
  }

  /**
   * Allows to configure the installer
   *
   */
  public function configure()
  {
  }

  /**
   * Installs the plugin assets, models, migrations and other stuff
   *
   */
  public function install()
  {
    $this->preInstall();
    $this->installWebAssets();
    $this->installModel();
    $this->installMigrations();
    $this->installData();
    $this->installSettings();
    $this->installOther();
    $this->postInstall();
  }

  public function uninstall()
  {
    $this->preUninstall();
    $this->uninstallWebAssets();
    $this->uninstallModel();
    $this->uninstallData();
    $this->uninstallSettings();
    $this->uninstallOther();
    $this->postUninstall();
  }

  /**
   * Pre install
   */
  protected function preInstall()
  {
  }

  /**
   * Post install
   *
   */
  protected function postInstall()
  {
  }

  /**
   * Pre uninstall
   */
  protected function preUninstall()
  {
  }

  /**
   * Post uninstall
   */
  protected function postUninstall()
  {
  }

  /**
   * Installs web assets to the web accessible folder
   *
   * @return boolean True on success, false on failure
   */
  protected function installWebAssets()
  {
    $pluginDir = $this->getPluginDir();
    // where is web directory?
    $webDir = $this->task->environment->get('sf_web_dir');

    $directories = sfFinder::type('dir')->maxDepth(0)->in($pluginDir . '/web');

    $filesystem = new sfFilesystem();

    foreach ($directories as $dir) {
      $dirName = basename($dir);
      $filesystem->mirror($dir, $webDir . '/' . $dirName, sfFinder::type('any'));
    }

    return true;
  }

  /**
   * Installs migrations to database
   *
   * @return boolean True on success, false on failure
   */
  protected function installMigrations()
  {
    return true;
  }

  /**
   * Installs settings
   *
   * @return boolean True on success, false on failure
   */
  protected function installSettings()
  {
    return true;
  }

  /**
   * Installs models into project model directory, also inserts data to database.
   * This is dummy method. Since Sift is not bundled with any ORM.
   *
   * @return boolean
   * @throws sfException
   */
  protected function installModel()
  {
    return true;
  }

  /**
   * Installs data to database
   *
   * @return boolean True on success, false on failure
   */
  protected function installData()
  {
    return true;
  }

  /**
   * Installs other stuff
   *
   * @return boolean true on success
   */
  protected function installOther()
  {
    return true;
  }

  /**
   * Uninstalls web assets
   *
   * @return boolean true on success, false on failure
   */
  protected function uninstallWebAssets()
  {
    $pluginDir = $this->getPluginDir();
    // where is web directory?
    $webDir = $this->task->environment->get('sf_web_dir');

    $directories = sfFinder::type('dir')->maxDepth(0)->in($pluginDir . '/web');

    $filesystem = new sfFilesystem();

    // remove files which belongs to the plugin from the project web folder
    foreach ($directories as $dir) {
      $dirName = basename($dir);
      $files = sfFinder::type('file')->relative()->in($dir);

      $checkDirs = array();

      foreach ($files as $file) {
        $toBeRemoved = $webDir . '/' . $dirName . '/' . $file;

        if (dirname($file) !== '.') {
          $checkDirs[] = $dirName . '/' . dirname($file);
        }

        if (file_exists($toBeRemoved)) {
          $filesystem->remove($toBeRemoved);
        }
      }

      // remove empty directories which are left by the plugin
      foreach ($checkDirs as $checkDir) {
        if (!is_dir($webDir . '/' . $checkDir)) {
          continue;
        }

        $files = sfFinder::type('file')->relative()->in($webDir . '/' . $checkDir);
        if (!count($files)) {
          $filesystem->remove($webDir . '/' . $checkDir);
        }
      }
    }

    return true;
  }

  /**
   * Uninstalls settings
   *
   * @return boolean True on success, false on failure
   */
  protected function uninstallSettings()
  {
    return true;
  }

  /**
   * Uninstalls model classes
   *
   * @return boolean True if success, false on failure
   */
  protected function uninstallModel()
  {
    return true;
  }

  /**
   * Uninstall data from database
   *
   * @return boolean True on success, false on failure
   */
  protected function uninstallData()
  {
    return true;
  }

  /**
   * Uninstalls other stuff
   *
   * @return boolean true on success, false on failure
   */
  protected function uninstallOther()
  {
    return true;
  }

  /**
   * Returns a path to the plugin location
   *
   * @return string
   */
  protected function getPluginDir()
  {
    return $this->getOption('plugin_dir');
  }

  /**
   * Logs message to the logger.
   *
   * @param string $message
   * @return void
   */
  protected function log($message)
  {
    return $this->task->logSection('plugin-installer', $message);
  }

}
