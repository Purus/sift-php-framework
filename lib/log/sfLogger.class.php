<?php
/*
 * This file is part of the Sift PHP framework.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * sfLogger manages all logging in Sift projects.
 *
 * sfLogger can be configured via the logging.yml configuration file.
 * Loggers can also be registered directly in the logging.yml configuration file.
 *
 * This level list is ordered by highest priority (sfLogger::EMERG) to lowest priority (sfLogger::DEBUG):
 *
 * - sfLogger::EMERG:   System is unusable
 * - sfLogger::ALERT:   Immediate action required
 * - sfLogger::CRIT:    Critical conditions
 * - sfLogger::ERR:     Error conditions
 * - sfLogger::WARNING: Warning conditions
 * - sfLogger::NOTICE:  Normal but significant
 * - sfLogger::INFO:    Informational
 * - sfLogger::DEBUG:   Debug-level messages
 *
 * @package    Sift
 * @subpackage log
 */
class sfLogger extends sfConfigurable implements sfILogger {

  /**
   * System is unusable
   */
  const EMERG = 0;

  /**
   * Immediate action required
   */
  const ALERT = 1;

  /**
   * Critical conditions
   */
  const CRIT = 2;

  /**
   * Error conditions
   */
  const ERR = 3;

  /**
   * Warning conditions
   */
  const WARNING = 4;

  /**
   * Normal but significant
   */
  const NOTICE = 5;

  /**
   * Informational
   */
  const INFO = 6;

  /**
   * Debug-level messages
   */
  const DEBUG = 7;

  /**
   * Array of loggers
   *
   * @var array
   */
  protected $loggers = array();

  /**
   * Default level
   *
   */
  protected $level = self::EMERG;

  /**
   * Log level map
   *
   * @var array
   */
  protected $levels = array(
    self::EMERG => 'emerg',
    self::ALERT => 'alert',
    self::CRIT => 'crit',
    self::ERR => 'err',
    self::WARNING => 'warning',
    self::NOTICE => 'notice',
    self::INFO => 'info',
    self::DEBUG => 'debug',
  );

  /**
   * Instance holder
   *
   * @var sfLogger
   */
  protected static $logger = null;

  /**
   * Returns the sfLogger instance.
   *
   * @return sfLogger sfLogger instance
   */
  public static function getInstance()
  {
    if(!self::$logger)
    {
      $class = __CLASS__;
      self::$logger = new $class();
    }
    return self::$logger;
  }

  /**
   * Constructs the object
   *
   * @param array $options
   */
  public function __construct($options = array())
  {
    parent::__construct($options);
    $this->initialize($options);
  }

  /**
   * Initializes the logger.
   *
   * @param array $options
   */
  public function initialize($options = array())
  {
    $this->loggers = array();
  }
  
  /**
   * Retrieves the log level for the current logger instance.
   *
   * @return string Log level
   */
  public function getLogLevel()
  {
    return $this->level;
  }

  /**
   * Sets a log level for the current logger instance.
   *
   * @param string Log level
   */
  public function setLogLevel($level)
  {
    $this->level = $level;
  }

  /**
   * Retrieves current loggers.
   *
   * @return array List of loggers
   */
  public function getLoggers()
  {
    return $this->loggers;
  }

  /**
   * Registers a logger.
   *
   * @param object The Logger object
   */
  public function registerLogger(sfILogger $logger)
  {
    $this->loggers[] = $logger;
  }

  /**
   * Logs a message.
   *
   * @param string Message
   * @param string Message priority
   */
  public function log($message, $priority = self::INFO)
  {
    if($this->getLogLevel() < $priority)
    {
      return;
    }

    foreach($this->loggers as $logger)
    {
      $logger->log((string) $message, $priority, is_string($priority) ? $priority : $this->levels[$priority]);
    }
  }

  /**
   * Sets an emerg message.
   *
   * @param string Message
   */
  public function emerg($message)
  {
    $this->log($message, self::EMERG);
  }

  /**
   * Sets an alert message.
   *
   * @param string Message
   */
  public function alert($message)
  {
    $this->log($message, self::ALERT);
  }

  /**
   * Sets a critical message.
   *
   * @param string Message
   */
  public function crit($message)
  {
    $this->log($message, self::CRIT);
  }

  /**
   * Sets an error message.
   *
   * @param string Message
   */
  public function err($message)
  {
    $this->log($message, self::ERR);
  }

  /**
   * Sets a warning message.
   *
   * @param string Message
   */
  public function warning($message)
  {
    $this->log($message, self::WARNING);
  }

  /**
   * Sets a notice message.
   *
   * @param string Message
   */
  public function notice($message)
  {
    $this->log($message, self::NOTICE);
  }

  /**
   * Sets an info message.
   *
   * @param string Message
   */
  public function info($message)
  {
    $this->log($message, self::INFO);
  }

  /**
   * Sets a debug message.
   *
   * @param string Message
   */
  public function debug($message)
  {
    $this->log($message, self::DEBUG);
  }

  /**
   * Returns the priority name given a priority class constant
   *
   * @param  integer $priority A priority class constant
   *
   * @return string  The priority name
   *
   * @throws sfException if the priority level does not exist
   */
  public function getPriorityName($priority)
  {
    if (!isset($this->levels[$priority]))
    {
      throw new sfException(sprintf('The priority level "%s" does not exist.', $priority));
    }

    return $this->levels[$priority];
  }

  /**
   * Executes the shutdown procedure.
   *
   * Cleans up the current logger instance.
   */
  public function shutdown()
  {
    foreach($this->loggers as $logger)
    {
      if(method_exists($logger, 'shutdown'))
      {
        $logger->shutdown();
      }
    }
    $this->loggers = array();
  }

}
