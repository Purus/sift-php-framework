<?php
/*
 * This file is part of the Sift PHP framework.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 *
 * sfImageCrop class.
 *
 * Flips image.
 *
 * Flips the image vertically.
 *
 * @package Sift
 * @subpackage image
 * @author Stuart Lowes <stuart.lowes@gmail.com>
 */
class sfImageFlipGD extends sfImageTransformAbstract
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

    $x = imagesx($resource);
    $y = imagesy($resource);

    $dest_resource = $image->getAdapter()->getTransparentImage($x, $y);

    for ($h = 0; $h < $y; $h++)
    {
      imagecopy($dest_resource, $resource, 0, $h, 0, $y - $h - 1, $x, 1);
    }
    // Tidy up
    imagedestroy($resource);

    // Replace old image with flipped version
    $image->getAdapter()->setHolder($dest_resource);

    return $image;
  }
}
