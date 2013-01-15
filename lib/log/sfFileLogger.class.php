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
 * @author     Fabien Potencier <fabien.potencier@symfony-project.com>
 */
class sfFileLogger
{
  protected
    $fp = null;

  /**
   * Initializes the file logger.
   *
   * @param array Options for the logger
   */
  public function initialize($options = array())
  {
    if (!isset($options['file']))
    {
      throw new sfConfigurationException('File option is mandatory for a file logger');
    }

    if (!isset($options['date_format'])) 
    {
      $options['date_format'] = false;
    }

    if(!isset($options['date_prefix'])) 
    {
      $options['date_prefix'] = '_';
    }

    if($options['date_format'])
    {
      $file       = $options['file'];    
      $dateFormat = $options['date_format'];
      $filePrefix = substr($file, 0, strrpos($file, '.'));
      $fileSuffix = substr($file, strrpos($file, '.'), strlen($file));
      $options['file'] = $filePrefix . $options['date_prefix'] . date($dateFormat) . $fileSuffix;    
    }    
    
    $dir = dirname($options['file']);

    if (!is_dir($dir))
    {
      mkdir($dir, 0777, 1);
    }

    $fileExists = file_exists($options['file']);
    if (!is_writable($dir) || ($fileExists && !is_writable($options['file'])))
    {
      throw new sfFileException(sprintf('Unable to open the log file "%s" for writing', $options['file']));
    }

    $this->fp = fopen($options['file'], 'a');
    if (!$fileExists)
    {
      chmod($options['file'], 0666);
    }
  }

  /**
   * Logs a message.
   *
   * @param string Message
   * @param string Message priority
   * @param string Message priority name
   * @param string Application name (default to Sift)
   */
  public function log($message, $priority, $priorityName, $appName = 'Sift')
  {
    $line = sprintf("%s %s [%s] %s%s", strftime('%b %d %H:%M:%S'), $appName, $priorityName, $message, DIRECTORY_SEPARATOR == '\\' ? "\r\n" : "\n");

    flock($this->fp, LOCK_EX);
    fwrite($this->fp, $line);
    flock($this->fp, LOCK_UN);
  }

  /**
   * Executes the shutdown method.
   */
  public function shutdown()
  {
    if (is_resource($this->fp))
    {
      fclose($this->fp);
    }
  }
}
