<?php
/*
 * This file is part of the Sift PHP framework.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

// load PEAR
require_once dirname(__FILE__) . '/PEAR_bootstrap.php';

/**
 * sfPearConfig.
 *
 * @package    Sift
 * @subpackage plugin_pear
 */
class sfPearConfig extends PEAR_Config {

  public function &getREST($version, $options = array())
  {
    $class = 'sfPearRest' . str_replace('.', '', $version);

    $remote = new $class($this, $options);

    return $remote;
  }

}
