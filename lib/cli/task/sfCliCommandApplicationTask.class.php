<?php
/*
 * This file is part of the Sift PHP framework.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * Base class for tasks that depends on a sfCliCommandApplication object.
 *
 * @package    Sift
 * @subpackage cli_task
 */
abstract class sfCliCommandApplicationTask extends sfCliTask
{
  protected
    $commandApplication = null;
  
  /**
   * Sets the command application instance for this task.
   *
   * @param sfCliCommandApplication $commandApplication A sfCliCommandApplication instance
   */
  public function setCommandApplication(sfCliCommandApplication $commandApplication = null)
  {
    $this->commandApplication = $commandApplication;    
  }

  /**
   * @see sfTask
   */
  public function log($messages)
  {
    if (null === $this->commandApplication || $this->commandApplication->isVerbose())
    {
      parent::log($messages);
    }
  }

  /**
   * @see sfTask
   */
  public function logSection($section, $message, $size = null, $style = 'INFO')
  {
    if (null === $this->commandApplication || $this->commandApplication->isVerbose())
    {
      parent::logSection($section, $message, $size, $style);
    }
  }

  /**
   * Creates a new task object.
   *
   * @param  string $name The name of the task
   *
   * @return sfTask
   *
   * @throws LogicException If the current task has no command application
   */
  public function createTask($name)
  {
    if (null === $this->commandApplication)
    {
      throw new LogicException('Unable to create a task as no command application is associated with this task yet.');
    }

    $task = $this->commandApplication->getTaskToExecute($name);

    if ($task instanceof sfCliCommandApplicationTask)
    {
      $task->setCommandApplication($this->commandApplication);
    }

    return $task;
  }

  /**
   * Executes another task in the context of the current one.
   *
   * @param  string  $name      The name of the task to execute
   * @param  array   $arguments An array of arguments to pass to the task
   * @param  array   $options   An array of options to pass to the task
   *
   * @return Boolean The returned value of the task run() method
   *
   * @see createTask()
   */
  public function runTask($name, $arguments = array(), $options = array())
  {
    return $this->createTask($name)->run($arguments, $options);
  }
  
}
