<?php
/*
 * This file is part of the Sift PHP framework.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * sfIStorageAware interface
 *
 * @package Sift
 * @subpackage storage
 */
interface sfIStorageAware {

  /**
   * Sets the storage instance
   *
   * @param sfIStorage $storage
   */
  public function setStorage(sfIStorage $storage = null);

  /**
   * Return the storage instance
   *
   * @return sfIStorage
   */
  public function getStorage();

}
