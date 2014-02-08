<?php
/*
 * This file is part of the Sift PHP framework.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * sfLoggerBase provides basic functionality for loggers
 *
 * @package    Sift
 * @subpackage log
 */
abstract class sfLoggerBase extends sfConfigurable implements sfILogger
{
    /**
     * Log level map
     *
     * @var array
     */
    protected static $logLevelMap
        = array(
            sfILogger::EMERGENCY => 'emergency',
            sfILogger::ALERT     => 'alert',
            sfILogger::CRITICAL  => 'critical',
            sfILogger::ERROR     => 'error',
            sfILogger::WARNING   => 'warning',
            sfILogger::NOTICE    => 'notice',
            sfILogger::INFO      => 'info',
            sfILogger::DEBUG     => 'debug',
        );

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
        $this->log($message, sfILogger::WARNING, $context);
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
     * Returns the level name given a level class constant
     *
     * @param  integer $level A level class constant
     *
     * @return string  The level name
     * @throws sfException if the level level does not exist
     */
    public function getLevelName($level)
    {
        if (!isset(self::$logLevelMap[$level])) {
            throw new InvalidArgumentException(sprintf('The level level "%s" does not exist.', $level));
        }

        return self::$logLevelMap[$level];
    }

    /**
     * Returns the log level map
     *
     * @return array
     */
    public static function getLogLevelMap()
    {
        return self::$logLevelMap;
    }

    /**
     * Formats context values into the message placeholders.
     *
     * @param string $message The message
     * @param array  $context Array of context
     *
     * @return string
     */
    protected function formatMessage($message, array $context = array())
    {
        if (!count($context)) {
            return (string)$message;
        }

        // build a replacement array with braces around the context keys
        $replace = array();
        foreach ($context as $key => $val) {
            $replace['{' . $key . '}'] = $val;
        }

        // interpolate replacement values into the message and return
        return strtr((string)$message, $replace);
    }

}
