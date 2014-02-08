<?php
/*
 * This file is part of the Sift PHP framework.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * Logger aware interface
 *
 * @package Sift
 * @subpackage log
 */
interface sfILoggerAware {

  /**
   * Sets a logger instance on the object
   *
   * @param sfILogger $logger
   * @return null
   */
  public function setLogger(sfILogger $logger = null);

  /**
   * Returns the logger
   *
   * @return sfILogger
   */
  public function getLogger();

}
