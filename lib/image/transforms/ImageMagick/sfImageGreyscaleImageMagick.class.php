<?php
/*
 * This file is part of the Sift PHP framework.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * sfImageGreyscaleImageMagick class.
 *
 * Converts an ImageMagick image to greyscale.
 *
 * @package    Sift
 * @subpackage image
 */
class sfImageGreyscaleImageMagick extends sfImageTransformAbstract
{
    /**
     * Apply the transform to the sfImage object.
     *
     * @access protected
     *
     * @param sfImage
     *
     * @return sfImage
     */
    protected function transform(sfImage $image)
    {
        $resource = $image->getAdapter()->getHolder();

        $resource->modulateImage(100, 0, 100);

        return $image;
    }

}
