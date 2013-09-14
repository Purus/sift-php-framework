<?php
/*
 * This file is part of the Sift PHP framework.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * sfICacheAware interface
 *
 * @package Sift
 * @subpackage cache
 */
interface sfICacheAware {

  /**
   * Sets cache instance
   *
   * @param sfICache $cache
   */
  public function setCache(sfICache $cache = null);

  /**
   * Return the cache instance
   *
   * @return sfICache
   */
  public function getCache();

}
