<?php
/*
 * This file is part of the Sift PHP framework.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * Dummy class which simulates the user implementation.
 *
 * @package Sift
 * @subpackage i18n_extract
 */
abstract class sfI18nExtractUser extends sfBasicSecurityUser {

  /**
   * Array of options
   *
   * @param array $options
   */
  public function __construct($options = array())
  {
    $dispatcher = new sfEventDispatcher();
    $storage = new sfNoStorage();
    $request = new sfWebRequest($dispatcher);
    parent::__construct($dispatcher, $storage, $request, $options);
  }

  // all methods from sfUser which deals with context and
  // other objects are redefined

  public function getId()
  {
    return 0;
  }

  public function addCredential($credential)
  {
  }

  public function clearCredentials()
  {
  }

  public function removeCredential($credential)
  {
  }

  public function setAuthenticated($authenticated)
  {
  }

  public function isSuperAdmin()
  {
  }

  public function setCulture($culture)
  {
  }

  public function getCulture()
  {
  }

  /**
   * @see sfISecurityUser
   * @return sfBasicSecurityUser
   */
  public function setTimedOut($flag = true)
  {
  }

  public function getLastRequestTime()
  {
    return time();
  }

  /**
   * @see sfISecurityUser
   */
  public function isTimedOut()
  {
  }

  /**
   * @see sfISecurityUser
   * @return sfBasicSecurityUser
   */
  public function setId($id)
  {
  }


  public function getIp()
  {
  }

  public function getRealIp()
  {
  }

  public function getIpForwardedFor()
  {
  }

  public function getHostname()
  {
  }

  public function getUserAgent()
  {
  }

  public function getBrowserName()
  {
  }

  public function getBrowserVersion()
  {
  }

  public function getBrowser()
  {
  }

  public function isBot()
  {
  }

  public function isMobile()
  {
  }

  public function setTimezone($timezone)
  {
  }

  public function getTimezone()
  {
  }

  public function getReferer($default = null)
  {
  }

  public function setReferer($referer)
  {
  }

  public function shutdown()
  {
  }

  public function __call($method, $arguments)
  {
  }

}