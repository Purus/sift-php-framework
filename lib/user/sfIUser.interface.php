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