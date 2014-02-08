<?php

/*
 * This file is part of the Sift PHP framework.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * Cache interface
 *
 * @package Sift
 * @subpackage cache
 */
interface sfICache
{
  /**
   * Delete mode -> only old caches
   */
  const MODE_OLD = 'old';

  /**
   * Delete mode -> all caches
   */
  const MODE_ALL = 'all';

  /**
   * Separator
   */
  const SEPARATOR = ':';

  /**
   * Gets the cache content for a given key.
   *
   * @param string $key     The cache key
   * @param mixed  $default The default value is the key does not exist or not valid anymore
   * @return string The data of the cache
   */
  public function get($id, $default = null);

  /**
   * Returns true if there is a cache for the given key.
   *
   * @param string $key The cache key
   * @return boolean true if the cache exists, false otherwise
   */
  public function has($key);

  /**
   * Saves some data in the cache.
   *
   * @param string $key      The cache key
   * @param string $data     The data to put in cache
   * @param integer $lifetime The lifetime
   * @return boolean true if no problem
   */
  public function set($key, $data, $lifetime = null);

  /**
   * Removes a content from the cache.
   *
   * @param string $key The cache key
   * @return boolean true if no problem
   */
  public function remove($key);

  /**
   * Removes content from the cache that matches the given pattern.
   *
   * @param string $pattern The cache key pattern
   * @return boolean true if no problem
   */
  public function removePattern($pattern);

  /**
   * Cleans the cache.
   *
   * @param string $mode The clean mode
   *                     sfICache::MODE_ALL: remove all keys (default)
   *                     sfICache::MODE_OLD: remove all expired keys
   *
   * @return boolean true if no problem
   */
  public function clean($mode = self::MODE_ALL);

  /**
   * Returns the timeout for the given key.
   *
   * @param string $key The cache key
   * @return int The timeout time
   */
  public function getTimeout($key);

  /**
   * Returns the last modification date of the given key.
   *
   * @param string $key The cache key
   * @return int The last modified time
   */
  public function getLastModified($key);

  /**
   * Gets many keys at once.
   *
   * @param array $keys An array of keys
   * @return array An associative array of data from cache
   */
  public function getMany($keys);

  /**
   * Returns the cache backend
   *
   * @return mixed
   */
  public function getCacheBackend();

}
