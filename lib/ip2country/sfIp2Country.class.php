<?php
/*
 * This file is part of the Sift PHP framework.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * sfIp2Country
 *
 * @package    Sift
 * @subpackage ip2country
 */
abstract class sfIp2Country extends sfConfigurable implements sfIIp2Country
{
    /**
     * Creates an instance of driver with given options
     *
     * @param string $driver
     * @param array  $options
     *
     * @return sfIIp2Country Driver instance
     * @throws LogicException
     * @throws InvalidArgumentException
     */
    public static function factory($driver, $options = array())
    {
        $driverObj = false;

        if (class_exists($class = sprintf('sfIp2CountryDriver%s', ucfirst($driver)))) {
            $driverObj = new $class($options);
        } else {
            if (class_exists($class = $driver)) {
                $driverObj = new $class($options);
            }
        }

        if ($driverObj) {
            if (!$driverObj instanceof sfIIp2Country) {
                throw new LogicException(sprintf('Driver "%s" does not implement sfIIp2Country interface.', $driver));
            }

            return $driverObj;
        }

        throw new InvalidArgumentException(sprintf('Invalid ip2country driver "%s".', $driver));
    }

    /**
     * Converts the IP to integer
     *
     * @param string $ip
     *
     * @return integer
     */
    protected function ip2int($ip)
    {
        return (integer)sprintf('%u', ip2long($ip));
    }

}
