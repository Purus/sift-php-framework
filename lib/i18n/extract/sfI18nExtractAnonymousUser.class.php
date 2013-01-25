<?php
/*
 * This file is part of the Sift PHP framework.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * Dummy class which simulates anonymous user. Used for extracting 
 * strings from forms (which can be dynamically configured to display fields only
 * for anonymous user)
 * 
 * @package Sift
 * @subpackage i18n_extract
 */
class sfI18nExtractAnonymousUser extends sfUser implements sfSecurityUser {

  public function getId()
  {
    return -1;
  }

  public function addCredential($credential)
  {    
  }

  public function clearCredentials()
  {    
  }
  
  public function hasCredential($credential)
  {
    return false;
  }

  public function isAuthenticated()
  {
    return false;
  }

  public function isLoggedIn()
  {
    return false;
  }

  public function removeCredential($credential)
  {
    return;
  }

  public function setAuthenticated($authenticated)
  {
    return;
  }

}
