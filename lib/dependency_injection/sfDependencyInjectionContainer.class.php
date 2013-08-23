<?php
/*
 * This file is part of the Sift PHP framework.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * Service container
 *
 * @package Sift
 * @subpackage dependency_injection
 * @see http://www.potstuck.com/2010/09/09/php-dependency-a-php-dependency-injection-framework/
 */
class sfDependencyInjectionContainer {

  /**
   * Default container name
   *
   */
  const DEFAULT_NAME = '_default_';

  /**
   *
   * @var array
   */
  private static $instance = array();

  /**
   * @var sfDependencyInjectionMaps
   */
  private $maps;

  /**
   * @var sfDependencyInjectionDependencies
   */
  private $dependencies;

  /**
   * Container name
   *
   * @var string
   */
  private $name;

  /**
   * Constructor
   *
   * @param string $name
   */
  public function __construct($name = self::DEFAULT_NAME)
  {
    $this->name = $name;
  }

  /**
   * Returns one instance singleton
   *
   * @return sfDependencyInjectionContainer
   */
  public static function getInstance($container = self::DEFAULT_NAME)
  {
    if(!isset(self::$instance[$container]))
    {
      self::$instance[$container] = new self($container);
      self::$instance[$container]->setup();
    }

    return self::$instance[$container];
  }

  /**
   * Sets up the container by creating a new map
   * and dependency holder.  This function doesn't really
   * need to ever be called, since the get() function
   * calls it when creating a 'new' container.
   */
  public function setup()
  {
    $this->maps = new sfDependencyInjectionMaps();
    $this->dependencies = new sfDependencyInjectionDependencies();
  }

  /**
   * Sets container name
   *
   * @param string $name
   * @return sfDependencyInjectionContainer
   */
  public function setName($name)
  {
    $this->name = $name;
    return $this;
  }

  /**
   * Makes an object.
   *
   * @param string $className The name of the class to create
   * @param array $arguments Array of arguments
   * @param string $container The name of the container to use
   * @return object the created class
   */
  public static function create($className, $arguments = null, $container = self::DEFAULT_NAME)
  {
    if(sfConfig::get('sf_logging_enabled'))
    {
      sfLogger::getInstance()->info(sprintf('{sfDependencyInjectionContainer} Creating class "%s"', $className));
    }
    $object = sfDependencyInjectionBuilderConstructor::construct($className, $arguments, $container);
    sfDependencyInjectionBuilderSetter::inject($object, $container);
    return $object;
  }

  /**
   * Get name
   *
   * @return string
   */
  public function getName()
  {
    return $this->name;
  }

  /**
   * @return sfDependencyInjectionMaps
   */
  public function getMaps()
  {
    return $this->maps;
  }

  /**
   * @return sfDependencyInjectionDependencies
   */
  public function getDependencies()
  {
    return $this->dependencies;
  }

}
