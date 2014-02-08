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
class sfFileLogger extends sfLoggerBase
{
    /**
     * Default options
     *
     * @var array
     */
    protected $defaultOptions
        = array(
            'type'        => 'Sift',
            'format'      => '%time% %type% [%level%] %message% [%extra%]%EOL%',
            'time_format' => 'M d H:i:s', // format for date() function
            'dir_mode'    => 0777,
            'file_mode'   => 0666,
            'date_format' => 'Y_m_d',
            'date_prefix' => '_'
        );

    /**
     * Array of required options
     *
     * @var array
     */
    protected $requiredOptions
        = array(
            'file'
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
    public function setup()
    {
        $file = $this->getOption('file');

        if ($dateFormat = $this->getOption('date_format')) {
            $filePrefix = substr($file, 0, strrpos($file, '.'));
            $fileSuffix = substr($file, strrpos($file, '.'), strlen($file));
            $file = $filePrefix . $this->getOption('date_prefix') . date($dateFormat) . $fileSuffix;
        }

        $dir = dirname($file);

        if (!is_dir($dir)) {
            mkdir($dir, $this->getOption('dir_mode'), true);
        }

        $fileExists = file_exists($file);
        if (!is_writable($dir) || ($fileExists && !is_writable($file))) {
            throw new sfFileException(sprintf('Unable to open the log file "%s" for writing.', $file));
        }

        $this->fp = fopen($file, 'a');
        if (!$fileExists) {
            chmod($file, $this->getOption('file_mode'));
        }

        parent::setup();
    }

    /**
     * Logs a message.
     *
     * @param string Message
     * @param string Message level
     * @param string Message level name
     * @param string Application name (default to Sift)
     */
    public function log($message, $level = sfILogger::INFO, array $context = array())
    {
        $extra = '';
        if (isset($context[sfILogger::CONTEXT_EXTRA]) && !empty($context[sfILogger::CONTEXT_EXTRA])) {
            $extra = sfJson::encode($context[sfILogger::CONTEXT_EXTRA]);
            unset($context[sfILogger::CONTEXT_EXTRA]);
        }

        flock($this->fp, LOCK_EX);
        fwrite(
            $this->fp,
            strtr(
                $this->getOption('format'),
                array(
                    '%type%'    => $this->getOption('type'),
                    '%message%' => $this->formatMessage($message, $context),
                    '%time%'    => date($this->getOption('time_format')),
                    '%level%'   => $this->getLevelName($level),
                    '%EOL%'     => PHP_EOL,
                    '%extra%'   => $extra
                )
            )
        );
        flock($this->fp, LOCK_UN);
    }

    /**
     * Executes the shutdown method.
     */
    public function shutdown()
    {
        if (is_resource($this->fp)) {
            fclose($this->fp);
        }
    }

}
