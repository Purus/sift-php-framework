<?php
/*
 * This file is part of the Sift PHP framework.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * sfCliCommandApplication manages the lifecycle of a CLI application.
 *
 * @package    Sift
 * @subpackage cli
 */
abstract class sfCliCommandApplication {

  protected
    $project = null,
    $commandManager = null,
    $trace = false,
    $verbose = true,
    $nowrite = false,
    $name = 'Sift',
    $version = 'UNKNOWN',
    $tasks = array(),
    $currentTask = null,
    $dispatcher = null,
    $logger = null,
    $formatter = null,
    $environment = null;

  /**
   * Constructor.
   *
   * @param sfEventDispatcher $dispatcher   A sfEventDispatcher instance
   * @param sfCliFormatter       $formatter    A sfCliFormatter instance
   * @param array             $options      An array of options
   */
  public function __construct(sfCliTaskEnvironment $env, sfEventDispatcher $dispatcher = null, sfCliFormatter $formatter = null, sfConsoleLogger $logger = null)
  {
    $this->environment = $env;
    $this->dispatcher = null === $dispatcher ? new sfEventDispatcher() : $dispatcher;
    $this->formatter = null === $formatter ? $this->guessBestFormatter(STDOUT) : $formatter;
    $this->logger = null === $logger ? new sfConsoleLogger() : $logger;

    $this->fixCgi();

    $argumentSet = new sfCliCommandArgumentSet(array(
                      new sfCliCommandArgument('task', sfCliCommandArgument::REQUIRED, 'The task to execute'),
                   ));

    $optionSet = new sfCliCommandOptionSet(array(
                new sfCliCommandOption('--help', '-H', sfCliCommandOption::PARAMETER_NONE, 'Display this help message.'),
                new sfCliCommandOption('--quiet', '-q', sfCliCommandOption::PARAMETER_NONE, 'Do not log messages to standard output.'),
                new sfCliCommandOption('--trace', '-t', sfCliCommandOption::PARAMETER_NONE, 'Turn on invoke/execute tracing, enable full backtrace.'),
                new sfCliCommandOption('--version', '-V', sfCliCommandOption::PARAMETER_NONE, 'Display the program version.'),
                new sfCliCommandOption('--color', '', sfCliCommandOption::PARAMETER_NONE, 'Forces ANSI color output.'),
            ));

    $this->commandManager = new sfCliCommandManager($argumentSet, $optionSet);

    $this->bindToProject();

    $this->configure();

    $this->registerTasks();
  }

  /**
   * Binds to an existing project.
   *
   */
  public function bindToProject()
  {
    // load project
    if(file_exists($this->environment->get('sf_root_dir').'/lib/myProject.class.php'))
    {
      require_once $this->environment->get('sf_root_dir').'/lib/myProject.class.php';
      $projectClass = 'myProject';
    }
    else
    {
      $projectClass = 'sfGenericProject';
    }

    $this->project = new $projectClass($this->environment->getAll(), $this->dispatcher);
    $this->environment->add($this->project->getOptions());

    // bind project
    sfCore::bindProject($this->project);

    // initialize project autoload
    $this->project->initializeAutoload();
    $this->project->loadPlugins();
    $this->project->setupPlugins();

    $classLoader = new sfClassLoader();
    foreach($this->project->getPlugins() as $plugin)
    {
      $plugin->initializeAutoload($classLoader);
    }
    $classLoader->register();
  }

  /**
   * Configures the current command application.
   */
  abstract public function configure();

  /**
   * Returns an instance to current project
   *
   * @return sfProject
   */
  public function getProject()
  {
    return $this->project;
  }

  /**
   * Returns the formatter instance.
   *
   * @return sfCliFormatter The formatter instance
   */
  public function getFormatter()
  {
    return $this->formatter;
  }

