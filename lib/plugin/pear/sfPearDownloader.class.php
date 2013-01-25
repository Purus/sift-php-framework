<?php
/*
 * This file is part of the Sift PHP framework.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

require_once 'PEAR/Downloader.php';

/**
 * sfPearDownloader downloads files from the Internet.
 *
 * @package    Sift
 * @subpackage plugin_pear
 */
class sfPearDownloader extends PEAR_Downloader {

  /**
   * @see PEAR_REST::downloadHttp()
   */
  public function downloadHttp($url, &$ui, $save_dir = '.', $callback = null, $lastmodified = null, $accept = false, $channel = false)
  {
    return parent::downloadHttp($url, $ui, $save_dir, $callback, $lastmodified, $accept, $channel);
  }

}
