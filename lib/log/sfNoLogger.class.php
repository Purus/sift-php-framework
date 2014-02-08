<?php
/*
 * This file is part of the Sift PHP framework.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * Logs to nowhere.
 *
 * @package    Sift
 * @subpackage log
 */
class sfNoLogger implements sfILogger
{
  /**
   * @see sfILogger
   */
  public function emergency($message, array $context = array())
  {
  }

  /**
   * @see sfILogger
   */
  public function alert($message, array $context = array())
  {
  }

  /**
   * @see sfILogger
   */
  public function critical($message, array $context = array())
  {
  }

  /**
   * @see sfILogger
   */
  public function error($message, array $context = array())
  {
  }

  /**
   * @see sfILogger
   */
  public function warning($message, array $context = array())
  {
  }

  /**
   * @see sfILogger
   */
  public function notice($message, array $context = array())
  {
  }

  /**
   * @see sfILogger
   */
  public function info($message, array $context = array())
  {
  }

  /**
   * @see sfILogger
   */
  public function debug($message, array $context = array())
  {
  }

  /**
   * @see sfILogger
   */
  public function log($message, $level = sfILogger::INFO, array $context = array())
  {
  }

  public function shutdown()
  {
  }

}
