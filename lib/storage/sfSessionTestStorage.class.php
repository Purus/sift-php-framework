<?php
/*
 * This file is part of the Sift PHP framework.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * sfSessionTestStorage is a fake sfSessionStorage implementation to allow easy testing.
 *
 * @package    Sift
 * @subpackage storage
 */
class sfSessionTestStorage extends sfStorage
{
  /**
   * Array of required options
   *
   * @var array
   */
  protected $requiredOptions = array(
    'session_path'
  );

  protected $sessionId = null,
    $sessionData = array(),
    $sessionPath = null;

  /**
   * Initializes this Storage instance.
   *
   * @param sfContext A sfContext instance
   * @param array   An associative array of initialization parameters
   *
   * @return boolean true, if initialization completes successfully, otherwise false
   *
   * @throws sfInitializationException If an error occurs while initializing this Storage
   */
  public function setup()
  {
    // initialize parent
    parent::setup();

    $this->sessionPath = $this->getOption('session_path').'/sessions';

    if (array_key_exists('session_id', $_SERVER)) {
      $this->sessionId = $_SERVER['session_id'];
      // we read session data from temp file
      $file = $this->sessionPath . '/' . $this->sessionId . '.session';
      $this->sessionData = file_exists($file) ? unserialize(file_get_contents($file)) : array();
    } else {
      $this->sessionId = md5(uniqid(rand(), true));
      $this->sessionData = array();
    }
  }

  /**
   * Gets session id for the current session storage instance.
   *
   * @return string Session id
   */
  public function getSessionId()
  {
    return $this->sessionId;
  }

  /**
   * @see sfIStorage
   */
  public function read($key)
  {
    if (isset($this->sessionData[$key])) {
      return $this->sessionData[$key];
    }
  }

  /**
   * @see sfIStorage
   */
  public function remove($key)
  {
    $retval = null;

    if (isset($this->sessionData[$key])) {
      $retval = $this->sessionData[$key];
      unset($this->sessionData[$key]);
    }

    return $retval;
  }

  /**
   * @see sfIStorage
   */
  public function write($key, $data)
  {
    $this->sessionData[$key] = $data;
  }

  /**
   * Clears all test sessions.
   */
  public function clear()
  {
    sfToolkit::clearDirectory($this->sessionPath);
  }

  /**
   * @see sfIStorage
   */
  public function regenerate($destroy = false)
  {
    return true;
  }

  /**
   * @see sfIStorage
   */
  public function isStarted()
  {
    return true;
  }

  /**
   * @see sfIStorage
   */
  public function start()
  {
  }

  /**
   * @see sfIService
   */
  public function shutdown()
  {
    if ($this->sessionId) {
      $current_umask = umask(0000);
      if (!is_dir($this->sessionPath)) {
        mkdir($this->sessionPath, 0777, true);
      }
      umask($current_umask);
      file_put_contents($this->sessionPath . '/' . $this->sessionId . '.session', serialize($this->sessionData));
      $this->sessionId = '';
      $this->sessionData = array();
    }
  }

}
