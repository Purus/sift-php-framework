<?php

/*
 * This file is part of the Sift PHP framework.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * DataUri convertable interface
 *
 * @package Sift
 * @subpackage util
 */
interface sfIDataUriConvertable
{
  /**
   * Converts the object to data uri
   *
   * @param boolean $raw Return raw data uri or sfDataUri object?
   * @return string|sfDataUri
   */
  public function toDataUri($raw = true);

}
