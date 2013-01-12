<?php
/*
 * This file is part of the Sift PHP framework.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 *
 * sfImageGaussianBlurGD class.
 *
 * Blurs the image using the Gaussian method.
 *
 * @package Sift
 * @subpackage image
 * @author Stuart Lowes <stuart.lowes@gmail.com>
 */
class sfImageGaussianBlurGD extends sfImageTransformAbstract
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

    if (function_exists('imagefilter'))
    {
      imagefilter($resource, IMG_FILTER_GAUSSIAN_BLUR);
    }

    else
    {
      throw new sfImageTransformException(sprintf('Cannot perform transform, GD does not support imagefilter '));
    }

    return $image;
  }
}