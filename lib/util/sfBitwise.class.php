<?php
/*
 * This file is part of the Sift PHP framework.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * sfBitwise class provides bitwise operations. Usefull for state flags.
 *
 * @see http://en.wikipedia.org/wiki/Bitwise_operation
 * @see http://php.net/manual/en/language.operators.bitwise.php#90514
 * @package Sift
 * @subpackage util
 */
class sfBitwise {

  /**
   * Creates flag from given parameters. Use can use variable length of arguments.
   *
   * @param integer flag
   * @return integer
   */
  public static function createFlag(/* variable-length args */)
  {
    $val = 0;
    foreach(func_get_args() as $flag)
    {
      $val |= (int) $flag;
    }
    return $val;
  }

  /**
   * Sets flag to the value
   *
   * @param integer $val
   * @param  $flag
   * @return integer
   */
  public static function setFlag($val, $flag)
  {
    return (int) $val | (int) $flag;
  }

  /**
   * Unsets flag from given value
   *
   * @param integer $val Value
   * @param integer $flag Flag
   * @return integer
   */
  public static function unsetFlag($val, $flag)
  {
    return (int) $val & ~ (int) $flag;
  }

  /**
   * Checks if given flag is set in $val
   *
   * @param integer $val
   * @param integer $flag
   * @return boolean
   */
  public static function isFlagSet($val, $flag)
  {
    return (((int) $val & (int) $flag) === (int) $flag);
  }

  /**
   * Toggles given flag (uses ^ operator)
   *
   * @param integer $val
   * @param integer $flag
   * @return integer
   */
  public static function toggleFlag($val, $flag)
  {
    return (int) $val ^ (int) $flag;
  }

}
