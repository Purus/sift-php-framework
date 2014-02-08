<?php
/*
 * This file is part of the Sift PHP framework.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * sfImageSketchyGD class.
 *
 * Embosses the image.
 *
 * @package    Sift
 * @subpackage image
 */
class sfImageSketchyGD extends sfImageTransformAbstract
{
    /**
     * Apply the transform to the sfImage object.
     *
     * @param sfImage
     *
     * @return sfImage
     */
    protected function transform(sfImage $image)
    {
        $resource = $image->getAdapter()->getHolder();

        if (function_exists('imagefilter')) {
            imagefilter($resource, IMG_FILTER_MEAN_REMOVAL);
        } else {
            throw new sfImageTransformException(sprintf('Cannot perform transform, GD does not support imagefilter '));
        }

        return $image;
    }
}