  /**
   * Sets the formatter instance.
   *
   * @param sfCliFormatter The formatter instance
   */
  public function setFormatter(sfCliFormatter $formatter)
  {
    $this->formatter = $formatter;

    foreach($this->getTasks() as $task)
    {
      $task->setFormatter($formatter);
    }
  }

  public function clearTasks()
  {
    $this->tasks = array();
  }

  /**
   * Registers an array of task objects.
   *
   * If you pass null, this method will register all available tasks.
   *
   * @param array  $tasks  An array of tasks
   */
  public function registerTasks($tasks = null)
  {
    if(null === $tasks)
    {
      $tasks = $this->autodiscoverTasks();
    }

    foreach($tasks as $task)
    {
      $this->registerTask($task);
    }
  }

  /**
   * Registers a task object.
   *
   * @param sfCliTask $task An sfCliTask object
   */
  public function registerTask(sfCliTask $task)
  {
    if(isset($this->tasks[$task->getFullName()]))
    {
      throw new sfCliCommandException(sprintf('The task named "%s" in "%s" task is already registered by the "%s" task.', $task->getFullName(), get_class($task), get_class($this->tasks[$task->getFullName()])));
    }

    $this->tasks[$task->getFullName()] = $task;

    foreach($task->getAliases() as $alias)
    {
      if(isset($this->tasks[$alias]))
      {
        throw new sfCliCommandException(sprintf('A task named "%s" is already registered.', $alias));
      }

      $this->tasks[$alias] = $task;
    }
  }

  /**
   * Autodiscovers task classes.
   *
   * @return array An array of tasks instances
   */
  public function autodiscoverTasks()
  {
    $tasks = array();

    $classLoader = new sfClassLoader();

    foreach(get_declared_classes() as $class)
    {
      $r = new ReflectionClass($class);
      if($r->isSubclassOf('sfCliTask') && !$r->isAbstract())
      {
        $task = new $class($this->environment, $this->dispatcher, $this->formatter, $this->logger);
        // we have to setup autoload for a task
        $task->setupAutoload($classLoader);
        $tasks[] = $task;
      }
    }

    $classLoader->register();

    return $tasks;
  }

  /**
   * Returns all registered tasks.
   *
   * @return array An array of sfCliTask objects
   */
  public function getTasks()
  {
    return $this->tasks;
  }

  /**
   * Returns a registered task by name or alias.
   *
   * @param string $name The task name or alias
   *
   * @return sfCliTask An sfCliTask object
   */
  public function getTask($name)
  {
    if(!isset($this->tasks[$name]))
    {
      throw new sfCliCommandException(sprintf('The task "%s" does not exist.', $name));
    }

    return $this->tasks[$name];
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

    $this->currentTask = $this->getTaskToExecute($arguments['task']);

    $ret = $this->currentTask->runFromCLI($this->commandManager, $this->commandOptions);

    $this->currentTask = null;

    return $ret;
  }

  /**
   * Gets the name of the application.
   *
   * @return string The application name
   */
  public function getName()
  {
    return $this->name;
  }

  /**
   * Sets the application name.
   *
   * @param string $name The application name
   */
  public function setName($name)
  {
    $this->name = $name;
  }

  /**
   * Gets the name of the script which executes this task
   *
   * @return string The script name
   */
  public function getScriptName()
  {
    return $this->getEnvironmentVariable('script_name');
  }

  /**
   * Sets the script name.
   *
   * @param string $name The script name
   */
  public function setScriptName($name)
  {
    $this->environment->set('script_name', $name);
  }

  /**
   * Gets the application version.
   *
   * @return string The application version
   */
  public function getVersion()
  {
    return $this->version;
  }

  /**
   * Sets the application version.
   *
   * @param string $version The application version
   */
  public function setVersion($version)
  {
    $this->version = $version;
  }

  /**
   * Returns the long version of the application.
   *
   * @return string The long application version
   */
  public function getLongVersion()
  {
    return sprintf('%s version %s', $this->getName(), $this->formatter->format($this->getVersion(), 'INFO')) . "\n";
  }

