<?php
/*
 * This file is part of the Sift PHP framework.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * Configurable interface
 *
 * @package Sift
 * @subpackage config
 */
interface sfIConfigurable {

  public function getOption($name, $default = null);
  public function setOption($name, $value);
  public function hasOption($name);
  public function getOptions();

}
