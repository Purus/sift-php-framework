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
   * @var sfCliBaseTask
   */
  protected $task;

  /**
   * Full cleanup deletes everything from database
   *
   * @var boolean
   */
  protected $fullCleanup = true;

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
   * Constructs the installer
   *
   * @param sfCliBaseTask $task Task which invoked the installation
   * @param array $options Options for the installer
   */
  public function __construct(sfCliBaseTask $task, $options = array())
  {
    parent::__construct($options);
    $this->task = $task;


    if($this->getOption('only-upgrade'))
    {
      $this->fullCleanup = false;
    }

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
    $webDir = $this->task->environment->get('sf_web_dir');

    $directories = sfFinder::type('dir')->maxdepth(0)->in($pluginDir . '/web');

    $filesystem = new sfFilesystem();

    foreach($directories as $dir)
    {
      $dirName = basename($dir);
      $filesystem->mirror($dir, $webDir . '/' . $dirName, sfFinder::type('any'));
    }
  }

  protected function installMigrations()
  {

  }

  protected function installSettings()
  {

  }

  /**
   * Installs models into project model directory, also inserts data to database
   * 
   * @return boolean
   * @throws sfException
   */
  protected function installModel()
  {
    $pluginDir = $this->getPluginDir();

    // first check if we have any schemas inside the config directory
    // Doctrine is the only one supported
    $schemas = sfFinder::type('file')->name('*.yml')->in($pluginDir . '/config/doctrine');

    // nothing to do, no schemas available
    if(!count($schemas))
    {
      return true;
    }

    try
    {
      $buildTask = $this->task->createTask('doctrine:build-model');
    }
    catch(sfException $e)
    {
      // this means that doctrine is not installed,
      // what to do?
      throw $e;
    }

    // model classes are living here
    $projectModelDir = $this->task->environment->get('sf_model_lib_dir');

    try {
      // run the task
      $buildTask->run(array(), array(
        'schema-path' => $pluginDir . '/config/doctrine',
        // 'models-path' => $projectModelDir . '/' . $this->getOption('plugin')
      ));
    }
    // something went wrong
    catch(Doctrine_Exception $e)
    {
      throw $e;
    }

    // we have to run build sql too
    $buildTask = $this->task->createTask('doctrine:build-sql');

    $filesystem = new sfFilesystem();

    $projectDataDir = $this->task->environment->get('sf_data_dir');

    $sqlFile = sprintf('%s/sql/%s.sql', $projectDataDir, $this->getOption('plugin'));
    $filesystem->touch($sqlFile);

    try {
      // run the task
      $buildTask->run(array(), array(
        'sql-path'    => $sqlFile,
        'models-path' => $projectModelDir . '/doctrine/' . $this->getOption('plugin')
      ));

    }
    // something went wrong
    catch(Doctrine_Exception $e)
    {
      // clean file
      $filesystem->remove($sqlFile);

      throw $e;
    }

    $directory = $projectModelDir . '/doctrine/' . $this->getOption('plugin');

    // we need to call this when not upgrading!
    // if upgrading, we need to make it with migrations!
    Doctrine_Core::createTablesFromModels($projectModelDir . '/doctrine/' . $this->getOption('plugin'));
    
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

    $directories = sfFinder::type('dir')->maxdepth(0)->in($pluginDir . '/web');

    $filesystem = new sfFilesystem();

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
    $projectModelDir = $this->task->environment->get('sf_model_lib_dir');
    $plugin = $this->getOption('plugin');
    $targetDir = $projectModelDir . '/doctrine/' . $plugin;

    if(!is_dir($targetDir))
    {
      return true;
    }

    $this->task->setupDatabases();

    // we are about to drop databases
    if($this->fullCleanup)
    {
      $models = Doctrine_Core::loadModels($targetDir);
      foreach($models as $model)
      {
        $table = Doctrine_Core::getTable($model);

        $generators = $table->getConnection()->export->getAllGenerators($table);

        // drop all tables which belongs to the generators
        foreach($generators as $name => $generator)
        {
          $generatorTable = $generator->getTable();
          $generatorTable->getConnection()->export->dropTable($generatorTable->getTableName());
        }

        // finally drop the table
        $table->getConnection()->export->dropTable($table->getTableName());
      }
    }

    $filesystem = new sfFilesystem();

    // remove all
    $filesystem->remove(sfFinder::type('any')->in($targetDir));
    $filesystem->remove($targetDir);

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
    return $this->task->log($message);
  }

}
