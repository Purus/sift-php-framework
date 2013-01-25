<?php
/*
 * This file is part of the Sift PHP framework.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

require_once 'PEAR/REST/10.php';

/**
 * sfPearRest10 interacts with a PEAR channel that supports REST 1.0.
 *
 * @package    Sift
 * @subpackage plugin_pear
 */
class sfPearRest10 extends PEAR_REST_10 {

  /**
   * @see PEAR_REST_10
   */
  public function __construct($config, $options = array())
  {
    $class = isset($options['base_class']) ? $options['base_class'] : 'sfPearRest';

    $this->_rest = new $class($config, $options);
  }

}
