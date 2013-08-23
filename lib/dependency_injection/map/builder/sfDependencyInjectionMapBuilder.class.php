<?php

/*
 * This file is part of the Sift PHP framework.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * Abstract class for building maps
 *
 * @package Sift
 * @subpackage dependency_injection
 */
abstract class sfDependencyInjectionMapBuilder {

  protected abstract function _setup();

  protected abstract function _build();

  /**
   * @var Pd_Map
   */
  protected $_map;

  /**
   * The map
   *
   * @return sfDependencyInjectionMap
   */
  public function getMap()
  {
    return $this->_map;
  }

  /**
   * Setup for building a make.  Makes a new
   * Pd_Map and then runs the builders _setup method
   */
  public function setup()
  {
    $this->_map = new sfDependencyInjectionMap();
    $this->_setup();
  }

  /**
   * Builds the map by running the setup method and
   * then running the builers _build method.
   *
   */
  public function build()
  {
    $this->setup();
    $this->_build();
  }

  /**
   * Creates a Map Item based off options array
   *
   * @param array $options
   * @return Pd_Map_Item
   */
  protected function makeItemFromOptions($options)
  {
    $options = array_merge( array(
        'dependencyName' => null,
        'injectWith' => null,
        'injectAs' => null,
        'force' => false,
        'newClass' => null,
    ), $options);

    $item = new sfDependencyInjectionMapItem();
    $item->setDependencyName($options['dependencyName']);
    $item->setInjectWith($options['injectWith']);
    $item->setInjectAs($options['injectAs']);
    $item->setForce($options['force']);
    $item->setNewClass($options['newClass']);
    return $item;
  }

}