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

  /**
   * @var sfDependencyInjectionMap
   */
  protected $map;

  /**
   * Constructor
   */
  public function __construct()
  {
    $this->map = new sfDependencyInjectionMap();
  }

  /**
   * Builds the map
   *
   * @return sfDependencyInjectionMapBuilder
   */
  protected abstract function build();

  /**
   * The map
   *
   * @return sfDependencyInjectionMap
   */
  public function getMap()
  {
    return $this->map;
  }

  /**
   * Creates a map item based off options array
   *
   * @param array $options Array of options
   * @return sfDependencyInjectionMapItem
   */
  protected function createItemFromArray($options)
  {
    return sfDependencyInjectionMapItem::createFromArray($options);
  }

}