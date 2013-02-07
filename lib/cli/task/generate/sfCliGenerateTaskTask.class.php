<?php
/*
 * This file is part of the Sift PHP framework.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * Creates a task skeleton
 *
 * @package    Sift
 * @subpackage cli_task
 */
class sfGenerateTaskTask extends sfCliGeneratorBaseTask
{
  /**
   * @see sfCliTask
   */
  protected function configure()
  {
    $this->addArguments(array(
      new sfCliCommandArgument('task_name', sfCliCommandArgument::REQUIRED, 'The task name (can contain namespace)'),
    ));

    $this->addOptions(array(
      new sfCliCommandOption('dir', null, sfCliCommandOption::PARAMETER_REQUIRED, 'The directory to create the task in', 'lib/cli/task'),
      new sfCliCommandOption('use-database', null, sfCliCommandOption::PARAMETER_REQUIRED, 'Whether the task needs model initialization to access database'),
      new sfCliCommandOption('brief-description', null, sfCliCommandOption::PARAMETER_REQUIRED, 'A brief task description (appears in task list)'),
    ));

    $this->namespace = 'generate';
    $this->name = 'task';
    $this->briefDescription = 'Creates a skeleton class for a new task';

    $scriptName = $this->environment->get('script_name');
    
    $this->detailedDescription = <<<EOF
The [generate:task|INFO] creates a new sfCliTask class based on the name passed as
argument:

  [{$scriptName} generate:task namespace:name|INFO]

The [namespaceNameTask.class.php|COMMENT] skeleton task is created under the [lib/cli/task/|COMMENT]
directory. Note that the namespace is optional.

If you want to create the file in another directory (relative to the project
root folder), pass it in the [--dir|COMMENT] option. This directory will be created
if it does not already exist.

  [{$scriptName} generate:task namespace:name --dir=plugins/myPlugin/lib/cli/task|INFO]

If you want the task to default to a connection other than [default|COMMENT], provide
the name of this connection with the [--use-database|COMMENT] option:

  [{$scriptName} generate:task namespace:name --use-database=main|INFO]

The [--use-database|COMMENT] option can also be used to disable database
initialization in the generated task:

  [{$scriptName} generate:task namespace:name --use-database=false|INFO]

You can also specify a description:

  [{$scriptName} generate:task namespace:name --brief-description="Does interesting things"|INFO]
EOF;
  }

  /**
   * @see sfCliTask
   */
  protected function execute($arguments = array(), $options = array())
  {
    $taskName = $arguments['task_name'];
    $taskNameComponents = explode(':', $taskName);
    $namespace = isset($taskNameComponents[1]) ? $taskNameComponents[0] : '';
    $name = isset($taskNameComponents[1]) ? $taskNameComponents[1] : $taskNameComponents[0];
    
    $taskClassName = sfInflector::camelize(str_replace('-', '_', ($namespace ? $namespace.ucfirst($name) : $name))).'Task';
    $taskClassName[0] = strtolower($taskClassName[0]);
    
    // Validate the class name
    if(!preg_match('/^[a-zA-Z_\x7f-\xff][a-zA-Z0-9_\x7f-\xff]*$/', $taskClassName))
    {
      throw new sfCliCommandException(sprintf('The task class name "%s" is invalid.', $taskClassName));
    }

    $briefDescription = $options['brief-description'];
    
    $useDatabase = sfToolkit::literalize($options['use-database']);
    
    if($useDatabase)
    {
      $skeletonFile = 'task_database.php';
    }
    else
    {
      $skeletonFile = 'task_simple.php';
    }
    
    $defaultConnection = 'default';

    $constants = array(
      'TASK_NAME' => $taskName,
      'NAME' => $name,
      'TASK_CLASS_NAME' => $taskClassName,
      'NAMESPACE' => $namespace,
      'BRIEF_DESCRIPTION' => $briefDescription,   
      'DEFAULT_CONNECTION' => $defaultConnection
    );
    
    if(is_readable($this->environment->get('sf_data_dir').'/skeleton/task/' . $skeletonFile))
    {
      $skeleton = $this->environment->get('sf_data_dir').'/skeleton/task/' . $skeletonFile;
    }
    else
    {
      $skeleton = $this->environment->get('sf_sift_data_dir').'/skeleton/task/' . $skeletonFile;
    }    

    // check that the task directory exists and that the task file doesn't exist
    if (!is_readable($this->environment->get('sf_root_dir').'/'.$options['dir']))
    {
      $this->getFilesystem()->mkdirs($this->environment->get('sf_root_dir') . '/' . $options['dir']);
    }

    $taskFile = $this->environment->get('sf_root_dir').'/'.$options['dir'].'/'.$taskClassName.'.class.php';
    if(is_readable($taskFile))
    {
      throw new sfCliCommandException(sprintf('A "%s" task already exists in "%s".', $taskName, $taskFile));
    }

    $this->logSection($this->getFullName(), sprintf('Creating "%s" task file', $taskFile));
    
    $this->getFilesystem()->copy($skeleton, $taskFile);
    $this->getFilesystem()->replaceTokens($taskFile, '##', '##', $constants);
    
  }
}
