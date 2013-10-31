<?php
/*
 * This file is part of the Sift package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

if(!defined('PHP_ROUND_HALF_DOWN'))
{
  define('PHP_ROUND_HALF_DOWN', 2);
}

/**
 * sfRounding provides a proxy to sfMath rounding functions
 *
 * @package Sift
 * @subpackage math
 */
class sfRounding {

  /**
   * No rounding
   */
  const NONE = 'NONE';

  /**
   * Round fractions up
   */
  const CEILING = 'CEILING';

  /**
   * Round fractions down
   */
  const FLOOR = 'FLOOR';

  /**
   * Round up
   */
  const UP = 'UP';

  /**
   * Round down
   */
  const DOWN = 'DOWN';

  /**
   * Round half down
   */
  const HALF_DOWN = 'HALF_DOWN';

  /**
   * Round half up
   */
  const HALF_UP = 'HALF_UP';

  /**
   * Round half to even, bankers' rounding
   */
  const HALF_EVEN = 'HALF_EVEN';

  /**
   * Round half to odd
   */
  const HALF_ODD = 'HALF_ODD';

  /**
   * Round to nearest penny, nickel...
   */
  const NEAREST = 'NEAREST';

  /**
   * Rounds a $value using given $mode in $precision
   *
   * @param string $value
   * @param integer $precision
   * @param string $mode
   * @param string $nearest Nearest (used only if mode is NEAREST)
   * @param string $nearestMode Mode for nearest rounding (used only if mode is NEAREST)
   * @return string
   * @throws InvalidArgumentException If rounding mode is invalid
   */
  public static function round($value, $precision = 0, $mode = self::HALF_UP, $nearest = null, $nearestMode = self::HALF_UP)
  {
    switch($mode)
    {
      case self::NONE:
        return $value;

      case self::FLOOR:
        return sfMath::floor($value, $precision);

      case self::CEILING:
        return sfMath::ceil($value, $precision);

      case self::DOWN:
        return sfMath::roundDown($value, $precision);

      case self::UP:
        return sfMath::roundUp($value, $precision);

      case self::HALF_DOWN:
      case PHP_ROUND_HALF_DOWN:
        return sfMath::roundHalfDown($value, $precision);

      case self::HALF_UP:
      case PHP_ROUND_HALF_UP:
        return sfMath::roundHalfUp($value, $precision);

      case self::HALF_EVEN:
      case PHP_ROUND_HALF_EVEN:
        return sfMath::roundHalfEven($value, $precision);

      case self::HALF_ODD:
      case PHP_ROUND_HALF_ODD:
        return sfMath::roundHalfOdd($value, $precision);

      case self::NEAREST:
        return self::roundToNearest($value, $nearest, $precision, $nearestMode);
    }

    $r = new sfReflectionClass('sfRounding');

    throw new InvalidArgumentException(sprintf('Invalid rounding mode "%s" given. Valid modes are: %s', $mode, join(', ',
      array_keys((array)$r->getConstants())
    )));
  }

  /**
   * Rounds to nearest penny, nickel...
   *
   * @see sfMath::roundToNearest
   */
  public static function roundToNearest($value, $nearest, $precision = 0, $mode = self::HALF_UP)
  {
    return sfMath::roundToNearest($value, $nearest, $precision, $mode);
  }

}
