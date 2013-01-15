<?php
/*
 * This file is part of the Sift PHP framework.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * sfNoStorage allows you to disable session support.
 *
 * To disable sessions, change the storage factory in config/factories.yml:
 *
 *   storage:
 *    class: sfNoStorage
 *
 * @package    Sift
 * @subpackage storage
 * @author     Fabien Potencier <fabien.potencier@symfony-project.com>
 * */
class sfNoStorage extends sfStorage
{
  /**
   * Reads data from this storage.
   *
   * The preferred format for a key is directory style so naming conflicts can be avoided.
   *
   * @param  string $key  A unique key identifying your data
   *
   * @return mixed Data associated with the key
   *
   * @throws sfStorageException If an error occurs while reading data from this storage
   */
  public function & read($key)
  {
    $null = null;
    return $null;
  }

  /**
   * Removes data from this storage.
   *
   * The preferred format for a key is directory style so naming conflicts can be avoided.
   *
   * @param  string $key  A unique key identifying your data
   *
   * @return mixed Data associated with the key
   *
   * @throws sfStorageException If an error occurs while removing data from this storage
   */
  public function & remove($key)
  {
    $null = null;
    return $null;
  }

  /**
   * Writes data to this storage.
   *
   * The preferred format for a key is directory style so naming conflicts can be avoided.
   *
   * @param  string $key   A unique key identifying your data
   * @param  mixed  $data  Data associated with your key
   *
   * @throws sfStorageException If an error occurs while writing to this storage
   */
  public function write($key, &$data)
  {
  }

  /**
   * Regenerates id that represents this storage.
   *
   * @param  boolean $destroy Destroy session when regenerating?
   *
   * @return boolean True if session regenerated, false if error
   *
   */
  public function regenerate($destroy = false)
  {
    return true;
  }

  /**
   * Executes the shutdown procedure.
   *
   * @throws sfStorageException If an error occurs while shutting down this storage
   */
  public function shutdown()
  {
  }
  
}