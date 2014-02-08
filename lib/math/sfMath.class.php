<?php
/*
 * This file is part of the Sift package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * sfMath provides mathematical functions which returns correct results. Solves
 * problem "Why don't my numbers add up?". Its a wrapper class around BC math extension.
 *
 * @package Sift
 * @subpackage math
 * @link http://floating-point-gui.de/languages/php/
 * @link http://php.net/manual/en/book.bc.php
 * @link http://stackoverflow.com/questions/1642614/how-to-ceil-floor-and-round-bcmath-numbers
 * @link http://en.wikipedia.org/wiki/Rounding
 * @link http://grokbase.com/t/php/php-notes/121aps3na5/note-107134-added-to-ref-bc
 */
class sfMath
{
  /**
   * Add two arbitrary precision numbers
   *
   * @param string $left_operand
   * @param string $right_operand
   * @param integer $precision The scale factor
   * @return string The sum of the two operands
   */
  public static function add($left_operand, $right_operand, $precision = null)
  {
    return is_null($precision) ? bcadd($left_operand, $right_operand) : bcadd($left_operand, $right_operand, $precision);
  }

  /**
   * Compares the left_operand to the right_operand and returns the result as an integer.
   *
   * @param string $left_operand The left operand, as a string.
   * @param string $right_operand The right operand, as a string.
   * @param integer $precision The optional scale parameter is used to set the number of digits after the decimal place which will be used in the comparison.
   * @return integer Returns 0 if the two operands are equal, 1 if the left_operand is larger than the right_operand, -1 otherwise.
   */
  public static function compare($left_operand, $right_operand, $precision = null)
  {
    return is_null($precision) ? bccomp($left_operand, $right_operand) : bccomp($left_operand, $right_operand, $precision);
  }

  /**
   * Divide two arbitrary precision numbers
   *
   * @param string $left_operand The left operand, as a string.
   * @param string $right_operand The right operand, as a string.
   * @param integer $precision This optional parameter is used to set the number of digits after the decimal place in the result.
   * @return string|null Returns the result of the division as a string, or NULL if right_operand is 0.
   */
  public static function divide($left_operand, $right_operand, $precision = null)
  {
    return is_null($precision) ? bcdiv($left_operand, $right_operand) : bcdiv($left_operand, $right_operand, $precision);
  }

  /**
   * Get modulus of an arbitrary precision number
   *
   * @param string $left_operand The left operand, as a string.
   * @param string $modulus The modulus, as a string.
   * @return string|null Returns the modulus as a string, or NULL if modulus is 0.
   */
  public static function modulus($left_operand, $modulus)
  {
    return bcmod($left_operand, $modulus);
  }

  /**
   * Multiply two arbitrary precision number
   *
   * @param string $left_operand The left operand, as a string.
   * @param string $right_operand The right operand, as a string.
   * @param integer $precision This optional parameter is used to set the number of digits after the decimal place in the result.
   * @return string Returns the result as a string.
   */
  public static function multiply($left_operand, $right_operand, $precision = null)
  {
    return is_null($precision) ? bcmul($left_operand, $right_operand) : bcmul($left_operand, $right_operand, $precision);
  }

  /**
   * Raise an arbitrary precision number to another
   *
   * @param string $left_operand The left operand, as a string.
   * @param string $right_operand The right operand, as a string.
   * @param integer $precision This optional parameter is used to set the number of digits after the decimal place in the result.
   * @return string Returns the result as a string.
   */
  public static function power($left_operand, $right_operand, $precision = null)
  {
    return is_null($precision) ? bcpow($left_operand, $right_operand) : bcpow($left_operand, $right_operand, $precision);
  }

  /**
   * Raise an arbitrary precision number to another, reduced by a specified modulus
   *
   * @param string $left_operand The left operand, as a string.
   * @param string $right_operand The right operand, as a string.
   * @param string $modulus The modulus, as a string.
   * @return string Returns the result as a string.
   */
  public static function powerModulus($left_operand, $right_operand, $modulus, $precision = null)
  {
    return is_null($precision) ? bcpowmod($left_operand, $right_operand, $modulus) : bcpowmod($left_operand, $right_operand, $modulus, $precision);
  }

