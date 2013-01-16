<?php
/*
 * This file is part of the Sift PHP framework.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * Log to files
 *
 * @package    Sift
 * @subpackage log
 */
class sfFileLogger extends sfLogger
{
  /**
   * Default options
   * 
   * @var array 
   */
  protected $defaultOptions = array(
    'type' => 'Sift',
    'format' => '%time% %type% [%priority%] %message%%EOL%',
    'time_format' => '%b %d %H:%M:%S',  
    'dir_mode' => 0777,
    'file_mode' => 0666
  );
  
  /**
   * File pointer
   * 
   * @var resource 
   */
  protected $fp;
  
  /**
   * Initializes the file logger.
   *
   * @param array Options for the logger
   */
  public function initialize($options = array())
  {
    $file = $this->getOption('file');
    
    if(!$file)
    {
      throw new sfConfigurationException('You must provide a "file" parameter for this logger.');
    }

    $dir = dirname($this->getOption('file'));    
    if(!is_dir($dir))
    {
      mkdir($dir, $this->getOption('dir_mode'), true);
    }

    $fileExists = file_exists($file);
    if (!is_writable($dir) || ($fileExists && !is_writable($file)))
    {
      throw new sfFileException(sprintf('Unable to open the log file "%s" for writing.', $file));
    }

    $this->fp = fopen($file, 'a');
    if(!$fileExists)
    {
      chmod($file, $this->getOption('file_mode'));
    }

    return parent::initialize($options);    
  }

  /**
   * Logs a message.
   *
   * @param string Message
   * @param string Message priority
   * @param string Message priority name
   * @param string Application name (default to Sift)
   */
  public function log($message, $priority = SF_LOG_INFO)
  {
    flock($this->fp, LOCK_EX);
    fwrite($this->fp, strtr($this->getOption('format'), array(
      '%type%'     => $this->getOption('type'),
      '%message%'  => $message,
      '%time%'     => strftime($this->getOption('time_format')),
      '%priority%' => $this->getPriorityName($priority),
      '%EOL%'      => PHP_EOL,
    )));
    flock($this->fp, LOCK_UN);
  }
  
  /**
   * Executes the shutdown method.
   */
  public function shutdown()
  {
    if(is_resource($this->fp))
    {
      fclose($this->fp);
    }
  }
  
}
