<?php

/*
 * This file is part of the Sift PHP framework.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * Storage interface
 *
 * @package Sift
 * @subpackage storage
 */
interface sfIStorage {

  /**
   * Reads data from this storage.
   *
   * @param string $key A unique key identifying your data
   * @return mixed Data associated with the key
   * @throws sfStorageException If an error occurs while reading data from this storage
   */
  public function read($key);

  /**
   * Removes data from this storage.
   *
   * @param string $key A unique key identifying your data
   * @return mixed Data associated with the key
   * @throws sfStorageException If an error occurs while removing data from this storage
   */
  public function remove($key);

  /**
   * Writes data to this storage.
   *
   * @param string $key A unique key identifying your data
   * @param mixed $data Data associated with your key
   * @throws sfStorageException If an error occurs while writing to this storage
   */
  public function write($key, $data);

  /**
   * Regenerates id that represents this storage.
   *
   * @param boolean $destroy Destroy session when regenerating?
   * @return boolean True if session regenerated, false if error
   */
  public function regenerate($destroy = false);

  /**
   * Returns if the session is started
   *
   * @return boolean True is its started, false otherwise
   */
  public function isStarted();

  /**
   * Starts the session
   *
   */
  public function start();

}
