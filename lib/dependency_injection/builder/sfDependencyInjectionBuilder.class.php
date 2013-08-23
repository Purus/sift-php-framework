<?php
/*
 * This file is part of the Sift PHP framework.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * Provides common methods that can be used when
 * constructing or injecting objects.
 *
 * @package Sift
 * @subpackage dependency_injection
 */
abstract class sfDependencyInjectionBuilder {

  /**
   * @var sfDependencyInjectionMap
   */
  protected $map;

  /**
   * @var sfDependencyInjectionContainer
   */
  protected $container;
  protected $object;
  protected $className;

  /**
   * The name of the class
   *
   * @param string $className
   * @return sfDependencyInjectionBuilder
   */
  public function setClassName($className)
  {
    $this->className = $className;
    return $this;
  }

  /**
   * The container that the make functions will put/pull
   * the maps and dependencies from.
   *
   * @param sfDependencyInjectionContainer $container
   * @return sfDependencyInjectionBuilder
   */
  public function setContainer(sfDependencyInjectionContainer $container)
  {
    $this->container = $container;
    return $this;
  }

  /**
   * The object (for injection)
   *
   * @param object $object
   * @return sfDependencyInjectionBuilder
   */
  public function setObject($object)
  {
    $this->object = $object;
    $this->setClassName(get_class($object));
    return $this;
  }

  /**
   * Return the object
   *
   * @return object
   */
  public function getObject()
  {
    return $this->object;
  }

  /**
   * Loads map from container
   *
   * @return void
   */
  private function getMapFromContainer()
  {
    if(!$this->map)
    {
      $this->map = $this->container->getMaps()->get($this->className);
    }
  }

  /**
   * Save map to the container
   *
   * @return void
   */
  private function saveMapToContainer()
  {
    $this->container->getMaps()->set($this->className, $this->map);
  }

  /**
   * Loads a map based on the set class name
   *
   * If there is no map in the container, then it will try to build
   * a map by reading the class.
   */
  protected function loadMap()
  {
    $this->getMapFromContainer();
    if($this->map->count() == 0)
    {
      $this->buildMap();
      $this->saveMapToContainer();
    }
  }

  /**
   * Builds the map using sfDependencyInjectionBuilderClass
   *
   */
  private function buildMap()
  {
    $builder = new sfDependencyInjectionMapBuilderClass();
    $builder->setClass($this->className);
    $builder->setup();
    $builder->build();
    $this->map = $builder->getMap();
  }

  /**
   * Finds the dependency, new class or pulls from container, based
   * on item.
   *
   * @param sfDependencyInjectionMapItem $item
   * @return mixed dependency
   */
  protected function getDependencyForItem(sfDependencyInjectionMapItem $item)
  {
    if($newClass = $item->getNewClass())
    {
      $dependency = sfDependencyInjectionBuilder::build($newClass, $this->container->getName());
    }
    else
    {
      $dependency = $this->container->getDependencies()->get($item->getDependencyName());
    }
    return $dependency;
  }

}

