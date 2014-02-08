<?php

/*
 * This file is part of the Sift PHP framework.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * Items define how each dependency should be injected/maintained.
 *
 * Options:
 *
 *  name - name of the dependency
 *  inject_with - method, property, constructor
 *  inject_as - depends on with param
 *  force - bool, force injection
 *  new_class - the name of the new class to create, false otherwise
 *  required - required or can be null?
 *
 * @package Sift
 * @subpackage dependency_injection
 */
class sfDependencyInjectionMapItem
{
  /**
   * Method injection
   */
  const INJECT_WITH_METHOD = 'method';

  /**
   * Property injection
   */
  const INJECT_WITH_PROPERTY = 'property';

  /**
   * Constructor injection
   */
  const INJECT_WITH_CONSTRUCTOR = 'constructor';

  /**
   * Dependency name
   *
   * @var string
   */
  protected $dependencyName;

  /**
   * Inject type
   * @var string
   */
  protected $injectWith;

  /**
   * Inject as
   * @var string
   */
  protected $injectAs;

  /**
   * Force the injection
   *
   * @var boolean
   */
  protected $force = false;

  /**
   * New class
   *
   * @var string
   */
  protected $newClass;

  /**
   * Required flag
   *
   * @var boolean
   */
  protected $required = true;

  /**
   * Constructor
   *
   * @param string $dependencyName The dependency name
   * @param string $injectWith
   * @param string $injectAs
   * @param boolean $force
   * @param string $newClass
   * @param boolean $required
   */
  public function __construct($dependencyName = null, $injectWith = null, $injectAs = null, $force = false, $newClass = null, $required = true)
  {
    $this->dependencyName = $dependencyName;
    $this->injectWith = $injectWith;
    $this->injectAs = $injectAs;
    $this->force = self::convertToBoolean($force);
    $this->newClass = $newClass;
    $this->required = self::convertToBoolean($required);
  }

  /**
   * Creates the item from array
   *
   * @param array $array Array of properties
   * @return sfDependencyInjectionMapItem
   */
  public static function createFromArray($array)
  {
    $array = array_merge(self::getDefaultOptions(), $array);

    return new self($array['dependency_name'], $array['inject_with'], $array['inject_as'], $array['force'], $array['new_class'], $array['required']);
  }

  /**
   * Sets dependency name
   *
   * @param string $dependencyName
   * @return sfDependencyInjectionMapItem
   */
  public function setDependencyName($dependencyName)
  {
    $this->dependencyName = $dependencyName;

    return $this;
  }

  /**
   * Sets inject with object
   *
   * @param string $injectWith
   * @return sfDependencyInjectionMapItem
   */
  public function setInjectWith($injectWith)
  {
    $this->injectWith = $injectWith;

    return $this;
  }

  /**
   * Sets inject as
   *
   * @param string $injectAs
   * @return sfDependencyInjectionMapItem
   */
  public function setInjectAs($injectAs)
  {
    $this->injectAs = $injectAs;

    return $this;
  }

  /**
   * Sets force
   *
   * @param boolean $force
   * @return sfDependencyInjectionMapItem
   */
  public function setForce($force)
  {
    $this->force = $force;

    return $this;
  }

  /**
   *
   * @param string $newClass
   * @return sfDependencyInjectionMapItem
   */
  public function setNewClass($newClass)
  {
    $this->newClass = $newClass;

    return $this;
  }

  /**
   *
   * @return string
   */
  public function getDependencyName()
  {
    return $this->dependencyName;
  }

  /**
   * Inject with
   *
   * @return string
   */
  public function getInjectWith()
  {
    return $this->injectWith;
  }

  /**
   * Inject as
   *
   * @return string
   */
  public function getInjectAs()
  {
    return $this->injectAs;
  }

  /**
   * Force the injection?
   *
   * @return boolean
   */
  public function getForce()
  {
    return $this->force;
  }

  /**
   * Returns new class
   *
   * @return
   */
  public function getNewClass()
  {
    return $this->newClass;
  }

  /**
   * Set required flag
   *
   * @param boolean $flag
   */
  public function setRequired($flag = true)
  {
    $this->required = self::convertToBoolean($flag);

    return $this;
  }

  /**
   * Is the item required?
   *
   * @return boolean
   */
  public function isRequired()
  {
    return $this->required;
  }

  /**
   * Returns default options
   *
   * @return array
   */
  public static function getDefaultOptions()
  {
    return array(
      'dependency_name' => null,
      'force' => false,
      'inject_with' => null,
      'inject_as' => null,
      'new_class' => false,
      'required' => true,
    );
  }

  /**
   * Converts the value to boolean
   *
   * @param string $value
   * @return boolean
   */
  public static function convertToBoolean($value)
  {
    if (in_array(strtolower($value), array('true'))) {
      return true;
    } elseif (in_array(strtolower($value), array('false'))) {
      return false;
    }

    return (boolean) $value;
  }

}
