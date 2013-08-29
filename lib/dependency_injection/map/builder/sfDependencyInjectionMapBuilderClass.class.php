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

  /**
   * The class name
   *
   * @var string
   */
  protected $className;

  /**
   * The reflection object
   *
   * @var sfReflectionClass
   */
  protected $reflection;

  /**
   * The doc comment parser
   *
   * @var sfDependencyInjectionInjectCommandParser
   */
  protected $parser;

  /**
   * Constructor
   *
   * @param string $className The class name
   * @param sfDependencyInjectionInjectCommandParser $parser The doc block parser
   */
  public function __construct($className, sfDependencyInjectionInjectCommandParser $parser = null)
  {
    parent::__construct();

    $this->className = $className;
    $this->reflection = new sfReflectionClass($className);
    $this->parser = is_null($parser) ? new sfDependencyInjectionInjectCommandParser() : $parser;
  }

  /**
   * Set a class name
   *
   * @param string $className
   * @return sfDependencyInjectionMapBuilderClass
   */
  public function setClassName($className)
  {
    $this->className = $className;
    $this->reflection = new sfReflectionClass($className);
    return $this;
  }

  /**
   * Runs all the builders and builds the entire map
   *
   * @return sfDependencyInjectionMapBuilderClass
   */
  public function build()
  {
    $this->buildMethods();
    $this->buildProperties();
    $this->buildClass();

    return $this;
  }

  /**
   * Pass in a reflection item (class, property, method)
   * and this function will build a parser and return its
   * results.
   *
   * @param ReflectionMethod $classProperty
   * @return array all options
   */
  protected function optionsFrom($classProperty)
  {
    if(!$docComment = $classProperty->getDocComment())
    {
      return false;
    }

    $this->parser->setString($docComment)
                 ->setDebugInformation($classProperty);

    return $this->parser->parse();
  }

  /**
   * Loops through all of the methods and builds items/maps for them.
   *
   */
  protected function buildMethods()
  {
    $methods = $this->reflection->getMethods();
    foreach($methods as $method)
    {
      $commands = $this->optionsFrom($method);

      if(!$commands)
      {
        continue;
      }

      foreach($commands as $options)
      {
        if($method->getName() == '__construct')
        {
          $options['inject_with'] = sfDependencyInjectionMapItem::INJECT_WITH_CONSTRUCTOR;
        }
        else
        {
          $options['inject_with'] = sfDependencyInjectionMapItem::INJECT_WITH_METHOD;
          $options['inject_as'] = $method->getName();
        }
        $this->map->append(
          $this->createItemFromArray($options)
        );
      }
    }
  }

  /**
   * Loops through all of the properties and builds items/maps for them.
   *
   */
  protected function buildProperties()
  {
    $properties = $this->reflection->getProperties();
    foreach($properties as $property)
    {
      $commands = $this->optionsFrom($property);
      if(!$commands)
      {
        continue;
      }
      foreach($commands as $options)
      {
        $options['inject_with'] = sfDependencyInjectionMapItem::INJECT_WITH_PROPERTY;
        $options['inject_as'] = $property->getName();
        $this->map->append(
          $this->createItemFromArray($options)
        );
      }
    }
  }

  /**
   * Builds items based on the classes doc block
   */
  protected function buildClass()
  {
    $commands = $this->optionsFrom($this->reflection);
    if(!$commands)
    {
      return;
    }
    foreach($commands as $options)
    {
      $this->map->append(
        $this->createItemFromArray($options)
      );
    }
  }

}
