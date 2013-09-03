<?php
/*
 * This file is part of the Sift PHP framework.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * sfSessionStorage allows you to store persistent data in the user session.
 *
 * Available parameters:
 *
 * * auto_start   - [true]     - Should session_start() automatically be called?
 * * session_name - [sessionID] - The name of the session.
 * * session_cookie_lifetime - Lifetime of the session cookie, defined in seconds. Default value is taken from session_get_cookie_params();
 * * session_cookie_path - Path on the domain where the cookie will work. Use a single slash ('/') for all paths on the domain. Default value is taken from session_get_cookie_params();
 * * session_cookie_domain - Cookie domain, for example 'www.example.com'. To make cookies visible on all subdomains then the domain must be prefixed with a dot like '.example.com'. Default value is taken from session_get_cookie_params();
 * * session_cookie_secure - If true cookie will only be sent over secure connections. Default value is taken from session_get_cookie_params();
 * * session_cookie_httponly - If set to true then PHP will attempt to send the httponly flag when setting the session cookie. Default value is taken from session_get_cookie_params();
 *
 * @package    Sift
 * @subpackage storage
 */
class sfSessionStorage extends sfStorage {

  static protected
    $sessionIdRegenerated = false,
    $sessionStarted       = false;

  /**
   * Default options
   *
   * @var array
   */
  protected $defaultOptions = array(
    'session_name' => 'sessionID',
    'auto_start' => true,
    'session_cookie_httponly' => true
  );

  /**
   * Returns default options for this object.
   *
   * @return array
   */
  protected function getDefaultOptions()
  {
    $defaultOptions = parent::getDefaultOptions();
    $cookieDefaults = session_get_cookie_params();
    $options = array_merge(array(
      'session_cookie_lifetime' => $cookieDefaults['lifetime'],
      'session_cookie_path'     => $cookieDefaults['path'],
      'session_cookie_domain'   => $cookieDefaults['domain'],
      'session_cookie_secure'   => $cookieDefaults['secure'],
      'session_cookie_httponly' => isset($cookieDefaults['httponly']) ? $cookieDefaults['httponly'] : false,
      'session_cache_limiter'   => null,
    ), $defaultOptions);
    return $options;
  }

  /**
   * Setups the storage
   *
   */
  public function setup()
  {
    parent::setup();

    // set session name
    session_name($this->getOption('session_name'));

    if(!(boolean) ini_get('session.use_cookies') && $sessionId = $this->getOption('session_id'))
    {
      session_id($sessionId);
    }

    $lifetime = $this->getOption('session_cookie_lifetime');
    $path     = $this->getOption('session_cookie_path');
    $domain   = $this->getOption('session_cookie_domain');
    $secure   = $this->getOption('session_cookie_secure');
    $httpOnly = $this->getOption('session_cookie_httponly');

    session_set_cookie_params($lifetime, $path, $domain, $secure, $httpOnly);

    if($this->getOption('auto_start'))
    {
      $this->start();
    }
  }

  /**
   * @see sfIStorage
   */
  public function read($key)
  {
    if(isset($_SESSION[$key]))
    {
      return $_SESSION[$key];
    }
  }

  /**
   * @see sfIStorage
   */
  public function remove($key)
  {
    if(isset($_SESSION[$key]))
    {
      $retval = $_SESSION[$key];
      unset($_SESSION[$key]);
    }
    return $retval;
  }

  /**
   * Writes data to this storage.
   *
   * The preferred format for a key is directory style so naming conflicts can be avoided.
   *
   * @param string A unique key identifying your data
   * @param mixed  Data associated with your key
   *
   */
  public function write($key, $data)
  {
    $_SESSION[$key] = $data;
  }

  /**
   * Regenerates id that represents this storage.
   *
   * @param  boolean $destroy Destroy session when regenerating?
   * @return boolean True if session regenerated, false if error
   */
  public function regenerate($destroy = false)
  {
    if(self::$sessionIdRegenerated)
    {
      return;
    }

    // regenerate a new session id once per object
    session_regenerate_id($destroy);

    self::$sessionIdRegenerated = true;
  }

  /**
   * @see sfIStorage
   */
  public function start()
  {
    if(self::$sessionStarted)
    {
      return;
    }
    // start our session
    session_start();
    self::$sessionStarted = true;
  }

  /**
   * @see sfIStorage
   */
  public function isStarted()
  {
    return self::$sessionStarted;
  }

  /**
   * @see sfIService
   */
  public function shutdown()
  {
    if($this->isStarted())
    {
      session_write_close();
      self::$sessionStarted = false;
    }
  }

}