  /**
   * Set default scale parameter for all bc math functions
   *
   * @param integer $precision The scale factor
   * @return boolean Returns TRUE on success or FALSE on failure
   */
  public static function setScale($precision)
  {
    return bcscale($precision);
  }

  /**
   * Returns default scale factor value
   *
   * @return integer|false Integer from ini_get setting or false when ini_get is disabled
   */
  public static function getDefaultScale()
  {
    return sfToolkit::isCallable('ini_get') ? ini_get('bcmath.scale') : false;
  }

  /**
   * Subtract one arbitrary precision number from another
   *
   * @param string $left_operand The left operand, as a string.
   * @param string $right_operand The right operand, as a string.
   * @param integer $precision This optional parameter is used to set the number of digits after the decimal place in the result.
   * @return string Returns the result as a string.
   */
  public static function substract($left_operand, $right_operand, $precision = null)
  {
    return is_null($precision) ? bcsub($left_operand, $right_operand) : bcsub($left_operand, $right_operand, $precision);
  }

  /**
   * Get the square root of an arbitrary precision number
   *
   * @param string $operand The operand, as a string.
   * @param integer $precision This optional parameter is used to set the number of digits after the decimal place in the result.
   * @return string|null Returns the square root as a string, or NULL if operand is negative.
   */
  public static function sqrt($operand, $precision = null)
  {
    return is_null($precision) ? bcsqrt($operand) : bcsqrt($operand, $precision);
  }

  /**
   * Calculates factorial for given number
   *
   * @param string $n Number to calculate factorial
   * @return string
   */
  public static function factorial($n)
  {
    if ($n == 0 || $n == 1) {
      return '1';
    }

    $r = $n--;
    while ($n > 1) {
      $r = self::multiply($r, $n--);
    }

    return $r;
  }

  /**
   * Remove trailing and leading zeros - just to return cleaner number
   *
   * @param string $number
   * @return string
   */
  public static function clean($number)
  {
    $number = (string) $number;

    // don't clean numbers without dot
    if (strpos($number, '.') === false) {
      return $number;
    }

    // remove zeros from end of number ie. 140.00000 becomes 140.
    $clean = rtrim($number, '0');
    // remove zeros from front of number ie. 0.33 becomes .33
    $clean = ltrim($clean, '0');

    // everything has been cleaned
    if ($clean == '.') {
      $clean = '.0';
    }

    // remove decimal point if an integer ie. 140. becomes 140
    $clean = rtrim($clean, '.');

    return $clean[0] == '.' ? '0'. $clean : $clean;
  }

  /**
   * Is the number even?
   *
   * @param string $value
   * @return boolean
   */
  public static function isEven($value)
  {
    $value = self::clean($value);

    return self::modulus(self::abs($value), 2) == 0;
  }

  /**
   * Is the number odd
   *
   * @param string $value
   * @return boolean
   */
  public static function isOdd($value)
  {
    $value = self::clean($value);

    return self::modulus(self::abs($value), 2) == 1;
  }

  /**
   * Round fractions up
   *
   * @param string $value The value to round
   * @param integer $precision Precision
   * @return string Returns the next highest value by rounding up value if necessary.
   */
  public static function ceil($value, $precision = 0)
  {
    $value = sfMath::clean($value);

    if (strpos($value, '.') === false) {
      return $value;
    }

    $multiplier = self::power(10, $precision);
    $value = self::multiply($value, $multiplier);

    if (!self::isNegative($value)) {
      $value = self::add($value, '1', 0);
    } else {
      $value = self::substract($value, '0', 0);
    }

    return sfMath::clean(sfMath::divide($value, $multiplier, $precision));
  }

