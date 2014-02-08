<?php
/*
 * This file is part of the Sift PHP framework.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * Color palette interface
 *
 * @package Sift
 * @subpackage color
 */
interface sfIColorPallete
{
  public function getSwatches();
  public function getClosestColor($color);
  public function getId();
}