  /**
   * Returns whether the application must be verbose.
   *
   * @return Boolean true if the application must be verbose, false otherwise
   */
  public function isVerbose()
  {
    return $this->verbose;
  }

  /**
   * Returns whether the application must activate the trace.
   *
   * @return Boolean true if the application must activate the trace, false otherwise
   */
  public function withTrace()
  {
    return $this->trace;
  }

  /**
   * Outputs a help message for the current application.
   */
  public function help()
  {
    $messages = array(
        $this->formatter->format('Usage:', 'COMMENT'),
        sprintf("  %s [options] task_name [arguments]\n", $this->getScriptName()),
        $this->formatter->format('Options:', 'COMMENT'),
    );

    foreach($this->commandManager->getOptionSet()->getOptions() as $option)
    {
      $messages[] = sprintf('  %-24s %s  %s', $this->formatter->format('--' . $option->getName(), 'INFO'), $option->getShortcut() ? $this->formatter->format('-' . $option->getShortcut(), 'INFO') : '  ', $option->getHelp()
      );
    }

    foreach($messages as $message)
    {
      $this->logger->log($message);
    }
  }

  /**
   * Parses and handles command line options.
   *
   * @param mixed $options The command line options
   */
  protected function handleOptions($options = null)
  {
    $this->commandManager->process($options);
    $this->commandOptions = $options;

    // the order of option processing matters

    if($this->commandManager->getOptionSet()->hasOption('color') && false !== $this->commandManager->getOptionValue('color'))
    {
      $this->setFormatter(new sfCliAnsiColorFormatter());
    }

    if($this->commandManager->getOptionSet()->hasOption('quiet') && false !== $this->commandManager->getOptionValue('quiet'))
    {
      $this->verbose = false;
    }

    if($this->commandManager->getOptionSet()->hasOption('trace') && false !== $this->commandManager->getOptionValue('trace'))
    {
      $this->verbose = true;
      $this->trace = true;
    }

    if($this->commandManager->getOptionSet()->hasOption('help') && false !== $this->commandManager->getOptionValue('help'))
    {
      $this->help();
      exit(0);
    }

    if($this->commandManager->getOptionSet()->hasOption('version') && false !== $this->commandManager->getOptionValue('version'))
    {
      echo $this->getLongVersion();
      exit(0);
    }
  }

  /**
   * Renders an exception.
   *
   * @param Exception $e An exception object
   */
  public function renderException($e)
  {
    $title = sprintf('  [%s]  ', get_class($e));
    $len = $this->strlen($title);
    $lines = array();
    foreach(explode("\n", $e->getMessage()) as $line)
    {
      $lines[] = sprintf('  %s  ', $line);
      $len = max($this->strlen($line) + 4, $len);
    }

    $messages = array(str_repeat(' ', $len));

    if($this->trace)
    {
      $messages[] = $title . str_repeat(' ', $len - $this->strlen($title));
    }

    foreach($lines as $line)
    {
      $messages[] = $line . str_repeat(' ', $len - $this->strlen($line));
    }

    $messages[] = str_repeat(' ', $len);

    fwrite(STDERR, "\n");
    foreach($messages as $message)
    {
      fwrite(STDERR, $this->formatter->format($message, 'ERROR', STDERR) . "\n");
    }
    fwrite(STDERR, "\n");

    if(null !== $this->currentTask && $e instanceof sfCliCommandArgumentsException)
    {
      fwrite(STDERR, $this->formatter->format(sprintf($this->currentTask->getSynopsis(), $this->getScriptName()), 'INFO', STDERR) . "\n");
      fwrite(STDERR, "\n");
    }

    if($this->trace)
    {
      fwrite(STDERR, $this->formatter->format("Exception trace:\n", 'COMMENT'));

      // exception related properties
      $trace = $e->getTrace();
      array_unshift($trace, array(
          'function' => '',
          'file' => $e->getFile() != null ? $e->getFile() : 'n/a',
          'line' => $e->getLine() != null ? $e->getLine() : 'n/a',
          'args' => array(),
      ));

      for($i = 0, $count = count($trace); $i < $count; $i++)
      {
        $class = isset($trace[$i]['class']) ? $trace[$i]['class'] : '';
        $type = isset($trace[$i]['type']) ? $trace[$i]['type'] : '';
        $function = $trace[$i]['function'];
        $file = isset($trace[$i]['file']) ? $trace[$i]['file'] : 'n/a';
        $line = isset($trace[$i]['line']) ? $trace[$i]['line'] : 'n/a';

        fwrite(STDERR, sprintf(" %s%s%s at %s:%s\n", $class, $type, $function, $this->formatter->format($file, 'INFO', STDERR), $this->formatter->format($line, 'INFO', STDERR)));
      }

      fwrite(STDERR, "\n");
    }
  }

