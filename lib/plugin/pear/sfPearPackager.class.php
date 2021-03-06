<?php
/*
 * This file is part of the Sift PHP framework.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

// load PEAR
require_once dirname(__FILE__) . '/PEAR_bootstrap.php';

/**
 * sfPluginPackager allows to make PEAR packages
 *
 * @package    Sift
 * @subpackage plugin
 */
class sfPearPackager extends PEAR_Packager {

  protected $logger;

  public function setLogger(sfILogger $logger = null)
  {
    $this->logger = $logger;
  }

  /**
   * Logging method.
   *
   * @param int    $level  log level (0 is quiet, higher is noisier)
   * @param string $msg    message to write to the log
   *
   * @return void
   *
   * @access public
   * @static
   */
  public function log($level, $msg, $append_crlf = true)
  {
    if($this->logger)
    {
      $this->logger->log('pear-packager: ' . $msg, $level);
    }
    else
    {
      echo $msg . "\n";
    }
  }

}
