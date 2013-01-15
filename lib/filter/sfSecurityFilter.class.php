<?php
/*
 * This file is part of the Sift PHP framework.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * sfSecurityFilter provides a base class that classifies a filter as one that handles security.
 *
 * @package    Sift
 * @subpackage filter
 */
abstract class sfSecurityFilter extends sfFilter
{
  /**
   * Returns a new instance of a sfSecurityFilter.
   *
   * @param string The security class name
   *
   * @return sfSecurityFilter A sfSecurityFilter implementation instance
   */
  public static function newInstance($class)
  {
    // the class exists
    $object = new $class();

    if(!($object instanceof sfSecurityFilter))
    {
      // the class name is of the wrong type
      throw new sfFactoryException(sprintf('Class "%s" is not of the type sfSecurityFilter', $class));
    }

    return $object;
  }
  
}
