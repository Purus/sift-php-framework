<?php
/*
 * This file is part of the Sift PHP framework.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * Creates a map from array
 *
 * @package Sift
 * @subpackage dependency_injection
 */
class sfDependencyInjectionMapBuilderArray extends sfDependencyInjectionMapBuilder {

  private $_arrayMaps = array();

  protected function _setup()
  {
    return null;
  }

  protected function _build()
  {
    foreach($this->_arrayMaps as $options)
    {
      $this->getMap()->append(
        $this->makeItemFromOptions($options)
      );
    }
    $this->_arrayMaps = array();
  }

  /**
   * Adds an item (array based).
   *
   * @param array $mapArray
   * @return sfDependencyInjectionMapBuilderArray
   */
  public function add($mapArray)
  {
    $this->_arrayMaps[] = $mapArray;
    return $this;
  }

}

