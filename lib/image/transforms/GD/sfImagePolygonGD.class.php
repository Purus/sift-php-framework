<?php
/*
 * This file is part of the Sift PHP framework.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * sfImagePolygonGD class.
 *
 * Draws a polygon.
 *
 * Draws a polygon on a GD image.
 *
 * @package Sift
 * @subpackage image
 */
class sfImagePolygonGD extends sfImageTransformAbstract
{
  /**
   * Points
   *
   * @var array
  */
  protected $points = array();

  /**
   * Rectangle thickness.
   *
   * @var integer
  */
  protected $thickness = 1;

  /**
   * Hex color.
   *
   * @var string
  */
  protected $color = '';

  /**
   * Fill.
   *
   * @var string/object hex or sfImage object
  */
  protected $fill = null;

  /**
   * Line style.
   *
   * @var integer
  */
  protected $style = null;

  /**
   * Construct an sfImageRectangleGD object.
   *
   * @param integer
   * @param integer
   * @param integer
   * @param integer
   * @param integer
   * @param integer
   * @param string/object hex or sfImage object
   * @param integer
   */
  public function __construct($points, $thickness=1, $color='#ff0000', $fill=null)
  {
    $this->setPoints($points);
    $this->setThickness($thickness);
    $this->setColor($color);
    $this->setFill($fill);
  }

  /**
   * Sets the points
   *
   * @param array
   * @return true
   */
  public function setPoints($points)
  {
    $this->points = $points;

    return true;
  }

  /**
   * Gets the points
   *
   * @return array
   */
  public function getPoints()
  {
    return $this->points;
  }

  /**
   * Sets the thickness
   *
   * @param integer
   * @return boolean
   */
  public function setThickness($thickness)
  {
    if (is_numeric($thickness)) {
      $this->thickness = (int) $thickness;

      return true;
    }

    return false;
  }

  /**
   * Gets the thickness
   *
   * @return integer
   */
  public function getThickness()
  {
    return $this->thickness;
  }

  /**
   * Sets the color
   *
   * @param string
   * @return boolean
   */
  public function setColor($color)
  {
    if (preg_match('/#[\d\w]{6}/',$color)) {
      $this->color = $color;

      return true;
    }

    return false;
  }

  /**
   * Gets the color
   *
   * @return integer
   */
  public function getColor()
  {
    return $this->color;
  }

  /**
   * Sets the fill
   *
   * @param mixed
   * @return boolean
   */
  public function setFill($fill)
  {
    if (preg_match('/#[\d\w]{6}/',$fill)) {
      $this->fill = $fill;

      return true;
    }

    return false;
  }

  /**
   * Gets the fill
   *
   * @return mixed
   */
  public function getFill()
  {
    return $this->fill;
  }

  /**
   * Apply the transform to the sfImage object.
   *
   * @param sfImage
   * @return sfImage
   */
  protected function transform(sfImage $image)
  {
    $resource = $image->getAdapter()->getHolder();

    imagefilledpolygon($resource, $this->points, count($this->points)/2,
            $image->getAdapter()->getColorByHex($resource, $this->color));

    return $image;
  }

}