  /**
   * Round fractions down
   *
   * @param string $number
   * @param integer $precision Precision
   * @return string
   */
  public static function floor($number, $precision = 0)
  {
    $number = self::clean($number);

    if (strpos($number, '.') === false) {
      return $number;
    }

    $multiplier = self::power(10, $precision);
    $number = self::multiply($number, $multiplier);

    if (!self::isNegative($number)) {
      $number = self::add($number, '0', 0);
    } else {
      $number = self::substract($number, '1', 0);
    }

    return self::clean(self::divide($number, $multiplier, $precision));
  }

  /**
   * Sets the current number to the absolute value of itself
   *
   * @return string
   */
  public static function abs($value)
  {
    $value = self::clean($value);

    // Use substr() to find the negative sign at the beginning of the
    // number, rather than using signum() to determine the sign.
    if (substr($value, 0, 1) === '-') {
      return substr($value, 1);
    }

    return $value;
  }

  /**
   * Returns the sign on the number
   *
   * @param string $value The value
   * @return integer Returns -1 or 1
   */
  public static function sign($value)
  {
    $cmp = self::compare($value, '0');

    // this is zero
    if ($cmp == 0) {
      return '1';
    }

    return (string) $cmp;
  }

  /**
   * Is the number negative?
   *
   * @param string $number
   * @return boolean
   */
  public static function isNegative($number)
  {
    return self::compare($number, 0) == -1;
  }

  /**
   * Rounds a number. This is just an alias method to sfRounding::round()
   *
   * @param string $number Number for rounding
   * @param integer $precision Precision
   * @param string $mode Rounding mode
   * @return string
   * @see sfRounding::round()
   */
  public static function round($number, $precision = 0, $mode = sfRounding::HALF_UP)
  {
    return sfRounding::round($number, $precision, $mode);
  }

  /**
   * Rounding mode to round away from zero.
   *
   * @param string $value
   * @param integer $precision
   */
  public static function roundUp($value, $precision = 0)
  {
    $value = sfMath::clean($value);

    if (strpos($value, '.') === false) {
      return $value;
    }

    if (!self::isNegative($value)) {
      return self::ceil($value, $precision);
    } else {
      return self::floor($value, $precision);
    }
  }

  /**
   * Rounding mode to round towards zero.
   *
   * @param string $value
   * @param integer $precision
   */
  public static function roundDown($value, $precision = 0)
  {
    $value = sfMath::clean($value);

    // larger than zero
    if (!self::isNegative($value)) {
      return self::floor($value, $precision);
    } else {
      return self::ceil($value, $precision);
    }
  }

  /**
   * Rounding mode to round towards "nearest neighbor" unless both neighbors
   * are equidistant, in which case round down. Behaves as for ROUND_UP
   * if the discarded fraction is > 0.5; otherwise, behaves as for ROUND_DOWN.
   *
   * @param string $value
   * @param integer $precision
   */
  public static function roundHalfDown($value, $precision = 0)
  {
    $value = self::clean($value);

    if (strpos($value, '.') === false) {
      return $value;
    }

    list(, $decimal) = explode('.', $value);

    $fraction = 0;

    if (isset($decimal[$precision])) {
      $fraction = $decimal[$precision];
    }

    if ($fraction > 5) {
      return self::roundUp($value, $precision);
    }

    return self::roundDown($value, $precision);
  }

  /**
   * Rounding mode to round towards "nearest neighbor" unless both neighbors are equidistant,
   * in which case round up. Behaves as for ROUND_UP if the discarded
   * fraction is >= 0.5; otherwise, behaves as for ROUND_DOWN.
   * Note that this is the rounding mode commonly taught at school.
   *
   * @param string $value
   * @param integer $precision
   */
  public static function roundHalfUp($value, $precision = 0)
  {
    $value = self::clean($value);

    if (strpos($value, '.') === false) {
      return $value;
    }

    if (!self::isNegative($value)) {
      return self::add($value, '0.' . str_repeat('0', $precision) . '5', $precision);
    }

    return self::substract($value, '0.' . str_repeat('0', $precision) . '5', $precision);
  }

