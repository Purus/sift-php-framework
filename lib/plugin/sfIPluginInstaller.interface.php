<?php
/*
 * This file is part of the Sift PHP framework.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * sfIPluginManager is an interface for plugin installers.
 *
 * @package    Sift
 * @subpackage plugin
 */
interface sfIPluginInstaller {
  
  public function __construct(sfCliBaseTask $project, $options = array());
  public function install();
  public function uninstall();
  
}
