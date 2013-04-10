<?php
/*
 * This file is part of the Sift PHP framework.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * Log messages to a PHP stream.
 *
 * @package    Sift
 * @subpackage log
 */
class sfStreamLogger extends sfLogger
{
  protected
    $stream = null;

  /**
   * Initializes this logger.
   *
   * Available options:
   *
   * - stream: A PHP stream
   *
   * @param  sfEventDispatcher $dispatcher  A sfEventDispatcher instance
   * @param  array             $options     An array of options.
   *
   * @return Boolean      true, if initialization completes successfully, otherwise false.
   */
  public function initialize($options = array())
  {
    if (!isset($options['stream']))
    {
      throw new sfConfigurationException('You must provide a "stream" option for this logger.');
    }
    else
    {
      if (is_resource($options['stream']) && 'stream' != get_resource_type($options['stream']))
      {
        throw new sfConfigurationException('The provided "stream" option is not a stream.');
      }
    }

    $this->stream = $options['stream'];
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
   * Logs a message.
   *
   * @param string $message   Message
   * @param string $priority  Message priority
   */
  public function log($message, $priority = sfLogger::INFO)
  {
    fwrite($this->stream, $message.PHP_EOL);
    flush();
  }
  
}

