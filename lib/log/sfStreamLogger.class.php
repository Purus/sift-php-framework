<?php
/*
 * This file is part of the Sift PHP framework.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * Log messages to a PHP stream. Available options:
 *
 * * stream (the php stream) [REQUIRED]
 * * close_stream (boolean) [OPTIONAL] Close the stream on shutdown?
 *
 * @package    Sift
 * @subpackage log
 */
class sfStreamLogger extends sfLoggerBase
{
  /**
   * The stream to log to
   *
   * @var resource
   */
  protected $stream;

  /**
   * Array of required options
   *
   * @var array
   */
  protected $requiredOptions = array(
    'stream'
  );

  /**
   * Array of default options
   *
   * @var array
   */
  protected $defaultOptions = array(
    'close_stream' => false
  );

  /**
   * Setups this logger.
   */
  public function setup()
  {
    if (!$stream = $this->getOption('stream')) {
      throw new sfConfigurationException('You must provide a "stream" option for this logger.');
    } else {
      if (is_resource($stream) && 'stream' != get_resource_type($stream)) {
        throw new sfConfigurationException('The provided "stream" option is not a stream.');
      }
    }

    $this->stream = $stream;
  }

  /**
   * Sets the PHP stream to use for this logger.
   *
   * @param stream $stream A php stream
   */
  public function setStream($stream)
  {
    $this->stream = $stream;
  }

  /**
   * @see sfILogger
   */
  public function log($message, $level = sfILogger::INFO, array $context = array())
  {
    fwrite($this->stream, $this->formatMessage($message, $context).PHP_EOL);
    flush();
  }

  /**
   * Shutdown the logger
   *
   */
  public function shutdown()
  {
    if ($this->getOption('close_stream') && $this->stream) {
      fclose($this->stream);
    }
  }

}
