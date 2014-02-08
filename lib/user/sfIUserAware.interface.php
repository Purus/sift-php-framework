<?php
/*
 * This file is part of the Sift PHP framework.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * sfIUserAware interface
 *
 * @package    Sift
 * @subpackage user
 */
interface sfIUserAware {

  /**
   * Sets the user
   *
   * @param sfIUser The user instance
   */
  public function setUser(sfIUser $user = null);

  /**
   * Returns the user
   *
   * @return sfIUser|null
   */
  public function getUser();

}
