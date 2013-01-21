<?php
/*
 * This file is part of the Sift PHP framework.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * sfSessionStorage allows you to store persistent symfony data in the user session.
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
 * @author     Fabien Potencier <fabien.potencier@symfony-project.com>
 * @author     Sean Kerr <sean@code-box.org>
 */
class sfSessionStorage extends sfStorage
{
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
  public function initialize($context, $parameters = null)
  {
    // initialize parent
    parent::initialize($context, $parameters);

    // set session name
    $sessionName = $this->getParameterHolder()->get('session_name', 'sessionID');

    session_name($sessionName);

    $use_cookies = (boolean) ini_get('session.use_cookies');
    if (!$use_cookies)
    {
      $sessionId = $context->getRequest()->getParameter($sessionName, '');

      if ($sessionId != '')
      {
        session_id($sessionId);
      }
    }

    $cookieDefaults = session_get_cookie_params();
    $lifetime = $this->getParameter('session_cookie_lifetime', $cookieDefaults['lifetime']);
    $path     = $this->getParameter('session_cookie_path',     $cookieDefaults['path']);
    $domain   = $this->getParameter('session_cookie_domain',   $cookieDefaults['domain']);
    $secure   = $this->getParameter('session_cookie_secure',   $cookieDefaults['secure']);
    $httpOnly = $this->getParameter('session_cookie_httponly', isset($cookieDefaults['httponly']) ? $cookieDefaults['httponly'] : false);
    
    if(version_compare(phpversion(), '5.2', '>='))
    {
      session_set_cookie_params($lifetime, $path, $domain, $secure, $httpOnly);
    }
    else
    {
      session_set_cookie_params($lifetime, $path, $domain, $secure);
    }

    if ($this->getParameter('auto_start', true))
    {
      // start our session
      session_start();
    }
  }

  /**
   * Reads data from this storage.
   *
   * The preferred format for a key is directory style so naming conflicts can be avoided.
   *
   * @param string A unique key identifying your data
   *
   * @return mixed Data associated with the key
   */
  public function & read($key)
  {
    $retval = null;

    if (isset($_SESSION[$key]))
    {
      $retval =& $_SESSION[$key];
    }

    return $retval;
  }

  /**
   * Removes data from this storage.
   *
   * The preferred format for a key is directory style so naming conflicts can be avoided.
   *
   * @param string A unique key identifying your data
   *
   * @return mixed Data associated with the key
   */
  public function & remove($key)
  {
    $retval = null;

    if (isset($_SESSION[$key]))
    {
      $retval =& $_SESSION[$key];
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
  public function write($key, &$data)
  {
    $_SESSION[$key] =& $data;
  }

  /**
   * Executes the shutdown procedure.
   *
   */
  public function shutdown()
  {
    // don't need a shutdown procedure because read/write do it in real-time
  }
  
}
