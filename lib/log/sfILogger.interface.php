<?php
/*
 * This file is part of the Sift PHP framework.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * Logger interface
 * 
 * @package Sift
 * @subpackage log
 */
interface sfILogger {

  public function log($message, $priority = SF_LOG_INFO);
  
}
