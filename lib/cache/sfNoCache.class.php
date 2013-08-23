<?php

/*
 * This file is part of the Sift PHP framework.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * Cache class that does nothing.
 *
 * @package Sift
 * @subpackage cache
 */
class sfNoCache extends sfCache {

  /**
   * @see sfICache
   */
  public function get($key, $default = null)
  {
    return $default;
  }

  /**
   * @see sfICache
   */
  public function has($key)
  {
    return false;
  }

  /**
   * @see sfICache
   */
  public function set($key, $data, $lifetime = null)
  {
    return true;
  }

  /**
   * @see sfICache
   */
  public function remove($key)
  {
    return true;
  }

  /**
   * @see sfICache
   */
  public function removePattern($pattern)
  {
    return true;
  }

  /**
   * @see sfICache
   */
  public function clean($mode = self::MODE_ALL)
  {
    return true;
  }

  /**
   * @see sfICache
   */
  public function getLastModified($key)
  {
    return 0;
  }

  /**
   * @see sfICache
   */
  public function getTimeout($key)
  {
    return 0;
  }

  /**
   * @see sfICache
   */
  public function getMany($keys)
  {
    return array();
  }

}
