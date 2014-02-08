<?php
/*
 * This file is part of the Sift PHP framework.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * sfIWebBrowserDriver interface
 *
 * @package    Sift
 * @subpackage browser
 */
interface sfIWebBrowserDriver {

  public function call($browser, $uri, $method = 'GET', $parameters = array(), $headers = array());

}
