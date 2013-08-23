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
class sfDependencyInjectionMaps {

  /**
   * Maps holder
   *
   * @var array
   */
  private $maps = array();

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

}
