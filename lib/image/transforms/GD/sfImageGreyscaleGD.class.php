<?php
/*
 * This file is part of the Sift PHP framework.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * sfImageGreyscaleGF class.
 *
 * Greyscales a GD image.
 *
 * Reduces the level of detail of an image.
 *
 * @package Sift
 * @subpackage image
 */
class sfImageGreyscaleGD extends sfImageTransformAbstract
{
  /**
   * Apply the transform to the sfImage object.
   *
   * @param sfImage
   * @return sfImage
   */
  protected function transform(sfImage $image)
  {
    $resource = $image->getAdapter()->getHolder();

    $resourcex = imagesx($resource);
    $resourcey = imagesy($resource);

    if (function_exists('imagefilter'))
    {
      imagefilter($resource, IMG_FILTER_GRAYSCALE);
    }

    else
    {
      throw new sfImageTransformException(sprintf('Cannot perform transform, GD does not support imagefilter '));
    }

    return $image;
  }
}
