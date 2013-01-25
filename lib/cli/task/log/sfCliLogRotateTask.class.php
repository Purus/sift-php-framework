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
  /** the default period to rotate logs in days */
  const DEF_PERIOD = 7;

  /** the default number of log historys to store, one history is created for every period */
  const DEF_HISTORY = 10;

  /**
   * @see sfTask
   */
  protected function configure()
  {
    $this->addArguments(array(
      new sfCliCommandArgument('application', sfCliCommandArgument::REQUIRED, 'The application name'),
      new sfCliCommandArgument('env', sfCliCommandArgument::REQUIRED, 'The environment name'),
    ));

    $this->addOptions(array(
      new sfCliCommandOption('history', null, sfCliCommandOption::PARAMETER_REQUIRED, 'The maximum number of old log files to keep', self::DEF_HISTORY),
      new sfCliCommandOption('period', null, sfCliCommandOption::PARAMETER_REQUIRED, 'The period in days', self::DEF_PERIOD),
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
   * @see sfTask
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
   *
   * @author Joe Simms
   * @todo Use sfLogManager!
   */
  public function rotate($app, $env, $period = null, $history = null, $override = false)
  {
    $logfile = $app.'_'.$env;
    $logdir = $this->environment->get('sf_log_dir');

    // set history and period values if not passed to default values
    $period = isset($period) ? $period : self::DEF_PERIOD;
    $history = isset($history) ? $history : self::DEF_HISTORY;

    // get todays date
    $today = date('Ymd');

    // check history folder exists
    if (!is_dir($logdir.'/history'))
    {
      $this->getFilesystem()->mkdirs($logdir.'/history');
    }

    // determine date of last rotation
    $logs = sfFinder::type('file')->maxdepth(1)->name($logfile.'_*.log')->sort_by_name()->in($logdir.'/history');
    $recentlog = is_array($logs) ? array_pop($logs) : null;

    if ($recentlog)
    {
      // calculate date to rotate logs on
      $lastRotatedOn = filemtime($recentlog);
      $rotateOn = date('Ymd', strtotime('+ '.$period.' days', $lastRotatedOn));
    }
    else
    {
      // no rotation has occured yet
      $rotateOn = null;
    }

    $srcLog = $logdir.'/'.$logfile.'.log';
    $destLog = $logdir.'/history/'.$logfile.'_'.$today.'.log';

    // if rotate log on date doesn't exist, or that date is today, then rotate the log
    if (!$rotateOn || ($rotateOn == $today) || $override)
    {
      // create a lock file
      $lockFile = $this->environment->get('sf_root_dir').'/'.$app.'_'.$env.'-cli.lck';
      $this->getFilesystem()->touch($lockFile);

      // change mode so the web user can remove it if we die
      $this->getFilesystem()->chmod($lockFile, 0777);

      // if log file exists rotate it
      if(file_exists($srcLog))
      {
        // check if the log file has already been rotated today
        if (file_exists($destLog))
        {
          // append log to existing rotated log
          $handle = fopen($destLog, 'a');
          $append = file_get_contents($srcLog);

          $this->logSection('file+', $destLog);
          fwrite($handle, $append);
        }
        else
        {
          // copy log
          $this->getFilesystem()->copy($srcLog, $destLog);
        }

        // remove the log file
        $this->getFilesystem()->remove($srcLog);

        // get all log history files for this application and environment
        $newLogs = sfFinder::type('file')->maxdepth(1)->name($logfile.'_*.log')->sort_by_name()->in($logdir.'/history');

        // if the number of logs in history exceeds history then remove the oldest log
        if (count($newLogs) > $history)
        {
          $this->getFilesystem()->remove($newLogs[0]);
        }
      }

      // release lock
      $this->getFilesystem()->remove($lockFile);
    }
  }
}
