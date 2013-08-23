<?php
/*
 * This file is part of the Sift PHP framework.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * Builds (read: constructor injection) the object.
 *
 * @package Sift
 * @subpackage dependency_injection
 */
class sfDependencyInjectionBuilderConstructor extends sfDependencyInjectionBuilder {

  /**
   * Creates the object
   *
   * @throws sfInitializationException If the object cannot be created
   */
  public function constructObject($arguments = null)
  {
    $this->loadMap();

    $reflector = new sfReflectionClass($this->className);
    if(is_array($arguments) && count($arguments))
    {     
      $this->object = null === $reflector->getConstructor() ?
                        $reflector->newInstance() : $reflector->newInstanceArgs($arguments);
    }
    elseif($this->map->has('constructor'))
    {
      $constructWith = array();
      foreach($this->map->getItemsFor('constructor') as $item)
      {
        $dependency = $this->getDependencyForItem($item);
        if(is_null($dependency))
        {
          throw new LogicException(sprintf('Missing dependency for "%s". Set dependencies using sfDependecyInjectionContainer::getInstance()->getDependencies()->set()', $item->getDependencyName()));
        }
        $constructWith[] = $dependency;
      }
      $this->object = $reflector->newInstanceArgs($constructWith);
    }
    else
    {
      if($reflector->isInstantiable())
      {
        $this->object = $reflector->newInstance();
      }
      else
      {
        throw new sfInitializationException(sprintf('The object of class "%s" cannot be initialized.', $this->className));
      }
    }
  }

  /**
   * Creates the object and sets all the dependencies required
   * for construction.
   *
   * @param string $className
   * @param array $arguments
   * @param string $containerName
   * @return mixed object
   */
  public static function construct($className, $arguments = null, $containerName = sfDependencyInjectionContainer::DEFAULT_NAME)
  {
    $constructor = new self();
    $constructor->setClassName($className);
    $constructor->setContainer(sfDependencyInjectionContainer::getInstance($containerName));
    $constructor->constructObject($arguments);
    return $constructor->getObject();
  }

}
