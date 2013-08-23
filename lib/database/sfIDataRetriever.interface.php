<?php
/*
 * This file is part of the Sift PHP framework.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * Data retriever interface
 *
 * @package    Sift
 * @subpackage database
 */
interface sfIDataRetriever {

  /**
   * Retrieve objects
   *
   * @param string $class Class of the model
   * @param string $peerMethod Peer method
   * @param array $options Array of options
   * @return mixed
   */
  public static function retrieveObjects($class, $peerMethod = null, $options = array());

}
