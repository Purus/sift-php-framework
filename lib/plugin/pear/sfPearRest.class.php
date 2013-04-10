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
 * sfPearRest interacts with a PEAR channel.
 *
 * @package    Sift
 * @subpackage plugin_pear
 */
class sfPearRest extends PEAR_REST {

  /**
   * @see PEAR_REST::downloadHttp()
   */
  public function downloadHttp($url, $lastmodified = null, $accept = false, $channel = false)
  {
    return parent::downloadHttp($url, $lastmodified, array_merge(
            false !== $accept ? $accept : array(),
            array(/*"\r\nX-SIFT-VERSION: " . SIFT_VERSION*/)), $channel);
  }

}
