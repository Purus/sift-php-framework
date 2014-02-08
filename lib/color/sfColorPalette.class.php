<?php
/*
 * This file is part of the Sift PHP framework.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * Color palette - base class for palettes
 *
 * @package Sift
 * @subpackage color
 */
abstract class sfColorPalette implements sfIColorPallete {

  protected $swatches = array();

  public function getSwatches()
  {
    return $this->swatches;
  }

  public function getId()
  {
    return get_class($this);
  }

  public function getClosestColor($color)
  {
    $swatches = $this->getSwatches();
    $myColor  = $color instanceof sfColor ? $color : new sfColor($color);
    if(count($swatches))
    {
      $index = $myColor->getClosestMatch($swatches);
      $myColor = new sfColor($swatches[$index]);
    }

    return $myColor;
  }

}
