<?php
/*
 * This file is part of the Sift PHP framework.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
 
/**
 * Displays help for a task.
 *
 * @package    Sift
 * @subpackage cli_task
 */
class sfCliHelpTask extends sfCliCommandApplicationTask
{
  /**
   * @see sfCliTask
   */
  protected function configure()
  {
    $this->addArguments(array(
      new sfCliCommandArgument('task_name', sfCliCommandArgument::OPTIONAL, 'The task name', 'help'),
    ));

    $this->addOptions(array(
      new sfCliCommandOption('xml', null, sfCliCommandOption::PARAMETER_NONE, 'To output help as XML'),
    ));

    $this->briefDescription = 'Displays help for a task';

    $scriptName = $this->environment->get('script_name');
    
    $this->detailedDescription = <<<EOF
The [help|INFO] task displays help for a given task:

  [{$scriptName} help test:all|INFO]

You can also output the help as XML by using the [--xml|COMMENT] option:

  [{$scriptName} help test:all --xml|INFO]
EOF;
  }

  /**
   * @see sfCliTask
   */
  protected function execute($arguments = array(), $options = array())
  {
    if (!isset($this->commandApplication))
    {
      throw new sfCliCommandException('You can only launch this task from the command line.');
    }

    $task = $this->commandApplication->getTask($arguments['task_name']);

    if ($options['xml'])
    {
      $this->outputAsXml($task);
    }
    else
    {
      $this->outputAsText($task);
    }
  }

  protected function outputAsText(sfCliTask $task)
  {
    $messages = array();

    $messages[] = $this->formatter->format('Usage:', 'COMMENT');
    $messages[] = $this->formatter->format(sprintf(' '.$task->getSynopsis(), null === $this->commandApplication ? '' : $this->commandApplication->getScriptName()))."\n";

    // find the largest option or argument name
    $max = 0;
    foreach ($task->getOptions() as $option)
    {
      $max = strlen($option->getName()) + 2 > $max ? strlen($option->getName()) + 2 : $max;
    }
    foreach ($task->getArguments() as $argument)
    {
      $max = strlen($argument->getName()) > $max ? strlen($argument->getName()) : $max;
    }
    $max += strlen($this->formatter->format(' ', 'INFO'));

    if ($task->getAliases())
    {
      $messages[] = $this->formatter->format('Aliases:', 'COMMENT').' '.$this->formatter->format(implode(', ', $task->getAliases()), 'INFO')."\n";
    }

    if ($task->getArguments())
    {
      $messages[] = $this->formatter->format('Arguments:', 'COMMENT');
      foreach ($task->getArguments() as $argument)
      {
        $default = null !== $argument->getDefault() && (!is_array($argument->getDefault()) || count($argument->getDefault())) ? $this->formatter->format(sprintf(' (default: %s)', is_array($argument->getDefault()) ? str_replace("\n", '', print_r($argument->getDefault(), true)): $argument->getDefault()), 'COMMENT') : '';
        $messages[] = sprintf(" %-${max}s %s%s", $this->formatter->format($argument->getName(), 'INFO'), $argument->getHelp(), $default);
      }

      $messages[] = '';
    }

    if ($task->getOptions())
    {
      $messages[] = $this->formatter->format('Options:', 'COMMENT');

      foreach ($task->getOptions() as $option)
      {
        $default = $option->acceptParameter() && null !== $option->getDefault() && (!is_array($option->getDefault()) || count($option->getDefault())) ? $this->formatter->format(sprintf(' (default: %s)', is_array($option->getDefault()) ? str_replace("\n", '', print_r($option->getDefault(), true)): $option->getDefault()), 'COMMENT') : '';
        $multiple = $option->isArray() ? $this->formatter->format(' (multiple values allowed)', 'COMMENT') : '';
        $messages[] = sprintf(' %-'.$max.'s %s%s%s%s', $this->formatter->format('--'.$option->getName(), 'INFO'), $option->getShortcut() ? sprintf('(-%s) ', $option->getShortcut()) : '', $option->getHelp(), $default, $multiple);
      }

      $messages[] = '';
    }

    if ($detailedDescription = $task->getDetailedDescription())
    {
      $messages[] = $this->formatter->format('Description:', 'COMMENT');

      $messages[] = ' '.implode("\n ", explode("\n", $detailedDescription))."\n";
    }

    $this->log($messages);
  }

  protected function outputAsXml(sfCliTask $task)
  {
    echo $task->asXml();
  }
}