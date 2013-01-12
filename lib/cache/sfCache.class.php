<?php
/*
 * This file is part of the Sift PHP framework.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * sfCache is an abstract class for all cache classes
 *
 * @package    Sift
 * @subpackage cache
 * @author     Fabien Potencier <fabien.potencier@symfony-project.com>
 * @author     Fabien Marty <fab@php.net>
 */
abstract class sfCache extends sfConfigurable 
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
   * Default cache namespace
   * 
   */
  const DEFAULT_NAMESPACE = '';
  
  /**
   * Array of default options
   * 
   * @var array 
   */
  protected $defaultOptions = array(
    'lifetime' => 86400,  
    'automatic_cleaning_factor' => 500
  );

  /**
   * Valid options for the cache
   * 
   * @var array 
   */
  protected $validOptions = array(
    'lifetime', 'automatic_cleaning_factor'
  );
  
  /**
   * Constructs the cache
   * 
   * @param array $options
   */
  public function __construct($options = array())
  {
    parent::__construct($options);
    $this->initialize($options);
  }
  
  /**
   * Initializes the cache
   * 
   * @param array $options
   */
  public function initialize($options = array())
  {    
  }

  /**
   * Gets the cache content for a given id and namespace.
   *
   * @param  string  The cache id
   * @param  string  The name of the cache namespace
   * @param  boolean If set to true, the cache validity won't be tested
   *
   * @return string  The data of the cache (or null if no cache available)
   */
  abstract public function get($id, $namespace = self::DEFAULT_NAMESPACE, $doNotTestCacheValidity = false);

  /**
   * Returns true if there is a cache for the given id and namespace.
   *
   * @param  string  The cache id
   * @param  string  The name of the cache namespace
   * @param  boolean If set to true, the cache validity won't be tested
   *
   * @return boolean true if the cache exists, false otherwise
   */
  abstract public function has($id, $namespace = self::DEFAULT_NAMESPACE, $doNotTestCacheValidity = false);

  /**
   * Saves some data in the cache.
   *
   * @param string The cache id
   * @param string The name of the cache namespace
   * @param string The data to put in cache
   * @param integer Lifetime of the cache
   * @return boolean true if no problem
   */
  abstract public function set($id, $namespace = self::DEFAULT_NAMESPACE, $data, $lifetime = null);

  /**
   * Removes a content from the cache.
   *
   * @param string The cache id
   * @param string The name of the cache namespace
   *
   * @return boolean true if no problem
   */
  abstract public function remove($id, $namespace = self::DEFAULT_NAMESPACE);

  /**
   * Cleans the cache.
   *
   * If no namespace is specified all cache content will be destroyed
   * else only cache contents of the specified namespace will be destroyed.
   *
   * @param string The name of the cache namespace
   *
   * @return boolean true if no problem
   */
  abstract public function clean($namespace = null, $mode = self::MODE_ALL);

  /**
   * Returns the cache last modification time.
   *
   * @return int The last modification time
   */
  abstract public function getLastModified($id, $namespace = self::DEFAULT_NAMESPACE);
  
  /**
   * Sets a new life time.
   *
   * @param int $lifetime The new life time (in seconds)
   * @return sfCache
   */
  public function setLifeTime($lifeTime)
  {
    return $this->setOption('lifetime', $lifeTime);   
  }

  /**
   * Gets automatic_cleaning_factor option
   *
   * @return integer
   */
  public function getAutomaticCleaningFactor()
  {
    return $this->getOption('automatic_cleaning_factor');
  }
  
  /**
   * Returns refresh time for given lifetime
   * 
   * @param integer $lifetime life time (in seconds)
   * @return integer
   */
  public function getRefreshTime($lifetime)
  {
    return time() - $lifetime;    
  }
  
  /**
   * Returns the current life time.
   *
   * @return int The current life time (in seconds)
   */
  public function getLifeTime()
  {
    return $this->getOption('lifetime');
  }
  
  
}
