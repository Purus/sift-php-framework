<?php
/*
 * This file is part of the Sift PHP framework.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * sfAntivirus is a base class for antivirus drivers.
 *
 * @package Sift
 * @subpackage antivirus
 */
abstract class sfAntivirus extends sfConfigurable implements sfIAntivirus
{
  /**
   * Creates an instance of driver with given options
   *
   * @param string $driver
   * @param array $options
   * @return sfIAntivirus Antivirus instance
   * @throws LogicException
   * @throws InvalidArgumentException
   */
  public static function factory($driver, $options = array())
  {
    $driverObj = false;

    if (class_exists($class = sprintf('sfAntivirusDriver%s', ucfirst($driver)))) {
      $driverObj = new $class($options);
    } else if (class_exists($class = $driver)) {
      $driverObj = new $class($options);
    }

    if ($driverObj) {
      if (!$driverObj instanceof sfIAntivirus) {
        throw new LogicException(sprintf('Driver "%s" does not implement sfIAntivirus interface.', $driver));
      }

      return $driverObj;
    }

    throw new InvalidArgumentException(sprintf('Invalid antivirus driver "%s".', $driver));
  }

}
