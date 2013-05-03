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
 * @package    Sift
 * @subpackage cache
 */
class sfNoCache extends sfCache
{
  public function get($id, $namespace = self::DEFAULT_NAMESPACE, $doNotTestCacheValidity = false)
  {
    return null;
  }

  /**
   * @see sfCache
   */
  public function has($id, $namespace = self::DEFAULT_NAMESPACE, $doNotTestCacheValidity = false)
  {
    return false;
  }

  /**
   * @see sfCache
   */
  public function set($id, $namespace = self::DEFAULT_NAMESPACE, $data, $lifetime = null)
  {
    return true;
  }

  /**
   * @see sfCache
   */
  public function remove($id, $namespace = self::DEFAULT_NAMESPACE)
  {
    return true;
  }

  public function clean($namespace = null, $mode = self::MODE_ALL)
  {
    return true;
  }

  /**
  * Returns the cache last modification time.
  *
  * @return int The last modification time
  */
  public function getLastModified($id, $namespace = self::DEFAULT_NAMESPACE)
  {
    return 0;
  }

}
