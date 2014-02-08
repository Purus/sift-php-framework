<?php
/*
 * This file is part of the Sift PHP framework.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * Service dependencies
 *
 * @package Sift
 * @subpackage dependency_injection
 */
class sfDependencyInjectionDependencies {

  /**
   * Array of dependencies
   *
   * @var array
   */
  private $dependencies = array();

  /**
   * service container
   *
   * @var sfServiceContainer
   */
  protected $serviceContainer;

  /**
   * Constructor
   *
   * @param sfDependencyInjectionContainer $container
   */
  public function __construct(sfServiceContainer $container)
  {
    $this->serviceContainer = $container;
  }

  /**
   * Returns a dependency by name. If dependency is not found, null is returned.
   *
   * @param string $name
   * @return mixed dependency
   */
  public function get($name)
  {
    if(isset($this->dependencies[$name]))
    {
      if($this->dependencies[$name] instanceof sfServiceReference)
      {
        return $this->serviceContainer->get((string)$this->dependencies[$name]);
      }
      return $this->dependencies[$name];
    }
    else
    {
      return null;
    }
  }

  /**
   * Sets a depenedency by name
   *
   * @param string $name
   * @param mixed $dependency resource
   * @return sfDependencyInjectionDependencies
   */
  public function set($name, $dependency)
  {
    $this->dependencies[$name] = $dependency;
    return $this;
  }

  /**
   * Clears
   *
   * @return sfDependencyInjectionDependencies
   */
  public function clear()
  {
    $this->dependencies = array();
    return $this;
  }

}
