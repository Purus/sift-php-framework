<?php
/*
 * This file is part of the Sift PHP framework.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * sfIUser interface
 *
 * @package    Sift
 * @subpackage user
 */
interface sfIUser {

  /**
   * Sets culture.
   *
   * @param string $culture Culture
   */
  public function setCulture($culture);

  /**
   * Gets culture.
   *
   * @return string
   */
  public function getCulture();

  /**
   * Sets timezone
   *
   * @param string $timezone
   */
  public function setTimezone($timezone);

  /**
   * Returns timezone
   *
   * @return string
   */
  public function getTimezone();

  /**
   * Returns user IP address
   *
   * @return string
   */
  public function getIp();

  /**
   * Returns "REAL" IP address (in case of a proxy)
   *
   * @return string
   */
  public function getRealIp();

  /**
   * Returns IP address of the user
   *
   * @return string
   */
  public function getIpForwardedFor();

  /**
   * Returns hostname of the user IP
   *
   * @return string
   */
  public function getHostname();

  /**
   * Returns user agent of the site visitor
   *
   * @return string
   */
  public function getUserAgent();

  /**
   * Returns browser name of the visitor user agent
   *
   * @return string
   */
  public function getBrowserName();

  /**
   * Returns browser version
   * @return string
   */
  public function getBrowserVersion();

  /**
   * Returns browser (aka user agent)
   *
   * @return string
   */
  public function getBrowser();

  /**
   * Detects if the user is bot (Google, Yahoo, Seznam)...
   *
   * @return boolean
   */
  public function isBot();

  /**
   * Returns true is user agent is mobile device
   *
   * @return boolean
   */
  public function isMobile();

  /**
   * Returns the referer
   *
   * @param string $default The default value to return if referer is not set
   * @return string|null
   */
  public function getReferer($default = null);

  /**
   * Sets referer (The URL which the user came from)
   *
   * @param string The URL the user came from
   */
  public function setReferer($referer);

  /**
   * Sets a flash variable that will be passed to the very next action.
   *
   * @param string $name The name of the flash variable
   * @param string $value The value of the flash variable
   * @param bool $persist true if the flash have to persist for the following request (true by default)
   */
  public function setFlash($name, $value, $persist = true);

  /**
   * Gets a flash variable.
   *
   * @param string $name The name of the flash variable
   * @param string $default The default value returned when named variable does not exist.
   * @param boolean $ignoreApplication Return the flash even for different application?
   * @return mixed The value of the flash variable
   */
  public function getFlash($name, $default = null, $ignoreApplication = false);

  /**
   * Returns true if a flash variable of the specified name exists.
   *
   * @param string $name The name of the flash variable
   * @param boolean $ignoreApplication Return the flash even for different application?
   * @return bool true if the variable exists, false otherwise
   */
  public function hasFlash($name, $ignoreApplication = false);

}