  /**
   * Gets a task from a task name or a shortcut.
   *
   * @param  string  $name  The task name or a task shortcut
   *
   * @return sfCliTask A sfCliTask object
   */
  public function getTaskToExecute($name)
  {
    // namespace
    if(false !== $pos = strpos($name, ':'))
    {
      $namespace = substr($name, 0, $pos);
      $name = substr($name, $pos + 1);

      $namespaces = array();
      foreach($this->tasks as $task)
      {
        if($task->getNamespace() && !in_array($task->getNamespace(), $namespaces))
        {
          $namespaces[] = $task->getNamespace();
        }
      }
      $abbrev = $this->getAbbreviations($namespaces);

      if(!isset($abbrev[$namespace]))
      {
        throw new sfCliCommandException(sprintf('There are no tasks defined in the "%s" namespace.', $namespace));
      }
      else if(count($abbrev[$namespace]) > 1)
      {
        throw new sfCliCommandException(sprintf('The namespace "%s" is ambiguous (%s).', $namespace, implode(', ', $abbrev[$namespace])));
      }
      else
      {
        $namespace = $abbrev[$namespace][0];
      }
    }
    else
    {
      $namespace = '';
    }

    // name
    $tasks = array();
    foreach($this->tasks as $taskName => $task)
    {
      if($taskName == $task->getFullName() && $task->getNamespace() == $namespace)
      {
        $tasks[] = $task->getName();
      }
    }

    $abbrev = $this->getAbbreviations($tasks);
    if(isset($abbrev[$name]) && count($abbrev[$name]) == 1)
    {
      return $this->getTask($namespace ? $namespace . ':' . $abbrev[$name][0] : $abbrev[$name][0]);
    }

    // aliases
    $aliases = array();
    foreach($this->tasks as $taskName => $task)
    {
      if($taskName == $task->getFullName())
      {
        foreach($task->getAliases() as $alias)
        {
          $aliases[] = $alias;
        }
      }
    }

    $abbrev = $this->getAbbreviations($aliases);
    $fullName = $namespace ? $namespace . ':' . $name : $name;
    if(!isset($abbrev[$fullName]))
    {
      throw new sfCliCommandException(sprintf('Task "%s" is not defined.', $fullName));
    }
    else if(count($abbrev[$fullName]) > 1)
    {
      throw new sfCliCommandException(sprintf('Task "%s" is ambiguous (%s).', $fullName, implode(', ', $abbrev[$fullName])));
    }
    else
    {
      return $this->getTask($abbrev[$fullName][0]);
    }
  }

  protected function strlen($string)
  {
    if(!function_exists('mb_strlen'))
    {
      return strlen($string);
    }

    if(false === $encoding = mb_detect_encoding($string))
    {
      return strlen($string);
    }

    return mb_strlen($string, $encoding);
  }

