<?php
/*
 * This file is part of the Sift PHP framework.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * sfJavascriptTemplateCompiler class compiles javascript templates.
 *
 * @package    Sift
 * @subpackage view
 */
abstract class sfJavascriptTemplateCompiler extends sfConfigurable implements sfIJavascriptTemplateCompiler
{
    /**
     * Returns an instance of the template compiler driver
     *
     * @param string $driver Driver name
     * @param array  $options Array of options for the driver
     *
     * @return sfIJavascriptTemplateCompiler
     */
    public static function factory($driver, $options = array())
    {
        $driverClass = sprintf(sprintf('sfJavascriptTemplateCompilerDriver%s', ucfirst($driver)));

        if (class_exists($driverClass)) {
            $driverObj = new $driverClass($options);
        } elseif (class_exists($driver)) {
            $driverObj = new $driver($options);
        } else {
            throw new InvalidArgumentException(sprintf('Driver "%s" does not exist.', $driver));
        }

        if (!$driverObj instanceof sfIJavascriptTemplateCompiler) {
            throw new LogicException(sprintf(
                'Driver "%s" does not implement sfIJavascriptTemplateCompiler interface.',
                $driver
            ));
        }

        return $driverObj;
    }

    /**
     * Write cache data to a file
     *
     * @param string $cacheFile Path to a file
     * @param string $data
     */
    public static function writeCache($cacheFile, $data)
    {
        return file_put_contents($cacheFile, self::getCacheFileHeader() . "\n" . $data, LOCK_EX);
    }

    /**
     * Returns header text for CSS files
     *
     * @return  string  a header text for CSS files
     */
    public static function getCacheFileHeader()
    {
        return '/* This file is automatically compiled. Don\'t edit it manually. */';
    }

}
