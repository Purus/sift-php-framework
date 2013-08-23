<?php

/*
 * This file is part of the Sift PHP framework.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * Injects (read: setter injection) all of the dependencies
 * into the object.
 *
 * @package Sift
 * @subpackage dependency_injection
 */
class sfDependencyInjectionBuilderSetter extends sfDependencyInjectionBuilder {

  /**
   * Injects all of the properties and methods
   */
  public function injectObject()
  {
    // load the map
    $this->loadMap();
    $this->injectMethods();
    $this->injectProperties();
  }

  private function injectMethods()
  {
    /* @var $item sfDependencyInjectionMapItem */
    foreach($this->map->getItemsFor('method') as $item)
    {
      // only inject if the class has the method, or the item allows forcing
      $reflector = new sfReflectionClass($this->className);
      if($reflector->hasMethod($item->getInjectAs()) || $item->getForce())
      {
        $this->object->{$item->getInjectAs()}($this->getDependencyForItem($item));
      }
    }
  }

  private function injectProperties()
  {
    /* @var $item fServiceMapItem */
    foreach($this->map->getItemsFor('property') as $item)
    {
      // only inject if the class has the property, or the item allows forcing
      $reflector = new sfReflectionClass($this->className);
      if($reflector->hasProperty($item->injectAs()) || $item->force())
      {
        $this->object->{$item->injectAs()} = $this->getDependencyForItem($item);
      }
    }
  }

  /**
   * Injects everything into the passed object/instance
   *
   * @param mixed $object instance
   * @param string $containerName the container that holds the maps/dependencies
   */
  public static function inject($object, $containerName = sfDependencyInjectionContainer::DEFAULT_NAME)
  {
    $injector = new self();
    $injector->setObject($object);
    $injector->setContainer(sfDependencyInjectionContainer::getInstance($containerName));
    $injector->injectObject();
  }

}