  /**
   * Fixes php behavior if using cgi php.
   *
   * @see http://www.sitepoint.com/article/php-command-line-1/3
   */
  protected function fixCgi()
  {
    // handle output buffering
    @ob_end_flush();
    ob_implicit_flush(true);

    // PHP ini settings
    set_time_limit(0);
    ini_set('track_errors', true);
    ini_set('html_errors', false);
    ini_set('magic_quotes_runtime', false);

    if(false === strpos(PHP_SAPI, 'cgi'))
    {
      return;
    }

    // define stream constants
    define('STDIN', fopen('php://stdin', 'r'));
    define('STDOUT', fopen('php://stdout', 'w'));
    define('STDERR', fopen('php://stderr', 'w'));

    // change directory
    if(isset($_SERVER['PWD']))
    {
      chdir($_SERVER['PWD']);
    }

    // close the streams on script termination
    register_shutdown_function(create_function('', 'fclose(STDIN); fclose(STDOUT); fclose(STDERR); return true;'));
  }

  /**
   * Returns an array of possible abbreviations given a set of names.
   *
   * @see Text::Abbrev perl module for the algorithm
   */
  protected function getAbbreviations($names)
  {
    $abbrevs = array();
    $table = array();

    foreach($names as $name)
    {
      for($len = strlen($name) - 1; $len > 0; --$len)
      {
        $abbrev = substr($name, 0, $len);
        if(!array_key_exists($abbrev, $table))
        {
          $table[$abbrev] = 1;
        }
        else
        {
          ++$table[$abbrev];
        }

        $seen = $table[$abbrev];
        if($seen == 1)
        {
          // We're the first word so far to have this abbreviation.
          $abbrevs[$abbrev] = array($name);
        }
        else if($seen == 2)
        {
          // We're the second word to have this abbreviation, so we can't use it.
          // unset($abbrevs[$abbrev]);
          $abbrevs[$abbrev][] = $name;
        }
        else
        {
          // We're the third word to have this abbreviation, so skip to the next word.
          continue;
        }
      }
    }

    // Non-abbreviations always get entered, even if they aren't unique
    foreach($names as $name)
    {
      $abbrevs[$name] = array($name);
    }

    return $abbrevs;
  }

  /**
   * Returns true if the stream supports colorization.
   *
   * Colorization is disabled if not supported by the stream:
   *
   *  -  windows without ansicon
   *  -  non tty consoles
   *
   * @param  mixed  $stream  A stream
   *
   * @return Boolean true if the stream supports colorization, false otherwise
   */
  protected function isStreamSupportsColors($stream)
  {
    if(DIRECTORY_SEPARATOR == '\\')
    {
      return false !== getenv('ANSICON');
    }
    else
    {
      return function_exists('posix_isatty') && @posix_isatty($stream);
    }
  }

  /**
   * Guesses the best formatter for the stream.
   *
   * @param  mixed       $stream  A stream
   *
   * @return sfCliFormatter A formatter instance
   */
  protected function guessBestFormatter($stream)
  {
    return $this->isStreamSupportsColors($stream) ? new sfCliAnsiColorFormatter() : new sfCliFormatter();
  }

  /**
   * Returns associated environment
   *
   * @return sfCliTaskEnvironment
   */
  public function getEnvironment()
  {
    return $this->environment;
  }

  /**
   * Set environment
   *
   * @param sfCliTaskEnvironment $environment
   */
  public function setEnvironment(sfCliTaskEnvironment $environment)
  {
    $this->environment = $environment;
  }

  /**
   * Returns environment variable
   *
   * This is a shortcut for $this->getEnvironment()->get($variableName,$default);
   *
   * @param string $variableName Name of the variable
   * @param string $default Default value
   * @return mixed
   */
  public function getEnvironmentVariable($variableName, $default = null)
  {
    return $this->environment->get($variableName, $default);
  }

  /**
   * Sets environment variable
   *
   * This is a shortcut for $this->getEnvironment()->set($variableName,$value);
   *
   * @param string $variableName Name of the variable
   * @param mixed $value Value of the variable
   * @return void
   */
  public function setEnvironmentVariable($variableName, $value)
  {
    $this->environment->set($variableName, $value);
  }

}
