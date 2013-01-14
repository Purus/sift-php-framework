<?php
/*
 * This file is part of the Sift PHP framework.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * sfDimensions is used for setting and getting dimensions
 *
 * @package    Sift
 * @subpackage core
 * @author     Dustin Whittle <dustin.whittle@symfony-project.com>
 */
class sfDimensions
{
  protected static $dimension = array(); // stores the current dimension
  protected static $dimensionDirectories = array(); // stores all the possible directories in order based on current dimension

  /**
   * Sets the current dimensions
   *
   * @param array $dimension
   * @return boolean true
   */
  public static function setDimension($dimension)
  {
    self::$dimension = $dimension;

    return true;
  }

  /**
   * Gets the current dimensions
   *
   * @return array Current dimension
   */
  public static function getDimension()
  {
    return self::$dimension;
  }

  /**
   * Gets all available dimension directories based on the current dimension
   *
   * @return array all available dimension directories in lookup order
   */
  public static function getDimensionDirs()
  {
    if(empty(self::$dimensionDirectories) && !empty(self::$dimension))
    {
      $dimensions = array();
      $key = array_keys(self::$dimension);

      $c = count(self::$dimension);
      for($i = 0; $i < $c; $i++)
      {
        $tmp = self::$dimension;
        for($j = $i; $j > 0; $j--)
        {
          array_pop($tmp);
        }
        $val = self::toString($tmp);

        $dimensions['combinations'][] = $val;
        $dimensions['roots'][] = self::$dimension[$key[$i]];
      }

      array_pop($dimensions['combinations']);
      $dimensions = array_merge($dimensions['combinations'], array_reverse($dimensions['roots']));

      self::$dimensionDirectories = array_unique(self::flatten($dimensions));
    }

    return self::$dimensionDirectories;
  }

  /**
   * Gets current dimension as a flattened string
   *
   * @return string the current dimension as a string
   */
  public static function getDimensionString()
  {
    return self::toString(self::getDimension());
  }

  /**
   * Helper function to flatten an array
   *
   * @param array an array to be flattened
   * @return string a flat string of the array input
   */
  public static function flatten($array)
  {
    for($x = 0; $x < sizeof($array); $x++)
    {
      $element = $array[$x];
      if(is_array($element))
      {
        $results = self::flatten($element);
        for($y = 0; $y < sizeof($results); $y++)
        {
          $flat_array[] = $results[$y];
        }
      }
      else
      {
        $flat_array[] = $element;
      }
    }
    return $flat_array;
  }

  /**
   * Converts array values to a string
   *
   * @param array Input array to be converted as string
   * @param boolean Separate by underscore
   * @return string
   */
  public static function toString($array, $underscore = true)
  {
    $i = 0;
    $return = false;
    foreach ($array as $index => $val)
    {
      $divider = (isset($underscore) && $i > 0) ? '_' : '';
      $return .= $divider.$val;
      $i++;
    }
    return $return;
  }
}
