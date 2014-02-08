<?php
/*
 * This file is part of the Sift PHP framework.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * Base class for all tasks
 *
 * @package    Sift
 * @subpackage cli_task
 */
abstract class sfCliBaseTask extends sfCliCommandApplicationTask
{
  protected $application   = null,
    $phpCli        = null,
    $databases     = null;

  /**
   * @see sfCliTask
   */
  protected function doRun(sfCliCommandManager $commandManager, $options)
  {
    $event = $this->dispatcher->filter(new sfEvent('cli_task.filter_options', array(
                                      'command_manager' => $commandManager,
                                       'taks' => $this)
                                        ), $options);

    $options = $event->getReturnValue();

    $this->process($commandManager, $options);
    $event = new sfEvent('command.pre_command', array('task' => &$this, 'arguments' => $commandManager->getArgumentValues(), 'options' => $commandManager->getOptionValues()));

    $this->dispatcher->notifyUntil($event);
    if ($event->isProcessed())
    {
      return $event->getReturnValue();
    }

    $this->checkProjectExists();

    $requiresApplication = $commandManager->getArgumentSet()->hasArgument('application')
                            || $commandManager->getOptionSet()->hasOption('application');

    // task requires application to be run
    if($requiresApplication && null === $this->application)
    {
      $application = $commandManager->getArgumentSet()->hasArgument('application') ?
                     $commandManager->getArgumentValue('application') :
                     ($commandManager->getOptionSet()->hasOption('application') ? $commandManager->getOptionValue('application') : null);

      // environment
      $env = $commandManager->getOptionSet()->hasOption('env') ? $commandManager->getOptionValue('env') : 'cli';

      if(true === $application)
      {
        $application = $this->getFirstApplication();

        if($commandManager->getOptionSet()->hasOption('application'))
        {
          $commandManager->setOption($commandManager->getOptionSet()->getOption('application'), $application);
        }

      }

      $this->application = $this->getApplication($application, $env);

      // add application specific setting to the environment
      $this->environment->add($this->application->getOptions());
    }

    if (null !== $this->commandApplication && !$this->commandApplication->withTrace())
    {
      $this->environment->set('sf_logging_enabled', false);
    }

    $ret = $this->execute($commandManager->getArgumentValues(), $commandManager->getOptionValues());

    $this->dispatcher->notify(new sfEvent('command.post_command', array('task' => $this)));

    return $ret;
  }

  /**
   * Creates sfContext instance for current application
   *
   * @param $application Application name
   * @throws sfException Is not application is initialized
   */
  public function createContextInstance($application = null)
  {
    if(is_string($application))
    {
      $this->application = $this->getApplication($application, $this->environment->get('sf_environment'));
    }

    if(!$this->application)
    {
      throw new sfException('No application is initialized. Cannot create sfContext instance.');
    }

    $name = $this->application->getName();
    if(!sfContext::hasInstance($name))
    {
      sfContext::createInstance($this->application, $name);
    }
  }

  /**
   * Returns the filesystem instance.
   *
   * @return sfFilesystem A sfFilesystem instance
   */
  public function getFilesystem()
  {
    if (!isset($this->filesystem))
    {
      if (null === $this->commandApplication || $this->commandApplication->isVerbose())
      {
        $this->filesystem = new sfFilesystem($this->logger, $this->formatter);
      }
      else
      {
        $this->filesystem = new sfFilesystem();
      }
    }

    return $this->filesystem;
  }

  /**
   * Returns database
   *
   * @param string $name Database connection name
   * @return sfDatabase
   * @throws sfException
   */
  protected function getDatabase($name = 'default')
  {
    $this->setupDatabases();

    if(!isset($this->databases[$name]))
    {
      throw new sfException(sprintf('Invalid database connection. Connection "%s" does not exist.', $name));
    }

    return $this->databases[$name];
  }

