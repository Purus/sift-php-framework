<?php
/*
 * This file is part of the Sift PHP framework.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * Rotates an application log files.
 *
 * @package    Sift
 * @subpackage cli_task
 */
class sfCliLogRotateTask extends sfCliBaseTask
{
  /**
   * @see sfCliTask
   */
  protected function configure()
  {
    $this->addArguments(array(
      new sfCliCommandArgument('application', sfCliCommandArgument::REQUIRED, 'The application name'),
      new sfCliCommandArgument('env', sfCliCommandArgument::REQUIRED, 'The environment name'),
    ));

    $this->addOptions(array(
      new sfCliCommandOption('history', null, sfCliCommandOption::PARAMETER_OPTIONAL, 'The maximum number of old log files to keep'),
      new sfCliCommandOption('period', null, sfCliCommandOption::PARAMETER_OPTIONAL, 'The period in days'),
    ));

    $this->namespace = 'log';
    $this->name = 'rotate';
    $this->briefDescription = 'Rotates an application\'s log files';

    $scriptName = $this->environment->get('script_name');

    $this->detailedDescription = <<<EOF
The [log:rotate|INFO] task rotates application log files for a given
environment:

  [{$scriptName} log:rotate frontend dev|INFO]

You can specify a [period|COMMENT] or a [history|COMMENT] option:

  [{$scriptName} log:rotate frontend dev --history=10 --period=7|INFO]
EOF;
  }

  /**
   * @see sfCliTask
   */
  protected function execute($arguments = array(), $options = array())
  {
    $this->logSection($this->getFullName(), 'Rotating logs...');
    $this->rotate($arguments['application'], $arguments['env'], $options['period'], $options['history'], true);
    $this->logSection($this->getFullName(), 'Done.');
  }

  /**
   * Rotates log file.
   *
   * @param  string $app       Application name
   * @param  string $env       Enviroment name
   * @param  string $period    Period
   * @param  string $history   History
   * @param  bool   $override  Override
   */
  public function rotate($app, $env, $period = null, $history = null, $override = false)
  {
    $manager = new sfLogManager($this->environment->get('sf_log_dir'));
    $manager->rotate($app, $env, $period, $history, $override);
  }

}
