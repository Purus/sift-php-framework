<?php
/*
 * This file is part of the Sift PHP framework.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * sfIStreamWrapper - stream interface
 *
 * @package    Sift
 * @subpackage util_stream
 * @link http://www.php.net/manual/en/class.streamwrapper.php
 */
interface sfIStreamWrapper
{
  /**
   * Register self as stream wrapper
   *
   * @see http://php.net/stream_wrapper_register
   */
  public static function register($protocol = null, $flags = null);

  /**
   * Opens the stream
   *
   * @param string $path
   * @param string $mode The mode used to open the file, as detailed for fopen().
   * @param integer $options Holds additional flags set by the streams API.
   * @param string $opened_path If the path is opened successfully, and STREAM_USE_PATH is set in options,
   *                            opened_path should be set to the full path of the file/resource that was actually opened.
   * @link http://www.php.net/manual/en/streamwrapper.stream-open.php
   * @return boolean Returns true on success or false on failure.
   */
  public function stream_open($path, $mode, $options, &$opened_path);

  /**
   * Reads from the stream
   *
   * @param integer $count How many bytes of data from the current position should be returned.
   * @return integer If there are less than count bytes available, return as many as are available. If no more data is available, return either false or an empty string.
   */
  public function stream_read($count);

  /**
   * Should return `true` if the read/write position is at the end of the stream
   * and if no more data is available to be read, or `false` otherwise.
   *
   * @return boolean
   */
  public function stream_eof();

  /**
   * Returns the current position of the stream.
   *
   * @return integer
   */
  public function stream_tell();

  /**
   * Sets the file position indicator
   *
   * @param integer $offset The offset.
   * @param integer $whence SEEK_SET - Set position equal to offset bytes,
   *                        SEEK_CUR - Set position to current location plus offset.
   *                        SEEK_END - Set position to end-of-file plus offset.
   */
  public function stream_seek($offset, $whence);

  /**
   * Closes the stream
   *
   * @return void
   */
  public function stream_close();

  /**
   * Returns information about the stream
   *
   * @return array|false
   */
  public function stream_stat();

  /**
   * Write data to stream
   *
   * This method is called in response to fwrite().
   *
   * @param string $data The data to be written
   * @return integer The number of bytes that were successfully stored, or 0 if none could be stored.
   */
  public function stream_write($data);
}