  public function setupDatabases()
  {
    if(!isset($this->databases))
    {
      $configHandler = new sfDatabaseConfigHandler();

      $files = array();

      if($appConfigDir = $this->environment->get('sf_app_config_dir'))
      {
        if($file = is_readable($appConfigDir . '/' . $this->environment->get('sf_app_config_dir_name') . 'databases.yml'))
        {
          $files[] = $file;
        }
      }

      $files[] = $this->environment->get('sf_root_dir') . '/'.
                $this->environment->get('sf_config_dir_name') . '/databases.yml';

      $this->databases = $configHandler->evaluate($files);
    }
  }

  /**
   * Checks if the current directory is a Sift project directory.
   *
   * @return true if the current directory is a Sift project directory, false otherwise
   */
  public function checkProjectExists()
  {
    if(!file_exists('sift'))
    {
      throw new sfException('You must be in Sift project directory.');
    }

    return true;
  }

  /**
   * Checks if an application exists.
   *
   * @param string $app  The application name
   * @param boolean $throwException Throw exception if it does not exist?
   * @return boolean true if the application exists, false otherwise (if $throwException is false)
   * @throws sfException
   */
  public function checkAppExists($app, $throwException = true)
  {
    if(!is_dir($this->environment->get('sf_apps_dir').'/'.$app))
    {
      if($throwException)
      {
        throw new sfException(sprintf('Application "%s" does not exist', $app));
      }
      return false;
    }
    return true;
  }

  /**
   * Checks if an plugin exists.
   *
   * @param string $app  The plugin name
   * @param boolean $throwException Throw exception if it does not exist?
   * @return boolean true if the plugin exists, false otherwise (if $throwException is false)
   * @throws sfException
   */
  public function checkPluginExists($plugin, $throwException = true)
  {
    if(!is_dir($this->environment->get('sf_plugins_dir').'/'.$plugin))
    {
      if($throwException)
      {
        throw new sfException(sprintf('Plugin "%s" does not exist', $plugin));
      }
      return false;
    }
    return true;
  }

  /**
   * Checks if a module exists.
   *
   * @param  string $app     The application name
   * @param  string $module  The module name
   *
   * @return bool true if the module exists, false otherwise
   */
  public function checkModuleExists($app, $module)
  {
    if (!is_dir($this->environment->get('sf_apps_dir').'/'.$app.'/modules/'.$module))
    {
      throw new sfException(sprintf('Module "%s/%s" does not exist.', $app, $module));
    }
  }

  /**
   * Creates a the application object.
   *
   * @param string  $application The application name
   * @param string  $env         The environment name
   *
   * @return sfApplication A sfApplication instance
   */
  protected function getApplication($application, $env)
  {
    $this->checkAppExists($application);
    $this->application = $this->commandApplication->getProject()->getApplication($application, $env, true);
    return $this->application;
  }

  /**
   * Returns an array with following structure:
   *
   * * application name (or plugin name)
   * * directory (directory to the application or plugin)
   * * isPlugin? (true is the application is a plugin)
   *
   * @param type $application
   * @return array ($applicationName, $dir, $isPlugin)
   */
  protected function getApplicationOrPlugin($application)
  {
    $isPlugin = false;

    // this is a plugin
    if(preg_match('|Plugin$|', $application))
    {
      $this->checkPluginExists($application);
      $isPlugin = true;
      $dir = $this->environment->get('sf_plugins_dir') . '/' . $application;
    }
    else
    {
      $this->checkAppExists($application);
      $dir = $this->environment->get('sf_apps_dir') . '/' . $application;
    }

    return array($application, $dir, $isPlugin);
  }

  /**
   * Returns the first application in apps.
   *
   * @return string The Application name
   */
  protected function getFirstApplication()
  {
    if (count($dirs = sfFinder::type('dir')->maxDepth(0)->followLink()->relative()->in(
            $this->environment->get('sf_apps_dir'))))
    {
      return $dirs[0];
    }

    return null;
  }

