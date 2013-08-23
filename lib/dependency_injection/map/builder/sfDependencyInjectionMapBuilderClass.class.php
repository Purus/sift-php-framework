<?php
/*
 * This file is part of the Sift PHP framework.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * This class will read a class and build a dependency map
 * (of items) based off the doc blocks.
 *
 * @package Sift
 * @subpackage dependency_injection
 */
class sfDependencyInjectionMapBuilderClass extends sfDependencyInjectionMapBuilder {

  private $_class;

  /**
   * @var sfReflectionClass
   */
  private $_reflect;

  /**
   * Set a class name
   *
   * @param string $class
   */
  public function setClass($class)
  {
    $this->_class = $class;
  }

  /**
   * Sets up the builder for... building.
   *
   * Makes a new map and reflection class
   */
  protected function _setup()
  {
    $this->_reflect = new sfReflectionClass($this->_class);
  }

  /**
   * Runs all the builders and builds the entire map
   */
  protected function _build()
  {
    $this->buildMethods();
    $this->buildProperties();
    $this->buildClass();
  }

  /**
   * Pass in a reflection item (class, property, method)
   * and this function will build a parser and return its
   * results.
   *
   * @param ReflectionClass $classProperty
   * @return array all options
   */
  private function optionsFrom($classProperty)
  {
    $parser = new sfDependencyInjectionMapBuilderParser();
    $parser->setString($classProperty->getDocComment());
    $parser->setInfo($classProperty);
    $parser->match();
    $parser->buildOptions();
    return $parser->getOptions();
  }

  /**
   * Loops through all of the methods and builds items/maps
   * for them.
   */
  public function buildMethods()
  {
    $methods = $this->_reflect->getMethods();
    foreach($methods as $method)
    {
      foreach($this->optionsFrom($method) as $options)
      {
        if($method->getName() == '__construct')
        {
          $options['injectWith'] = 'constructor';
        }
        else
        {
          $options['injectWith'] = 'method';
          $options['injectAs'] = $method->getName();
        }
        $this->_map->append(
          $this->makeItemFromOptions($options)
        );
      }
    }
  }

  /**
   * Loops through all of the properties and builds items/maps
   * for them.
   */
  public function buildProperties()
  {
    $properties = $this->_reflect->getProperties();
    foreach($properties as $property)
    {
      foreach($this->optionsFrom($property) as $options)
      {
        $options['injectWith'] = 'property';
        $options['injectAs'] = $property->getName();
        $this->_map->append(
            $this->makeItemFromOptions($options)
        );
      }
    }
  }

  /**
   * Builds items based on the classes doc block
   */
  public function buildClass()
  {
    foreach($this->optionsFrom($this->_reflect) as $options)
    {
      $this->_map->append(
          $this->makeItemFromOptions($options)
      );
    }
  }

}
