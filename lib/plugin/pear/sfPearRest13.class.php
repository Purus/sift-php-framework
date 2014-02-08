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
 * sfPearRest10 interacts with a PEAR channel that supports REST 1.1.
 *
 * @package    Sift
 * @subpackage plugin_pear
 */
class sfPearRest13 extends PEAR_REST_13
{
  /**
   * @see PEAR_REST_11
   */
  public function __construct($config, $options = array())
  {
    $class = isset($options['base_class']) ? $options['base_class'] : 'sfPearRest';

    $this->_rest = new $class($config, $options);
  }

}
