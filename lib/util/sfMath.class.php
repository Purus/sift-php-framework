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
 * @package    Sift
 * @subpackage util
 * @link http://floating-point-gui.de/languages/php/
 * @link http://php.net/manual/en/book.bc.php
 * @link http://stackoverflow.com/questions/1642614/how-to-ceil-floor-and-round-bcmath-numbers
 */
class sfMath {

  /**
   * Add two arbitrary precision numbers
   *
   * @param string $left_operand
   * @param string $right_operand
   * @param integer $scale The scale factor
   * @return string The sum of the two operands
   */
  public static function add($left_operand, $right_operand, $scale = null)
  {
    return is_null($scale) ? bcadd($left_operand, $right_operand) : bcadd($left_operand, $right_operand, $scale);
  }

  /**
   * Compares the left_operand to the right_operand and returns the result as an integer.
   *
   * @param string $left_operand The left operand, as a string.
   * @param string $right_operand The right operand, as a string.
   * @param integer $scale The optional scale parameter is used to set the number of digits after the decimal place which will be used in the comparison.
   * @return integer Returns 0 if the two operands are equal, 1 if the left_operand is larger than the right_operand, -1 otherwise.
   */
  public static function compare($left_operand, $right_operand, $scale = null)
  {
    return is_null($scale) ? bccomp($left_operand, $right_operand) : bccomp($left_operand, $right_operand, $scale);
  }

  /**
   * Divide two arbitrary precision numbers
   *
   * @param string $left_operand The left operand, as a string.
   * @param string $right_operand The right operand, as a string.
   * @param integer $scale This optional parameter is used to set the number of digits after the decimal place in the result.
   * @return string|null Returns the result of the division as a string, or NULL if right_operand is 0.
   */
  public static function divide($left_operand, $right_operand, $scale = null)
  {
    return is_null($scale) ? bcdiv($left_operand, $right_operand) : bcdiv($left_operand, $right_operand, $scale);
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
   * @param integer $scale This optional parameter is used to set the number of digits after the decimal place in the result.
   * @return string Returns the result as a string.
   */
  public static function multiply($left_operand, $right_operand, $scale = null)
  {
    return is_null($scale) ? bcmul($left_operand, $right_operand) : bcmul($left_operand, $right_operand, $scale);
  }

  /**
   * Raise an arbitrary precision number to another
   *
   * @param string $left_operand The left operand, as a string.
   * @param string $right_operand The right operand, as a string.
   * @param integer $scale This optional parameter is used to set the number of digits after the decimal place in the result.
   * @return string Returns the result as a string.
   */
  public static function power($left_operand, $right_operand, $scale = null)
  {
    return is_null($scale) ? bcpow($left_operand, $right_operand) : bcpow($left_operand, $right_operand, $scale);
  }

  /**
   * Raise an arbitrary precision number to another, reduced by a specified modulus
   *
   * @param string $left_operand The left operand, as a string.
   * @param string $right_operand The right operand, as a string.
   * @param string $modulus The modulus, as a string.
   * @return string Returns the result as a string.
   */
  public static function powerModulus($left_operand, $right_operand, $modulus, $scale = null)
  {
    return is_null($scale) ? bcpowmod($left_operand, $right_operand, $modulus) : bcpowmod($left_operand, $right_operand, $modulus, $scale);
  }

  /**
   * Set default scale parameter for all bc math functions
   *
   * @param integer $scale The scale factor
   * @return boolean Returns TRUE on success or FALSE on failure
   */
  public static function setScale($scale)
  {
    return bcscale($scale);
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
   * @param integer $scale This optional parameter is used to set the number of digits after the decimal place in the result.
   * @return string Returns the result as a string.
   */
  public static function substract($left_operand, $right_operand, $scale = null)
  {
    return is_null($scale) ? bcsub($left_operand, $right_operand) : bcsub($left_operand, $right_operand, $scale);
  }

  /**
   * Get the square root of an arbitrary precision number
   *
   * @param string $operand The operand, as a string.
   * @param integer $scale This optional parameter is used to set the number of digits after the decimal place in the result.
   * @return string|null Returns the square root as a string, or NULL if operand is negative.
   */
  public static function sqrt($operand, $scale = null)
  {
    return is_null($scale) ? bcsqrt($operand) : bcsqrt($operand, $scale);
  }

  /**
   * Calculates factorial for given number
   *
   * @param string $n Number to calculate factorial
   * @return string
   */
  public static function factorial($n)
  {
    if($n == 0 || $n == 1)
    {
      return '1';
    }

    $r = $n--;
    while($n > 1)
    {
      $r = bcmul($r, $n--);
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
    // don't clean numbers without dot
    if(strpos($number, '.') === false)
    {
      return $number;
    }

    //remove zeros from end of number ie. 140.00000 becomes 140.
    $clean = rtrim($number, '0');
     //remove zeros from front of number ie. 0.33 becomes .33
    $clean = ltrim($clean, '0');
    //remove decimal point if an integer ie. 140. becomes 140
    $clean = rtrim($clean, '.');

    return $clean;
  }

  /**
   * Ceils the number
   *
   * @param string $number
   * @return string
   */
  public static function ceil($number)
  {
    return self::round($number, 0);
  }

  /**
   * Round fractions down
   *
   * @param string $number
   * @return string
   */
  public static function floor($number)
  {
    if(strpos($number, '.') !== false)
    {
      if(preg_match("/\.[0]+$/i", $number))
      {
        return bcround($number, 0);
      }

      if($number[0] != '-')
      {
        return bcadd($number, 0, 0);
      }

      return bcsub($number, 1, 0);
    }
    return $number;
  }

  /**
   * Rounds a number
   *
   * @param string $number Number for rounding
   * @param integer $precision Precision
   * @return string
   */
  public static function round($number, $precision = 0)
  {
    if(strpos($number, '.') !== false)
    {
      if($number[0] != '-')
      {
        return bcadd($number, '0.' . str_repeat('0', $precision) . '5', $precision);
      }

      return bcsub($number, '0.' . str_repeat('0', $precision) . '5', $precision);
    }
    return $number;
  }

}