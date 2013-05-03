<?php
/*
 * This file is part of the Sift PHP framework.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * sfImageTransform class.
 *
 * Abstract class.
 *
 * Abstract class all sfImageTranform transform classes are extended from.
 *
 * @package Sift
 * @subpackage image
 */
abstract class sfImageTransformAbstract {

  /**
   * Apply the transform to the sfImage object.
   *
   * @param sfImage
   * @return sfImage
   */
  public function execute(sfImage $image)
  {
    // Check we have a valid image holder
    if(false === $image->getAdapter()->hasHolder())
    {
      throw new sfImageTransformException(sprintf('Cannot perform transform: %s invalid image resource', get_class($this)));
    }
    return $this->transform($image);
  }

  /**
   * Abstract method that performs the image manipulation.
   *
   * @param sfImage
   * @ignore
   * @return sfImage
   */
  abstract protected function transform(sfImage $image);

}
