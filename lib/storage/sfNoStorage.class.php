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
 */
class sfNoStorage extends sfStorage {

  /**
   * @see sfIStorage
   */
  public function read($key)
  {
  }

  /**
   * @see sfIStorage
   */
  public function remove($key)
  {
  }

  /**
   * @see sfIStorage
   */
  public function write($key, $data)
  {
  }

  /**
   * @see sfIStorage
   */
  public function regenerate($destroy = false)
  {
    return true;
  }

  /**
   * @see sfIService
   */
  public function shutdown()
  {
  }

}
