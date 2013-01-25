<?php
/*
 * This file is part of the Sift PHP framework.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * sfCliRootCommandApplication manages the Sift CLI.
 *
 * @package    Sift
 * @subpackage cli
 */
class sfCliRootCommandApplication extends sfCliCommandApplication
{
  protected $taskFiles = array();
  
  public function __construct(sfCliTaskEnvironment $env, 
          sfEventDispatcher $dispatcher = null, sfCliFormatter $formatter = null, sfConsoleLogger $logger = null)
  {
    parent::__construct($env, $dispatcher, $formatter, $logger);
  }
  
  /**
   * Configures the current command application.
   */
  public function configure()
  {
    $this->setName('Sift');
    $this->setScriptName('./sift');  
    $this->setVersion(file_get_contents($this->environment->get('sf_sift_lib_dir'). DIRECTORY_SEPARATOR . 'VERSION'));
    $this->loadTasks();
  }

  /**
   * Runs the current application.
   *
   * @param mixed $options The command line options
   *
   * @return integer 0 if everything went fine, or an error code
   */
  public function run($options = null)
  {
    $this->handleOptions($options);
    $arguments = $this->commandManager->getArgumentValues();

    if (!isset($arguments['task']))
    {
      $arguments['task'] = 'list';
      $this->commandOptions .= $arguments['task'];
    }

    $this->currentTask = $this->getTaskToExecute($arguments['task']);

    if ($this->currentTask instanceof sfCliCommandApplicationTask)
    {
      $this->currentTask->setCommandApplication($this);
    }
    
    $ret = $this->currentTask->runFromCLI($this->commandManager, $this->commandOptions);

    $this->currentTask = null;

    return $ret;
  }

  /**
   * Loads all available tasks.
   *
   * Looks for tasks in the symfony core, the current project and all project plugins.
   *
   * @param sfProjectConfiguration $configuration The project configuration
   */
  public function loadTasks()
  {
    // core tasks
    $dirs = array($this->environment->get('sf_sift_lib_dir').'/cli/task');

    // plugin tasks
    foreach(glob(getcwd() . '/plugins/*') as $path)
    {
      if(is_dir($taskPath = $path.'/lib/cli/task'))
      {
        $dirs[] = $taskPath;
      }
      if(is_dir($taskPath = $path.'/lib/task'))
      {
        $dirs[] = $taskPath;
      }
    }

    if(is_dir($taskPath = $this->environment->get('sf_root_dir').'/lib/cli/task'))
    {
      $dirs[] = $taskPath;      
    }
    
    // Backward compatibility
    if(is_dir($taskPath = $this->environment->get('sf_root_dir').'/lib/task'))
    {
      $dirs[] = $taskPath;      
    }

    $dirs = array_unique($dirs);
    
    $finder = sfFinder::type('file')->name('*Task.class.php');
    
    foreach($finder->in($dirs) as $file)   
    {
      $this->taskFiles[basename($file, '.class.php')] = $file;
    }
    
    // register local autoloader for tasks
    spl_autoload_register(array($this, 'autoloadTask'));

    // require tasks
    foreach($this->taskFiles as $task => $file)
    {
      // forces autoloading of each task class
      class_exists($task, true);
    }
    
    // unregister local autoloader
    spl_autoload_unregister(array($this, 'autoloadTask'));
  }

  /**
   * Autoloads a task class
   *
   * @param  string  $class  The task class name
   *
   * @return boolean
   */
  public function autoloadTask($class)
  {
    if (isset($this->taskFiles[$class]))
    {
      require_once $this->taskFiles[$class];

      return true;
    }

    return false;
  }

  /**
   * @see sfCommandApplication
   */
  public function getLongVersion()
  {
    return sprintf('%s version %s (%s)', 
            'Sift', 
            $this->formatter->format($this->getVersion(), 'INFO'), 
            $this->environment->get('sf_sift_lib_dir'))."\n";
  }
}
