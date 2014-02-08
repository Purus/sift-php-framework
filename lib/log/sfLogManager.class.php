<?php
/*
 * This file is part of the Sift PHP framework.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * Log manager
 *
 * @package    Sift
 * @subpackage log
 */
class sfLogManager extends sfConfigurable
{

  /**
   * Default options
   *
   * @var array
   */
  protected $defaultOptions = array(
    // The default period to rotate logs in days
    'period' => 7,
    // The default number of log histories to store, one history is created for every period
    'history' => 10,
    // Separator of application name and environment in the file name
    'app_env_separator' => '_',
    // directory mode
    'dir_mode' => 0777,
    // date format for rotated logs
    'date_format' => 'Y_m_d',
    'override' => false,
    'date_prefix' => '_',
    // log application while rotating logs?
    'lock' => true
  );

  /**
   * Directory where do the logs live
   *
   * @var string
   */
  protected $logDir;

  /**
   * Constructs the manager
   *
   * @param string $logDir Log directory
   * @param array $options Array of options
   * @throws InvalidArgumentException If log directory does not exist
   */
  public function __construct($logDir, $options = array())
  {
    if(!is_dir($logDir))
    {
      throw new InvalidArgumentException(sprintf('Directory "%s" does not exist.', $logDir));
    }

    $this->logDir = $logDir;

    parent::__construct($options);
  }

  /**
   * Rotates log file.
   *
   * @param string Application name
   * @param string Enviroment name
   * @param string Period
   * @param string History
   * @param boolean Override
   */
  public function rotate($app, $env, $period = null, $history = null, $override = false)
  {
    if(is_null($period))
    {
      $period = $this->getOption('period');
    }

    if(is_null($history))
    {
      $history = $this->getOption('history');
    }

    // get todays date
    $today = date($this->getOption('date_format'));

    $logFile = sprintf('%s%s%s', $app, $this->getOption('app_env_separator'), $env);

    // check history folder exists
    if(!is_dir($this->logDir.'/history'))
    {
      mkdir($this->logDir.'/history', $this->getOption('dir_mode'), true);
    }

    $name = sprintf('%s%s*.log', $logFile, $this->getOption('date_separator'));

    // determine date of last rotation
    $logs = sfFinder::type('file')
              ->ignoreVersionControl()
              ->maxDepth(1)->name($name)
              ->in($this->logDir.'/history/');

    usort($logs, array($this, 'sort'));

    $recentlog = is_array($logs) ? array_pop($logs) : null;

    if($recentlog)
    {
      // calculate date to rotate logs on
      $last_rotated_on = filemtime($recentlog);
      $rotate_on = date($this->getOption('date_format'), strtotime('+ '.$period.' days', $last_rotated_on));
    }
    else
    {
      // no rotation has occured yet
      $rotate_on = null;
    }

    // if rotate log on date doesn't exist, or that date is today, then rotate the log
    if (!$rotate_on || ($rotate_on == $today) || $override)
    {
      if($this->getOption('lock') && ($dataDir = sfConfig::get('sf_data_dir')))
      {
        $lockFile = $dataDir .'/'.$app.'_'.$env.'.lck';
        touch($lockFile);
        chmod($lockFile, 0777);
      }

      // find all logs for the application
      $allLogs = sfFinder::type('file')
                  ->ignoreVersionControl()
                  ->maxDepth(0)->name($name)
                  ->in($this->logDir);

      // loop all logs and move them to history
      foreach($allLogs as $logFile)
      {
        $target = $this->logDir . '/history/'. basename($logFile);

        // we have a date information in the filename
        if(!preg_match($this->getRegex($this->getOption('date_format')), $logFile))
        {
          $target = $this->logDir.'/history/'.$this->generateFileName(basename($logFile),
              $this->getOption('date_prefix'), $today);
        }

        if(file_exists($target))
        {
          // append log to existing rotated log
          $handle = fopen($target, 'a');
          $append = file_get_contents($logFile);
          fwrite($handle, $append);
          fclose($handle);
        }
        else
        {
          // copy log
          $fileMTime = filemtime($logFile);
          copy($logFile, $target);
          touch($target, $fileMTime);
        }

        unlink($logFile);
      }

      // get all log history files for this application and environment
      $new_logs = sfFinder::type('file')
          ->ignoreVersionControl()
          ->maxdepth(0)
          ->name($name)
          ->in($this->logDir.'/history');

      // sort by filemtime
      usort($new_logs, array($this, 'sort'));

      // if the number of logs in history exceeds history then remove the oldest log
      if(count($new_logs) > $history)
      {
        // how many to delete?
        for($i = 0, $diff = count($new_logs) - $history; $i < $diff; $i++)
        {
          unlink($new_logs[$i]);
        }
      }

      if($this->getOption('lock') && $dataDir)
      {
        @unlink($lockFile);
      }
    }
  }

  /**
   * Sort files based on their modification time
   *
   * @param string $a Path to a file A
   * @param string $b Path to a file B
   * @return int
   */
  protected function sort($a, $b)
  {
    $mTimeA = filemtime($a);
    $mTimeB = filemtime($b);

    if($mTimeA == $mTimeB)
    {
      return 0;
    }

    return $mTimeA > $mTimeB ? 1 : -1;
  }

  /**
   * Returns a regular expression for given $dateFormat
   * @param string $dateFormat
   * @return string
   */
  protected function getRegex($dateFormat)
  {
    return sfDateFormatRegexGenerator::getInstance()->generateRegex($dateFormat);
  }

  /**
   * Generates file name with date appended
   * @param string $file
   * @param type $dateFormat
   * @return string
   */
  protected function generateFileName($file, $datePrefix, $date)
  {
    $filePrefix = substr($file, 0, strrpos($file, '.'));
    $fileSuffix = substr($file, strrpos($file, '.'), strlen($file));
    return $filePrefix . $datePrefix . ($date) . $fileSuffix;
  }

}
