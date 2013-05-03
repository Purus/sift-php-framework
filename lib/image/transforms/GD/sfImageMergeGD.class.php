<?php
/*
 * This file is part of the Sift PHP framework.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
 
/**
 * sfImageMergeGD class.
 *
 * Merges GD image on top of another GD image.
 *
 * Handles transparency correctly.
 *
 * @package Sift
 * @subpackage image
 */
class sfImageMergeGD extends sfImageOverlayGD
{
  protected $opacity = 100;
  
  /**
   * Construct an sfImageOverlay object.
   *
   * @param array mixed
   */
  public function __construct(sfImage $overlay, $opacity = 100, $position='top-left')
  {
    $this->setOverlay($overlay);

    if (is_array($position) && count($position))
    {

      $this->setLeft($position[0]);

      if (isset($position[1]))
      {
        $this->setTop($position[1]);
      }
    }

    else
    {
      $this->setPosition($position);
    }
    
    $this->setOpacity($opacity);
  }
  
  public function setOpacity($opacity)
  {
    $this->opacity = $opacity;
  }
  
  public function getOpacity()
  {
    return $this->opacity;
  }
  
  /**
   * Apply the transform to the sfImage object.
   *
   * @param integer
   * @return sfImage
   */
  protected function transform(sfImage $image)
  {
    // compute the named coordinates
    $this->computeCoordinates($image);
    $resource = $image->getAdapter()->getHolder();
    
    // Check we have a valid image resource
    if(false === $this->overlay->getAdapter()->getHolder())
    {
      throw new sfImageTransformException(sprintf('Cannot perform transform: %s', get_class($this)));
    }

    // create new transparent image
    $new = $image->getAdapter()->getTransparentImage($image->getWidth(), $image->getHeight());
    
    // create true color overlay image:
    $overlay_w   = $this->overlay->getWidth();
    $overlay_h   = $this->overlay->getHeight();
    $overlay_img = $this->overlay->getAdapter()->getHolder();

    imagealphablending($new, true); 
    
    imagecopy($new, $resource, 0, 0, 0, 0, $image->getWidth(), $image->getHeight());
    
    $opacity = $this->getOpacity();
    if($opacity < 100)
    {    
      imagecopymergealpha($new, $overlay_img, $this->left, $this->top, 0, 0, $overlay_w, $overlay_h, $opacity);
    }
    else
    {
      imagecopy($new, $overlay_img, $this->left, $this->top, 0, 0, $overlay_w, $overlay_h);
    }
    
    imagesavealpha($new, true);    
    $image->getAdapter()->setHolder($new);
    // tidy up
    imagedestroy($resource);    
    return $image;
  }
  
}
