<?php

/*
 * This file is part of the Sift PHP framework.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * sfStringStreamWrapper provides a stream wrapper for strings
 *
 * @package    Sift
 * @subpackage util_stream
 */
class sfStringStreamWrapper extends sfStreamWrapper {

  /**
   * Name of stream protocol - string://
   */
  const PROTOCOL = 'string';

  /**
   * The string data
   *
   * @var string
   */
  protected $data = '';

  /**
   * The pointer position
   *
   * @var integer
   */
  protected $position = 0;

  /**
   * The string length
   *
   * @var integer
   */
  protected $length = 0;

  /**
   * @see sfIStreamWrapper
   */
  public static function register($protocol = null, $flags = null)
  {
    return stream_wrapper_register($protocol ? $protocol : self::PROTOCOL, __CLASS__, $flags);
  }

  /**
   * Contructor
   */
  public function __construct()
  {
    $this->length = 0;
    $this->data = '';
  }

  /**
   * @see sfIStreamWrapper
   */
  function stream_open($path, $mode, $options, &$opened_path)
  {
    return true;
  }

  /**
   * @see sfIStreamWrapper
   */
  function stream_stat()
  {
    return false;
  }

  /**
   * @see sfIStreamWrapper
   */
  function stream_read($count)
  {
    $result = substr($this->data, $this->position, $count);
    if($result)
    {
      $this->position += strlen($result);
    }
    return $result;
  }

  /**
   * @see sfIStreamWrapper
   */
  public function stream_write($data)
  {
    $length = strlen($data);
    $this->data = substr($this->data, 0, $this->position) . $data . substr($this->data, $this->position += $length);
    return $length;
  }

  /**
   * @see sfIStreamWrapper
   */
  public function stream_close()
  {
  }

  /**
   * @see sfIStreamWrapper
   */
  function stream_tell()
  {
    return $this->position;
  }

  /**
   * @see sfIStreamWrapper
   */
  function stream_eof()
  {
    if($this->position > $this->length)
    {
      return true;
    }
    return false;
  }

  /**
   * @see sfIStreamWrapper
   */
  function stream_seek($offset, $whence)
  {
    $length = strlen($this->data);
    switch($whence)
    {
      case SEEK_SET:
        $newPos = $offset;
      break;
      case SEEK_CUR:
        $newPos = $this->position + $offset;
      break;
      case SEEK_END:
        $newPos = $length + $offset;
      break;

      default:
        return false;
    }
    $return = ($newPos >= 0 && $newPos <= $length);
    if($return)
    {
      $this->position = $newPos;
    }
    return $return;
  }

}
