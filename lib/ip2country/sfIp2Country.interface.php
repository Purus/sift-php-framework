<?php
/*
 * This file is part of the Sift PHP framework.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * sfIIp2Country interface
 *
 * @package    Sift
 * @subpackage ip2country
 */
interface sfIIp2Country
{
  /**
   * Return the a two-character ISO 3166-1 country code for the country associated with the IP address.
   *
   * @param string $ip The ip to look for
   * @param string $default Default code to return if not found
   * @return string
   */
  public function getCountryCode($ip, $default = null);

}