  /**
   * Round half to even
   *
   * A tie-breaking rule that is less biased is round half to even, namely:
   * If the fraction of y is 0.5, then q is the even integer nearest to y.
   * Thus, for example, +23.5 becomes +24, as does +24.5; while −23.5 becomes −24, as does −24.5.
   *
   * Rounding mode to round towards the "nearest neighbor" unless both neighbors are equidistant,
   * in which case, round towards the even neighbor.
   * Behaves as for HALF_UP if the digit to the left of the discarded fraction is odd;
   * behaves as for HALF_DOWN if it's even.
   *
   * This variant of the round-to-nearest method is also called unbiased rounding,
   * convergent rounding, statistician's rounding, Dutch rounding, Gaussian rounding,
   * odd-even rounding, bankers' rounding or broken rounding, and is widely used in bookkeeping.
   * This is the default rounding mode used in IEEE 754 computing functions and operators.
   *
   * @param string $value
   * @param integer $precision
   * @see http://en.wikipedia.org/wiki/Rounding#Round_half_to_even
   * @see http://forums.adobe.com/message/2899961
   */
  public static function roundHalfEven($value, $precision = 0)
  {
    $value = self::clean($value);

    if (strpos($value, '.') === false) {
      return $value;
    }

    list($integer, $decimal) = explode('.', $value);
    $index = $precision > 0 ? $precision - 1 : 0;

    if (!isset($decimal[$index])) {
      return $value;
    }

    $digit = $decimal[$index];

    if (self::isOdd($precision > 0 ? $digit : $integer)) {
      return self::roundHalfUp($value, $precision);
    } else {
      return self::roundHalfDown($value, $precision);
    }
  }

  /**
   * Round half to odd
   *
   * This variant is almost never used in computations,
   * except in situations where one wants to avoid rounding 0.5 or −0.5 to zero;
   * or to avoid increasing the scale of floating point numbers,
   * which have a limited exponent range. With round half to even,
   * a non infinite number would round to infinity, and a small denormal value
   * would round to a normal non-zero value. Effectively, this mode prefers preserving
   * the existing scale of tie numbers, avoiding out of range results when possible.
   *
   * @param string $value
   * @param integer $precision
   */
  public static function roundHalfOdd($value, $precision = 0)
  {
    $value = self::clean($value);

    if (strpos($value, '.') === false) {
      return $value;
    }

    list($integer, $decimal) = explode('.', $value);

    $index = $precision > 0 ? $precision - 1 : 0;

    if (!isset($decimal[$index])) {
      return $value;
    }

    $digit = $decimal[$index];

    if (self::isEven($precision > 0 ? $digit : $integer)) {
      return self::roundHalfUp($value, $precision);
    } else {
      return self::roundHalfDown($value, $precision);
    }
  }

  /**
   * Rounds to the nearest value. Usefull for rounding to the nearest nickel, dime or quarter.
   *
   * Rounding to nearest nickel (5 cents):
   * <pre>
   * sfMath::roundToNearest('10.125', '5', 2);
   * </pre>
   *
   * Rounding the nearest dime (10 cents):
   * <pre>
   * sfMath::roundToNearest('10.125', '10', 2);
   * </pre>
   *
   * Rounding the nearest quarter (25 cents):
   * <pre>
   * sfMath::roundToNearest('10.125', '25', 2);
   * </pre>
   *
   * @param string $value
   * @param integer $precision
   * @param string $nearest
   * @throws InvalidArgumentException
   */
  public static function roundToNearest($value, $nearest, $precision = 0, $mode = sfRounding::HALF_UP)
  {
    $value = self::clean($value);

    if ($nearest == '' || $nearest == 0) {
      throw new InvalidArgumentException('Nearest value is missing or is zero.');
    }

    $nearest = self::divide('100', $nearest, $precision);
    $rounded = self::round(self::multiply($value, $nearest, $precision), 0, $mode);

    return self::clean(self::divide($rounded, $nearest, $precision));
  }

}
