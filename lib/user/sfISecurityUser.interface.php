<?php
/*
 * This file is part of the Sift PHP framework.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * sfISecurityUser interface provides advanced security manipulation methods.
 *
 * @package    Sift
 * @subpackage user
 */
interface sfISecurityUser
{
  /**
   * Super admin credential
   */
  const CREDENTIAL_SUPER_ADMIN = 'super_admin';

  /**
   * Add a credential to this user.
   *
   * @param mixed Credential data.
   *
   * @return void
   */
  public function addCredential($credential);

  /**
   * Clear all credentials associated with this user.
   *
   * @return void
   */
  public function clearCredentials();

  /**
   * Indicates whether or not this user has a credential.
   *
   * @param mixed Credential data.
   *
   * @return bool true, if this user has the credential, otherwise false.
   */
  public function hasCredential($credential);

  /**
   * Indicates whether or not this user is authenticated.
   *
   * @return bool true, if this user is authenticated, otherwise false.
   */
  public function isAuthenticated();

  /**
   * Remove a credential from this user.
   *
   * @param mixed Credential data.
   *
   * @return void
   */
  public function removeCredential($credential);

  /**
   * Set the authenticated status of this user.
   *
   * @param bool A flag indicating the authenticated status of this user.
   *
   * @return void
   */
  public function setAuthenticated($authenticated);

  /**
   * Is the user super admin?
   *
   * @return boolean
   */
  public function isSuperAdmin();

  /**
   * Returns user id
   *
   * @return integer
   */
  public function getId();

  /**
   * Set user id
   *
   * @param integer $id The user identifier
   */
  public function setId($id);

  /**
   * Set the user timeout status
   *
   * @param boolean $flag
   */
  public function setTimedOut($flag = true);

  /**
   * Is the user timed out?
   */
  public function isTimedOut();

  /**
   * Returns last request time
   *
   * @return integer
   */
  public function getLastRequestTime();

}
