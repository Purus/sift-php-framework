<?php
/*
 * This file is part of the Sift PHP framework.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * Debugging helpers
 *
 * @package Sift
 * @subpackage helper_debug
 */

/**
 * Logs message to the logger. Only if web debug is turned on.
 *
 * @param string $message Message to be logged
 * @param string $level The log level
 * @param array $context The context variables
 */
function debug_message($message, $level = 'info', array $context = array())
{
  if(sfConfig::get('sf_web_debug'))
  {
    return log_message($message, $level, $context);
  }
}

/**
 * Logs message to the logger
 *
 * @param string $message Message to be logged
 * @param string $level The log level
 * @param array $context The context variables 
 */
function log_message($message, $level = 'info', array $context = array())
{
  if(sfConfig::get('sf_logging_enabled'))
  {
    sfLogger::getInstance()->log($message, $level, $context);
  }
}

/**
 * Dumps the variable
 *
 * @param mixed $var Variable to dump
 * @param boolean $exit Exit after dumping the variable?
 * @return string The dump result
 */
function dump($var, $exit = false)
{
  return sfDebug::dump($var, $exit, debug_backtrace());
}
