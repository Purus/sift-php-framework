<?php
/*
 * This file is part of the Sift PHP framework.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * Generator interface
 *
 * @package Sift
 * @subpackage generator
 */
interface sfIGenerator
{
  /**
   * Generates classes and templates.
   *
   * @return string The cache for the configuration file
   */
  public function generate();

  /**
   * Return module name
   */
  public function getModuleName();

  /**
   * Sets module name
   *
   * @param string $moduleName
   */
  public function setModuleName($moduleName);

}
