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
 * Options
 *  Name - name of the dependency
 *  InjectWith - method, property, constructor
 *  InjectAs - depends on with param
 *  Force - bool, force injection
 *  NewClass - the name of the new class to create, false otherwise
 *
 * @package Sift
 * @subpackage dependency_injection
 */
class sfDependencyInjectionMapItem {

  /**
   * Dependency name
   *
   * @var string
   */
  private $dependencyName;

  private $injectWith;
  private $injectAs;
  private $force = false;
  private $newClass = null;

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
   *
   * @return string
   */
  public function getInjectWith()
  {
    return $this->injectWith;
  }

  /**
   *
   * @return string
   */
  public function getInjectAs()
  {
    return $this->injectAs;
  }

  /**
   *
   * @return type
   */
  public function getForce()
  {
    return $this->force;
  }

  /**
   *
   * @return
   */
  public function getNewClass()
  {
    return $this->newClass;
  }

}
