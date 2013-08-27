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
   *
   * @var integer
   */
  protected $lastRequest;

  /**
   * Credentials
   *
   * @var array
   */
  protected $credentials = array();

  /**
   * Authenticated flag
   *
   * @var boolean
   */
  protected $authenticated = false;

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
  protected $id;

  /**
   * Array of default options.
   * The default options are inherited from parent classes.
   *
   * @var array
   */
  protected $defaultOptions = array(
    'timeout' => 1800
  );

  /**
   * Setups the user
   *
   */
  public function setup()
  {
    parent::setup();

    // force the max lifetime for session garbage collector to be greater than timeout
    if(ini_get('session.gc_maxlifetime') < $this->getOption('timeout')
      && sfToolkit::isCallable('ini_set'))
    {
      ini_set('session.gc_maxlifetime', $this->getOption('timeout'));
    }

    // read data from storage
    $storage = $this->serviceContainer->get('storage');

    $this->authenticated = $storage->read(self::AUTH_NAMESPACE);
    $this->credentials = $storage->read(self::CREDENTIAL_NAMESPACE);
    $this->lastRequest = $storage->read(self::LAST_REQUEST_NAMESPACE);
    $this->id = $storage->read(self::ID_NAMESPACE);

    // Automatic logout logged in user if no request within the timeout option
    if(null !== $this->lastRequest && (time() - $this->lastRequest) > $this->getOption('timeout'))
    {
      if(sfConfig::get('sf_logging_enabled'))
      {
        sfLogger::getInstance()->info('{sfUser} Automatic user logout due to timeout.');
      }
      $this->setTimedOut(true);
      $this->setAuthenticated(false);
      $this->setId(null);
    }

    $this->lastRequest = time();
  }

  /**
   * Clears all credentials.
   *
   * @return sfBasicSecurityUser
   */
  public function clearCredentials()
  {
    $this->credentials = array();
    return $this;
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
   * @param mixed $credential credential
   * @return sfBasicSecurityUser
   */
  public function removeCredential($credential)
  {
    if($this->hasCredential($credential))
    {
      foreach($this->credentials as $key => $value)
      {
        if($credential == $value)
        {
          unset($this->credentials[$key]);
          if(sfConfig::get('sf_logging_enabled'))
          {
            sfLogger::getInstance()->info(sprintf('{sfUser} Removed credential "%s',  $credential));
          }
          break;
        }
      }
    }
    return $this;
  }

  /**
   * Adds a credential.
   *
   * @param mixed $credential Credentials
   * @return sfBasicSecurityUser
   */
  public function addCredential($credential)
  {
    $this->addCredentials(func_get_args());
    return $this;
  }

  /**
   * Adds several credential at once.
   *
   * @param array $credentials List of credentials
   * @return sfBasicSecurityUser
   */
  public function addCredentials()
  {
    if(func_num_args() == 0)
    {
      return;
    }

    // Add all credentials
    $credentials = (is_array(func_get_arg(0))) ? func_get_arg(0) : func_get_args();

    foreach($credentials as $aCredential)
    {
      if(!in_array($aCredential, $this->credentials))
      {
        $this->credentials[] = $aCredential;
      }
    }

    if(sfConfig::get('sf_logging_enabled'))
    {
      sfLogger::getInstance()->info(sprintf('{sfUser} Add credential(s) "%s"', implode(', ', $credentials)));
    }

    return $this;
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
   * Is the user super admin?
   *
   * @return boolean
   */
  public function isSuperAdmin()
  {
    return $this->hasCredential(self::CREDENTIAL_SUPER_ADMIN);
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
   * @param boolean $authenticated The flag
   * @return sfBasicSecurityUser
   */
  public function setAuthenticated($authenticated)
  {
    if(sfConfig::get('sf_logging_enabled'))
    {
      sfLogger::getInstance()->info('{sfUser} User is ' . ($authenticated === true ? '' : 'not ') . 'authenticated');
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

    return $this;
  }

  /**
   * @see sfISecurityUser
   * @return sfBasicSecurityUser
   */
  public function setTimedOut($flag = true)
  {
    $this->timedout = $flag;
    return $this;
  }

  /**
   * @see sfISecurityUser
   */
  public function isTimedOut()
  {
    return $this->timedout;
  }

  /**
   * @see sfISecurityUser
   */
  public function getId()
  {
    return $this->id;
  }

  /**
   * @see sfISecurityUser
   * @return sfBasicSecurityUser
   */
  public function setId($id)
  {
    $this->id = $id;
    return $this;
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
