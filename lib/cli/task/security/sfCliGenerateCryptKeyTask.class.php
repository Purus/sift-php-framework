<?php
/*
 * This file is part of the Sift PHP framework.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * Generates the crypt key for an application.
 *
 * @package    Sift
 * @subpackage cli_task
 */
class sfCliGenerateCryptKeyTask extends sfCliBaseTask
{
  /**
   * @see sfTask
   */
  protected function configure()
  {
    $this->addArguments(array(
      new sfCliCommandArgument('application', sfCliCommandArgument::OPTIONAL, 'The application name'),
    ));

    $this->namespace = 'security';
    $this->name = 'generate-crypt-key';
    $this->aliases = array('gencryptkey');
    
    $this->briefDescription = 'Generates crypt key (requires Openssl)';

    $scriptName = $this->environment->get('script_name');
    
    $this->detailedDescription = <<<EOF
The [security:generate-crypt-key|INFO] task generates security key for project or application.
(Requires Openssl)

  [{$scriptName} security:generate-crypt-key|INFO]

You can specify an [application|COMMENT]:

  [{$scriptName} security:generate-crypt-key front|INFO]
EOF;
  }

  /**
   * @see sfTask
   */
  protected function execute($arguments = array(), $options = array())
  {
    $filesystem = $this->getFilesystem();

    $app = false;
    if(isset($arguments['application']))
    {
      $this->checkAppExists($arguments['application']);
      $app = $arguments['application'];
    }  
    
    $this->logSection($this->getFullName(), 'Generating crypt key...');
    
    try
    {
      $command = 'openssl rand -base64 2048';
      $commandOutput = $filesystem->execute($command);
      $newKey = $commandOutput[0];
    }
    catch(RuntimeException $e)
    {
      throw $e;
    }
    
    // safety check
    if(strlen(base64_decode($newKey)) !== 2048)
    {
      throw new sfException('Generated key has incorrect size, aborting.');
    }    

    // we are generating key for application
    if($app)
    {
      $keyFilePath = $this->environment->get('sf_apps_dir') . 
                     '/' . $app . '/' . $this->environment->get('sf_config_dir_name')
                     . '/crypt.key';
    } 
    else
    {
      $keyFilePath = $this->environment->get('sf_root_dir') . '/' . 
                    $this->environment->get('sf_config_dir_name') . '/crypt.key';
    }

    $keyFileExists = file_exists($keyFilePath);

    if($keyFileExists)
    {
      $backupFileName = 'crypt.key.' . time() . '.backup';
      $this->logSection($this->getFullName(), 'Key already exists. Moving old to backup.');    
      $filesystem->rename($keyFilePath, dirname($keyFilePath).'/'.$backupFileName);    
    }
  
    $keyFileHandle = fopen($keyFilePath, 'w');
    fwrite($keyFileHandle, $newKey);
    fclose($keyFileHandle);
    
    // change permission of newly created file to read for everyone
    $filesystem->chmod($keyFilePath, 0444);     
    
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
   **/
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
