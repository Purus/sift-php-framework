<?php

/*
 * This file is part of the Sift PHP framework.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * sfVarLogger logs messages within its instance for later use.
 *
 * @package    Sift
 * @subpackage log
 */
class sfVarLogger extends sfLoggerBase
{
  protected $logs = array(),
    $xdebugLogging = false;

  /**
   * Array of default options
   *
   * @var array
   */
  protected $defaultOptions = array(
    // xdebug logging
    'xdebug_logging' => true,
    // backtrace logging
    'with_backtrace' => false
  );

  /**
   * Initializes this logger.
   *
   * Available options:
   *
   * - xdebug_logging: Whether to add xdebug trace to the logs (false by default).
   *
   * @param  sfEventDispatcher $dispatcher  A sfEventDispatcher instance
   * @param  array             $options     An array of options.
   *
   * @return Boolean           true, if initialization completes successfully, otherwise false.
   */
  public function __construct($options = array())
  {
    parent::__construct($options);

    $this->xdebugLogging = $this->getOption('xdebug_logging');

    // disable xdebug when an HTTP debug session exists (crashes Apache, see #2438)
    if (isset($_GET['XDEBUG_SESSION_START']) || isset($_COOKIE['XDEBUG_SESSION'])) {
      $this->xdebugLogging = false;
    }
  }

  /**
   * Gets the logs.
   *
   * Each log entry has the following attributes:
   *
   *  * level
   *  * time
   *  * message
   *  * type
   *  * debugStack
   *
   * @return array An array of logs
   */
  public function getLogs()
  {
    return $this->logs;
  }

  /**
   * Returns all the types in the logs.
   *
   * @return array An array of types
   */
  public function getTypes()
  {
    $types = array();
    foreach ($this->logs as $log) {
      if (!in_array($log['type'], $types)) {
        $types[] = $log['type'];
      }
    }

    sort($types);

    return $types;
  }

  /**
   * Returns all the priorities in the logs.
   *
   * @return array An array of priorities
   */
  public function getLevels()
  {
    $priorities = array();
    foreach ($this->logs as $log) {
      if (!in_array($log['level'], $priorities)) {
        $priorities[] = $log['level'];
      }
    }

    sort($priorities);

    return $priorities;
  }

  /**
   * Returns the highest level in the logs.
   *
   * @return integer The highest level
   */
  public function getHighestLevel()
  {
    $level = 1000;
    foreach ($this->logs as $log) {
      if ($log['level'] < $level) {
        $level = $log['level'];
      }
    }

    return $level;
  }

  /**
   * Logs a message.
   *
   * @param string $message   Message
   * @param string $level  Message level
   */
  public function log($message, $level = sfILogger::INFO, array $context = array())
  {
    // get log type in {}
    $type = 'sfOther';
    if (preg_match('/^\s*{([^}]+)}\s*(.+?)$/s', $message, $matches)) {
      $type = $matches[1];
      $message = $matches[2];
    }

    $this->logs[] = array(
      'level' => $level,
      'level_name' => $this->getLevelName($level),
      'time' => time(),
      'message_formatted' => $this->formatMessage($message, $context),
      'message' => $message,
      'context' => $context,
      'type' => $type,
      'debug_backtrace' => $this->getOption('with_backtrace') ? $this->getDebugBacktrace() : array(),
    );
  }

  /**
   * Returns the debug stack.
   *
   * @return array
   * @see debug_backtrace()
   */
  protected function getDebugBacktrace()
  {
    // if we have xdebug and dev has not disabled the feature, add some stack information
    if (!$this->xdebugLogging || !function_exists('debug_backtrace')) {
      return array();
    }
    // remove first item, since its this function
    return array_slice(debug_backtrace(), 0);
  }

  public function shutdown()
  {
  }

}
