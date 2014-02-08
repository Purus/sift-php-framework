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
 * @package    Sift
 * @subpackage helper_debug
 */

/**
 * Logs message to the logger. Only if web debug is turned on.
 *
 * @param string $message Message to be logged
 * @param string $level   The log level
 * @param array  $context The context variables
 */
function debug_message($message, $level = 'info', array $context = array())
{
    if (sfConfig::get('sf_web_debug')) {
        return log_message($message, $level, $context);
    }
}

/**
 * Logs message to the logger
 *
 * @param string $message Message to be logged
 * @param string $level   The log level
 * @param array  $context The context variables
 */
function log_message($message, $level = 'info', array $context = array())
{
    if (sfConfig::get('sf_logging_enabled')) {
        sfLogger::getInstance()->log($message, $level, $context);
    }
}

if (!function_exists('dump')) {
    /**
     * Dumps variable to the output.
     *
     * @param mixed   $var The variable to dump
     * @param array   $options Array of options
     * @param boolean $echo Echo the output?
     */
    function dump($var, array $options = null, $echo = true)
    {
        $dir = dirname(__FILE__);
        $location = null;
        // we need to provide the location where was the dump() called
        foreach (debug_backtrace(PHP_VERSION_ID >= 50306 ? DEBUG_BACKTRACE_IGNORE_ARGS : false) as $item) {
            if (isset($item['file']) && strpos($item['file'], $dir) === 0) {
                continue;
            } elseif (!isset($item['file'], $item['line']) || !is_file($item['file'])) {
                break;
            } else {
                $lines = file($item['file']);
                $line = $lines[$item['line'] - 1];
                $location = array(
                    $item['file'],
                    $item['line'],
                    preg_match('#\w*dump(er::\w+)?\((.*)\)#i', $line, $match) ? $match[2] : $line
                );
                break;
            }
        }
        $options['location'] = $location;

        return sfDebugDumper::dump($var, $options, $echo);
    }
} else {
    log_message(
        'The dump() function already exists. Are you including Debug helper in production environment?',
        'warning'
    );
}
