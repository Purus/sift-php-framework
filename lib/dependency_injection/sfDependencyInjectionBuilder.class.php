<?php
/*
 * This file is part of the Sift PHP framework.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * Provides common methods for constructing objects.
 *
 * @package Sift
 * @subpackage dependency_injection
 */
class sfDependencyInjectionBuilder {

  /**
   * @var sfDependencyInjectionMaps
   */
  protected $maps;

  /**
   * @var sfDependencyInjectionDependencies
   */
  protected $dependencies;

  /**
   * The class name
   *
   * @var string
   */
  protected $className;

  /**
   * Constructor
   *
   * @param string $className The class name
   * @param sfDependencyInjectionDependencies $dependencies
   * @param sfDependencyInjectionMaps $maps
   */
  public function __construct($className, sfDependencyInjectionDependencies $dependencies, sfDependencyInjectionMaps $maps)
  {
    $this->className = $className;
    $this->dependencies = $dependencies;
    $this->maps = $maps;
    $this->setup();
  }

  /**
   * Setup the builder. Creates a map if it does not exist.
   *
   */
  protected function setup()
  {
    if(!$this->maps->has($this->getClassName()))
    {
      if(sfConfig::get('sf_debug'))
      {
        $timer = sfTimerManager::getTimer('Object builder');
      }

      $builder = new sfDependencyInjectionMapBuilderClass($this->getClassName());
      $builder->build();

      if(isset($timer))
      {
        $timer->addTime();
      }

      $this->maps->set($this->getClassName(), $builder->getMap());
    }
    $this->map = $this->maps->get($this->getClassName());
  }

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
   * Returns the class name
   *
   * @return string
   */
  public function getClassName()
  {
    return $this->className;
  }

  /**
   * Creates the object
   *
   * @throws sfInitializationException If the object cannot be created
   * @throws LogicException If the dependency is missing
   */
  public function constructObject($arguments = null)
  {
    $reflector = new sfReflectionClass($this->getClassName());

    if(is_array($arguments) && count($arguments))
    {
      // FIXME: this silently dies if the arguments does not contain the required arguments for the constructor!
      $object = null === $reflector->getConstructor() ? $reflector->newInstance() : $reflector->newInstanceArgs($arguments);
    }
    elseif($this->map->has(sfDependencyInjectionMapItem::INJECT_WITH_CONSTRUCTOR))
    {
      $constructWith = array();
      foreach($this->map->getItemsFor(sfDependencyInjectionMapItem::INJECT_WITH_CONSTRUCTOR) as $item)
      {
        $dependency = $this->getDependencyForItem($item);
        if($item->isRequired() && is_null($dependency))
        {
          throw new LogicException(sprintf('Missing dependency "%s" for construction of object instance "%s".', $item->getDependencyName(), $this->getClassName()));
        }
        $constructWith[] = $dependency;
      }

      $object = $reflector->newInstanceArgs($constructWith);
    }
    else
    {
      if($reflector->isInstantiable())
      {
        $object = $reflector->newInstance();
      }
      else
      {
        throw new sfInitializationException(sprintf('The object of class "%s" cannot be initialized.', $this->className));
      }
    }

    $this->injectMethods($object, $reflector);
    $this->injectProperties($object, $reflector);

    return $object;
  }

  /**
   * Injects methods
   *
   * @param mixed $object
   * @param sfReflection $reflector
   */
  private function injectMethods(&$object, $reflector)
  {
    foreach($this->map->getItemsFor(sfDependencyInjectionMapItem::INJECT_WITH_METHOD) as $item)
    {
      /* @var $item sfDependencyInjectionMapItem */
      // only inject if the class has the method, or the item allows forcing
      if($reflector->hasMethod($item->getInjectAs()) || $item->getForce())
      {
        $dependency = $this->getDependencyForItem($item);
        if($item->isRequired() && !$dependency)
        {
          throw new LogicException(sprintf('Missing dependency "%s" for method injection "%s" of object instance "%s".',
              $item->getDependencyName(),
              $item->getInjectAs(),
              $this->getClassName()
          ));
        }
        $object->{$item->getInjectAs()}($dependency);
      }
    }
  }

  /**
   * Injects properties to the object
   *
   * @param mixed $object
   * @param sfReflection $reflector
   */
  private function injectProperties(&$object, $reflector)
  {
    /* @var $item sfDependencyInjectionMapItem */
    foreach($this->map->getItemsFor('property') as $item)
    {
      // only inject if the class has the property, or the item allows forcing
      if($reflector->hasProperty($item->getInjectAs()) || $item->getForce())
      {
        $dependency = $this->getDependencyForItem($item);
        if($item->isRequired() && !$dependency)
        {
          throw new LogicException(sprintf('Missing dependency "%s" for property injection "%s" of object instance "%s".',
              $item->getDependencyName(),
              $item->getInjectAs(),
              $this->getClassName()
          ));
        }
        $object->{$item->getInjectAs()} = $dependency;
      }
    }
  }

  /**
   * Finds the dependency or cerate new class
   *
   * @param sfDependencyInjectionMapItem $item
   * @return mixed dependency
   */
  protected function getDependencyForItem(sfDependencyInjectionMapItem $item)
  {
    if($newClass = $item->getNewClass())
    {
      $builder = new self($newClass, $this->dependencies, $this->maps);
      $dependency = $builder->constructObject();
    }
    else
    {
      $dependency = $this->dependencies->get($item->getDependencyName());
    }

    return $dependency;
  }

}
