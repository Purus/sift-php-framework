<?php
/*
 * This file is part of the Sift PHP framework.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * sfIArrayAccessByReference - extension to fix ArrayAccess interface reference issues with PHP < 5.3.4
 *
 * @package Sift
 * @subpackage util
 * @link http://php.net/manual/en/arrayaccess.offsetget.php
 */
interface sfIArrayAccessByReference
{
  /**
   * Set a value by reference
   *
   * @param mixed $offset The offset to assign the value to.
   * @param mixed $value The value to set.
   * @return mixed $value The value
   */
  public function &offsetSetByReference($offset, &$value);

  /**
   * Get a value by reference
   *
   * @param mixed $offset The offset
   */
  public function &offsetGetByReference($offset);

}
