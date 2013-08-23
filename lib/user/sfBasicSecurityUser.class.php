<?php
/*
 * This file is part of the Sift PHP framework.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * sfBasicSecurityUser will handle any type of data as a credential.
 *
 * @package    Sift
 * @subpackage user
 */
class sfBasicSecurityUser extends sfUser implements sfISecurityUser, sfIService {

  /**
   * Last request namespace
   */
  const LAST_REQUEST_NAMESPACE = 'sift/user/sfUser/lastRequest';

  /**
   * Auth namespace
   */
  const AUTH_NAMESPACE = 'sift/user/sfUser/authenticated';

  /**
   * Credentials namespace
   */
  const CREDENTIAL_NAMESPACE = 'sift/user/sfUser/credentials';

  /**
   * Id namespace
   */
  const ID_NAMESPACE = 'sift/user/sfUser/id';

  /**
   * Last request time
   * @var integer
   */
  protected $lastRequest = null;

  /**
   * Credentials
   *
   * @var array
   */
  protected $credentials = null;

  /**
   * Authenticated flag
   *
   * @var boolean
   */
  protected $authenticated = null;

  /**
   * Timed out flag
   *
   * @var boolean
   */
  protected $timedout = false;

  /**
   * User id
   *
   * @var integer
   */
  protected $id = null;

  /**
   * @see sfUser
   */
  public function __construct(sfServiceContainer $serviceContainer, $parameters = array())
  {
    parent::__construct($serviceContainer, $parameters);

    // read data from storage
    $storage = $this->serviceContainer->get('storage');

    $this->authenticated = $storage->read(self::AUTH_NAMESPACE);
    $this->credentials = $storage->read(self::CREDENTIAL_NAMESPACE);
    $this->lastRequest = $storage->read(self::LAST_REQUEST_NAMESPACE);
    $this->id = $storage->read(self::ID_NAMESPACE);

    if($this->authenticated == null)
    {
      $this->authenticated = false;
      $this->credentials = array();
    }
    else
    {
      // Automatic logout logged in user if no request within [sf_timeout] setting
      if(null !== $this->lastRequest && (time() - $this->lastRequest) > sfConfig::get('sf_timeout'))
      {
        if(sfConfig::get('sf_logging_enabled'))
        {
          sfLogger::getInstance()->info('{sfUser} Automatic user logout due to timeout.');
        }
        $this->setTimedOut();
        $this->setAuthenticated(false);
        $this->setId(null);
      }
    }

    $this->lastRequest = time();
  }

  /**
   * Clears all credentials.
   *
   */
  public function clearCredentials()
  {
    $this->credentials = null;
    $this->credentials = array();
  }

  /**
   * Returns an array containing the credentials
   *
   * @return array
   */
  public function getCredentials()
  {
    return $this->credentials;
  }

  /**
   * Removes a credential.
   *
   * @param  mixed credential
   */
  public function removeCredential($credential)
  {
    if($this->hasCredential($credential))
    {
      foreach($this->credentials as $key => $value)
      {
        if($credential == $value)
        {
          if(sfConfig::get('sf_logging_enabled'))
          {
            sfLogger::getInstance()->info('{sfUser} remove credential "' . $credential . '"');
          }
          unset($this->credentials[$key]);
          return;
        }
      }
    }
  }

  /**
   * Adds a credential.
   *
   * @param mixed $credential Credentials
   */
  public function addCredential($credential)
  {
    $this->addCredentials(func_get_args());
  }

  /**
   * Adds several credential at once.
   *
   * @param  mixed array or list of credentials
   */
  public function addCredentials()
  {
    if(func_num_args() == 0)
    {
      return;
    }

    // Add all credentials
    $credentials = (is_array(func_get_arg(0))) ? func_get_arg(0) : func_get_args();

    if(sfConfig::get('sf_logging_enabled'))
    {
      sfLogger::getInstance()->info('{sfUser} add credential(s) "' . implode(', ', $credentials) . '"');
    }

    foreach($credentials as $aCredential)
    {
      if(!in_array($aCredential, $this->credentials))
      {
        $this->credentials[] = $aCredential;
      }
    }
  }

  /**
   * Returns true if user has credential.
   *
   * @param  mixed credentials
   * @param  boolean useAnd specify the mode, either AND or OR
   * @return boolean   
   */
  public function hasCredential($credentials, $useAnd = true)
  {
    if(!is_array($credentials))
    {
      return in_array($credentials, $this->credentials);
    }

    // now we assume that $credentials is an array
    $test = false;

    foreach($credentials as $credential)
    {
      // recursively check the credential with a switched AND/OR mode
      $test = $this->hasCredential($credential, $useAnd ? false : true);

      if($useAnd)
      {
        $test = $test ? false : true;
      }

      if($test) // either passed one in OR mode or failed one in AND mode
      {
        break; // the matter is settled
      }
    }

    if($useAnd) // in AND mode we succeed if $test is false
    {
      $test = $test ? false : true;
    }

    return $test;
  }

  /**
   * Returns true if user is authenticated.
   *
   * @return boolean
   */
  public function isAuthenticated()
  {
    return $this->authenticated;
  }

  /**
   * Returns true if user is logged in.
   * This is an alias for "isAuthenticated()"
   *
   * @return boolean
   */
  public function isLoggedIn()
  {
    return $this->isAuthenticated();
  }

  /**
   * Sets authentication for user.
   *
   * @param  boolean
   */
  public function setAuthenticated($authenticated)
  {
    if(sfConfig::get('sf_logging_enabled'))
    {
      sfLogger::getInstance()->info('{sfUser} user is ' . ($authenticated === true ? '' : 'not ') . 'authenticated');
    }

    if($authenticated === true)
    {
      $this->authenticated = true;
    }
    else
    {
      $this->authenticated = false;
      $this->clearCredentials();
    }
  }

  public function setTimedOut()
  {
    $this->timedout = true;
  }

  public function isTimedOut()
  {
    return $this->timedout;
  }

  /**
   * Returns Id of the user
   */
  public function getId()
  {
    return $this->id;
  }

  /**
   * Returns the user Id
   *
   * @param string $id
   */
  public function setId($id)
  {
    $this->id = $id;
  }

  /**
   * Returns the timestamp of the last user request.
   *
   * @param  integer
   */
  public function getLastRequestTime()
  {
    return $this->lastRequest;
  }

  public function shutdown()
  {
    $storage = $this->serviceContainer->get('storage');

    // write the last request time to the storage
    $storage->write(self::LAST_REQUEST_NAMESPACE, $this->lastRequest);

    $storage->write(self::AUTH_NAMESPACE, $this->authenticated);
    $storage->write(self::CREDENTIAL_NAMESPACE, $this->credentials);
    $storage->write(self::ID_NAMESPACE, $this->id);

    // call the parent shutdown method
    parent::shutdown();
  }

}
