<?php
/*
 * This file is part of the Sift PHP framework.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * Logs message to the logger. Only if web debug is turned on.
 *
 * @param string $message Message to be logged
 * @param string $priority Piority
 */
function debug_message($message, $priority = 'info')
{
  if(sfConfig::get('sf_web_debug'))
  {
    return log_message($message, $priority);
  }
}

/**
 * Logs message to the logger
 *
 * @param string $message Message to be logged
 * @param string $priority Priority
 */
function log_message($message, $priority = 'info')
{
  if(sfConfig::get('sf_logging_enabled'))
  {
    sfLogger::getInstance()->log($message, $priority);
  }
}

/**
 * Dumps $var using sfDebug
 *
 * @param mixed $var Variable to dump
 * @param boolean $exit Exit after dumping the variable?
 */
function dump($var, $exit = false)
{
  return sfDebug::dump($var, $exit, debug_backtrace());
}
