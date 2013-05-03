<?php
/*
 * This file is part of the Sift PHP framework.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * sfImageFlipImageMagick class.
 *
 * Flips image.
 *
 * Flips the image vertically.
 *
 * @package Sift
 * @subpackage image
 */
class sfImageFlipImageMagick extends sfImageTransformAbstract
{
  /**
   * Apply the transform to the sfImage object.
   *
   * @param integer
   * @return sfImage
   */
  protected function transform(sfImage $image)
  {
    $resource = $image->getAdapter()->getHolder();

    $resource->flipImage();

    return $image;
  }
}
