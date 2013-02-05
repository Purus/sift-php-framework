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
class sfPluginInstaller extends sfConfigurable implements sfIPluginInstaller {

  /**
   * Project instance
   *
   * @var sfProject
   */
  protected $project;

  /**
   * Logger instance
   *
   * @var sfILogger
   */
  protected $logger;

  /**
   * Array of required options
   *
   * @var array
   */
  protected $requiredOptions = array(
      'plugin_dir' // where is the plugin located?
  );

  /**
   * Constructs the installer
   *
   * @param sfDispatcher $dispatcher
   * @param sfILogger $logger
   * @param type $options
   */
  public function __construct(sfProject $project, $options = array(), sfILogger $logger = null)
  {
    parent::__construct($options);

    $this->project = $project;
    $this->logger = $logger;

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
    $this->installSettings();
    $this->installOther();
    $this->postInstall();
  }

  public function uninstall()
  {
    $this->preUninstall();
    $this->uninstallWebAssets();
    $this->uninstallModel();
    $this->uninstallMigrations();
    $this->uninstallSettings();
    $this->uninstallOther();
    $this->postUninstall();
  }

  protected function preInstall()
  {

  }

  protected function postInstall()
  {

  }

  protected function preUninstall()
  {

  }

  protected function postUninstall()
  {

  }

  /**
   * Installs web assets to the web accessible folder
   *
   *
   */
  protected function installWebAssets()
  {
    $pluginDir = $this->getPluginDir();
    // where is web directory?
    $webDir = $this->project->getOption('sf_web_dir');

    $directories = sfFinder::type('dir')->maxdepth(0)->in($pluginDir . '/web');

    $filesystem = new sfFilesystem($this->logger);

    foreach($directories as $dir)
    {
      $dirName = basename($dir);
      // symlink the directory, copy on windows machines
      $filesystem->symlink($dir, $webDir . '/' . $dirName, true);
    }
  }

  protected function installMigrations()
  {

  }

  protected function installSettings()
  {

  }

  protected function installModel()
  {

  }

  protected function installOther()
  {

  }

  /**
   * Uninstalls web assets
   * 
   * @return boolean True on success
   */
  protected function uninstallWebAssets()
  {
    $pluginDir = $this->getPluginDir();
    // where is web directory?
    $webDir = $this->project->getOption('sf_web_dir');

    $directories = sfFinder::type('dir')->maxdepth(0)->in($pluginDir . '/web');

    $filesystem = new sfFilesystem($this->logger);

    // remove files which belongs to the plugin from the project web folder
    foreach($directories as $dir)
    {
      $dirName = basename($dir);
      $files = sfFinder::type('file')->relative()->in($dir);

      $checkDirs = array();

      foreach($files as $file)
      {
        $toBeRemoved = $webDir . '/' . $dirName . '/' . $file;

        if(dirname($file) !== '.')
        {
          $checkDirs[] = $dirName . '/' . dirname($file);
        }

        if(file_exists($toBeRemoved))
        {
          $filesystem->remove($toBeRemoved);
        }
      }

      // remove empty directories which are left by the plugin
      foreach($checkDirs as $checkDir)
      {
        if(!is_dir($webDir . '/' . $checkDir))
        {
          continue;
        }

        $files = sfFinder::type('file')->relative()->in($webDir . '/' . $checkDir);
        if(!count($files))
        {
          $filesystem->remove($webDir . '/' . $checkDir);
        }
      }
    }
    
    return true;
  }

  protected function uninstallMigrations()
  {

  }

  protected function uninstallSettings()
  {

  }

  protected function uninstallModel()
  {

  }

  protected function uninstallOther()
  {

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
   * @param string $priority
   * @return void
   */
  protected function log($message, $priority = 'info')
  {
    if(!$this->logger)
    {
      return;
    }

    return $this->logger->log($message, $priority);
  }

}
