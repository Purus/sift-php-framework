<?php
/*
 * This file is part of the Sift PHP framework.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * sfLogger manages the logging. sfLogger can be configured via the logging.yml configuration file.
 * Loggers can also be registered directly in the logging.yml configuration file.
 *
 * This level list is ordered by highest level (sfLogger::EMERGENCY) to lowest level (sfLogger::DEBUG):
 *
 * - sfILogger::EMERGENCY:   System is unusable
 * - sfILogger::ALERT:       Immediate action required
 * - sfILogger::CRITICAL:    Critical conditions
 * - sfILogger::ERROR:       Error conditions
 * - sfILogger::WARNING:     Warning conditions
 * - sfILogger::NOTICE:      Normal but significant
 * - sfILogger::INFO:        Informational
 * - sfILogger::DEBUG:       Debug-level messages
 *
 * @package    Sift
 * @subpackage log
 */
class sfLogger implements sfILogger
{
  /**
   * Array of loggers
   *
   * @var array
   */
  protected $loggers = array();

  /**
   * Default level
   *
   * @var integer
   */
  protected $level = sfILogger::EMERGENCY;

  /**
   * Instance holder
   *
   * @var sfLogger
   */
  protected static $logger;

  /**
   * Returns the sfLogger instance.
   *
   * @return sfLogger sfLogger instance
   */
  public static function getInstance()
  {
    if (!self::$logger) {
      $class = __CLASS__;
      self::$logger = new $class();
    }

    return self::$logger;
  }

  /**
   * Resets the singleton instance
   *
   */
  public static function resetInstance()
  {
    self::$logger = null;
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
   * @return null
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
   * @return null
   */
  public function registerLogger(sfILogger $logger)
  {
    $this->loggers[] = $logger;
  }

  /**
   * Logs a message to the registered loggers.
   *
   * @param string $message
   * @param integer $level The log level
   * @param array $context Array of contextual parameters
   * @return null
   */
  public function log($message, $level = sfILogger::INFO, array $context = array())
  {
    $level = $this->convertLevel($level);

    if ($this->getLogLevel() < $level) {
      return;
    }

    foreach ($this->loggers as $logger) {
      $logger->log($message, $level, $context);
    }
  }

  /**
   * Converts string levels like "info" to its corresponding level constant
   *
   * @param string|integer $level
   * @return integer
   */
  public function convertLevel($level)
  {
    if (is_string($level)) {
      $constant = sprintf('sfILogger::%s', strtoupper($level));
      if (defined($constant)) {
        return constant($constant);
      }
    }

    return $level;
  }

  /**
   * @see sfILogger
   */
  public function emergency($message, array $context = array())
  {
    $this->log($message, sfILogger::EMERGENCY, $context);
  }

  /**
   * @see emergency()
   */
  public function emerg($message, array $context = array())
  {
    $this->emergency($message, $context);
  }

  /**
   * @see sfILogger
   */
  public function alert($message, array $context = array())
  {
    $this->log($message, sfILogger::ALERT, $context);
  }

  /**
   * @see sfILogger
   */
  public function critical($message, array $context = array())
  {
    $this->log($message, sfILogger::CRITICAL, $context);
  }

  /**
   * Alias for critical
   *
   * @see critical()
   */
  public function crit($message, array $context = array())
  {
    $this->critical($message, $context);
  }

  /**
   * @see sfILogger
   */
  public function error($message, array $context = array())
  {
    $this->log($message, sfILogger::ERROR, $context);
  }

  /**
   * Alias for err
   *
   * @see error()
   */
  public function err($message, array $context = array())
  {
    $this->error($message, $context);
  }

  /**
   * @see sfILogger
   */
  public function warning($message, array $context = array())
  {
    $this->log($message,sfILogger::WARNING, $context);
  }

  /**
   * Alias for warning
   *
   * see warning()
   */
  public function warn($message, array $context = array())
  {
    $this->warning($message, $context);
  }

  /**
   * @see sfILogger
   */
  public function notice($message, array $context = array())
  {
    $this->log($message, sfILogger::NOTICE, $context);
  }

  /**
   * @see sfILogger
   */
  public function info($message, array $context = array())
  {
    $this->log($message, sfILogger::INFO, $context);
  }

  /**
   * @see sfILogger
   */
  public function debug($message, array $context = array())
  {
    $this->log($message, sfILogger::DEBUG, $context);
  }

  /**
   * Clears the registered loggers
   *
   * @return sfLogger
   */
  public function clear()
  {
    $this->loggers = array();

    return $this;
  }

  /**
   * Executes the shutdown procedure.
   *
   * Cleans up the current logger instance.
   */
  public function shutdown()
  {
    foreach ($this->loggers as $logger) {
      $logger->shutdown();
    }
    $this->clear();
  }

}