  /**
   * Returns project property specified in config/properties.ini file
   *
   * @param string $property
   * @param mixed $default
   * @return mixed
   */
  public function getProjectProperty($property, $default = null)
  {
    // load configuration
    if(is_readable($propertyFile = $this->environment->get('sf_config_dir').'/properties.ini'))
    {
      $properties = parse_ini_file($propertyFile, true);

      if(isset($properties['project']))
      {
        $find = $properties['project'];
      }

      return isset($find[$property]) ? $find[$property] : $default;
    }

    return $default;
  }

  /**
   * Reloads all autoloaders.
   *
   * This method should be called whenever a task generates new classes that
   * are to be loaded by the autoloader. It clears the autoloader
   * cache for all applications and environments and the current execution.
   *
   * @see initializeAutoload()
   */
  protected function reloadAutoload()
  {
    $this->initializeAutoload($this->application ?
                              $this->application : $this->commandApplication->getProject(), true);
  }

  /**
   * Initializes autoloaders.
   *
   * @param sfApplication|sfProject $application The current project or application
   * @param boolean                 $reload      If true, all autoloaders will be reloaded
   */
  protected function initializeAutoload($application, $reload = false)
  {
    // sfAutoload
    if($reload)
    {
      $this->logSection('autoload', 'Resetting CLI autoloader');
    }

    $application->initializeAutoload($reload);
  }

  /**
   * Mirrors a directory structure inside the created project.
   *
   * @param string   $dir    The directory to mirror
   * @param sfFinder $finder A sfFinder instance to use for the mirroring
   * @param boolean $discardPlaceholders Discard .sf placeholders from the dir?
   */
  protected function installDir($dir, $finder = null, $discardDirPlaceholders = true)
  {
    if (null === $finder)
    {
      $finder = sfFinder::type('any');
      if($discardDirPlaceholders)
      {
        $finder->discard('.sf');
      }
    }

    $this->getFilesystem()->mirror($dir, getcwd(), $finder);
  }

  /**
   * Replaces tokens in files contained in a given directory.
   *
   * If you don't pass a directory, it will replace in the config/ and lib/ directory.
   *
   * You can define global tokens by defining the $this->tokens property.
   *
   * @param array $dirs   An array of directory where to do the replacement
   * @param array $tokens An array of tokens to use
   */
  protected function replaceTokens($dirs = array(), $tokens = array())
  {
    if (!$dirs)
    {
      $dirs = array($this->environment->get('sf_config_dir'), $this->environment->get('sf_lib_dir'));
    }

    $tokens = array_merge(isset($this->tokens) ? $this->tokens : array(), $tokens);

    $this->getFilesystem()->replaceTokens(sfFinder::type('file')->prune('vendor')->in($dirs), '##', '##', $tokens);
  }

  /**
   * Returns path to php executable.
   *
   * @return string Path to php executable
   */
  protected function getPhpCli()
  {
    if(!isset($this->phpCli))
    {
      $this->phpCli = sfToolkit::getPhpCli();
    }
    return $this->phpCli;
  }

  protected function getPresentationFor($application, $module, $action, $env = 'prod')
  {
    $file = tempnam(sys_get_temp_dir(), 'presentation');
    $rootDir  = $this->environment->get('sf_root_dir');

    file_put_contents($file, <<<EOF
<?php
// This is a separated process to send mail in the queue

define('SF_ROOT_DIR',    '{$rootDir}');
define('SF_APP',         '{$application}');
define('SF_ENVIRONMENT', '{$env}');
define('SF_DEBUG',       true);

\$app_config = SF_ROOT_DIR.DIRECTORY_SEPARATOR.'apps'.DIRECTORY_SEPARATOR.SF_APP.
               DIRECTORY_SEPARATOR.'config'.DIRECTORY_SEPARATOR.'config.php';

// disable error reporting, we need clean output
error_reporting(0);

require_once(\$app_config);

echo \sfContext::getInstance()->getController()->getPresentationFor('{$module}', '{$action}');

EOF
);

    ob_start();
    passthru(sprintf('%s %s 2>&1', escapeshellarg($this->getPhpCli()), escapeshellarg($file)), $return);
    $result = ob_get_clean();

    // remove the file
    unlink($file);

    return $result;
  }

}
