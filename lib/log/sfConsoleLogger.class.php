<?php
/*
 * This file is part of the Sift PHP framework.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * Log messages to the console.
 *
 * @package    Sift
 * @subpackage log
 */
class sfConsoleLogger extends sfStreamLogger
{
  /**
   * Constructor
   *
   * @param array $options Array of options
   */
  public function __construct($options = array())
  {
    $options['stream'] = defined('STDOUT') ? STDOUT : fopen('php://stdout', 'w');
    parent::__construct($options);
  }

}
