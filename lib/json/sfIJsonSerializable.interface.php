<?php
/*
 * This file is part of the Sift PHP framework.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * sfIJsonSerializable interface defines what data should be serialized using sfJson::encode()
 *
 * @package Sift
 * @subpackage json
 */
interface sfIJsonSerializable {

  /**
   * Specify data which should be serialized to JSON
   *
   * @return array|object
   */
  public function jsonSerialize();

}
