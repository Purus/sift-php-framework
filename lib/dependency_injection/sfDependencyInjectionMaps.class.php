<?php
/*
 * This file is part of the Sift PHP framework.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * Service maps
 *
 * @package Sift
 * @subpackage dependency_injection
 */
class sfDependencyInjectionMaps implements Countable, ArrayAccess {

  /**
   * Maps holder
   *
   * @var array
   */
  protected $maps = array();

  /**
   * Add/set a map to the container by name
   *
   * @param string $name
   * @param sfDependencyInjectionMap $map
   * @return sfDependencyInjectionMaps
   */
  public function set($name, sfDependencyInjectionMap $map)
  {
    $this->maps[$name] = $map;
    return $this;
  }

  /**
   * Does the name has a map?
   *
   * @param string $name
   * @return boolean
   */
  public function has($name)
  {
    return isset($this->maps[$name]);
  }

  /**
   * Returns a dependency map given a name
   *
   * @param string $name
   * @return sfDependencyInjectionMap
   */
  public function get($name)
  {
    if(isset($this->maps[$name]))
    {
      return $this->maps[$name];
    }
    else
    {
      return new sfDependencyInjectionMap();
    }
  }

  /**
   * Returns number of maps
   *
   * @return integer
   */
  public function count()
  {
    return count($this->maps);
  }

  public function offsetGet($offset)
  {
    return $this->maps[$offset];
  }

  public function offsetExists($offset)
  {
    return isset($this->maps[$offset]);
  }

  public function offsetSet($offset, $value)
  {
    throw new BadMethodCallException('Cannot set offset, use set()');
  }

  public function offsetUnset($offset)
  {
    unset($this->maps[$offset]);
  }

}
