<?php
/*
 * This file is part of the Sift PHP framework.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * Logger interface
 *
 * @package    Sift
 * @subpackage log
 */
interface sfILogger extends sfIService
{
    /**
     * System is unusable
     */
    const EMERGENCY = 0;

    /**
     * Alias for EMERGENCY
     */
    const EMERG = 0;

    /**
     * Immediate action required
     */
    const ALERT = 1;

    /**
     * Critical conditions
     */
    const CRITICAL = 2;

    /**
     * Alias for CRITICAL
     *
     * @internal
     */
    const CRIT = 2;

    /**
     * Error conditions
     */
    const ERROR = 3;

    /**
     * Alias for ERROR
     */
    const ERR = 3;

    /**
     * Warning conditions
     */
    const WARNING = 4;

    /**
     * Alias for WARNING
     */
    const WARN = 4;

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
     * Extra context namespace
     *
     */
    const CONTEXT_EXTRA = '_extra_';

    /**
     * System is unusable.
     *
     * @param string $message
     * @param array  $context
     *
     * @return null
     */
    public function emergency($message, array $context = array());

    /**
     * Action must be taken immediately.
     *
     * Example: Entire website down, database unavailable, etc. This should
     * trigger the SMS alerts and wake you up.
     *
     * @param string $message
     * @param array  $context
     *
     * @return null
     */
    public function alert($message, array $context = array());

    /**
     * Critical conditions.
     *
     * Example: Application component unavailable, unexpected exception.
     *
     * @param string $message
     * @param array  $context
     *
     * @return null
     */
    public function critical($message, array $context = array());

    /**
     * Runtime errors that do not require immediate action but should typically
     * be logged and monitored.
     *
     * @param string $message
     * @param array  $context
     *
     * @return null
     */
    public function error($message, array $context = array());

    /**
     * Exceptional occurrences that are not errors.
     *
     * Example: Use of deprecated APIs, poor use of an API, undesirable things
     * that are not necessarily wrong.
     *
     * @param string $message
     * @param array  $context
     *
     * @return null
     */
    public function warning($message, array $context = array());

    /**
     * Normal but significant events.
     *
     * @param string $message
     * @param array  $context
     *
     * @return null
     */
    public function notice($message, array $context = array());

    /**
     * Interesting events.
     *
     * Example: User logs in, SQL logs.
     *
     * @param string $message
     * @param array  $context
     *
     * @return null
     */
    public function info($message, array $context = array());

    /**
     * Detailed debug information.
     *
     * @param string $message
     * @param array  $context
     *
     * @return null
     */
    public function debug($message, array $context = array());

    /**
     * Logs with an arbitrary level.
     *
     * @param string  $message The message to log
     * @param integer $level   The level
     * @param array   $context Arary of conxtextual parameters
     *
     * @return null
     */
    public function log($message, $level = sfILogger::INFO, array $context = array());

}
