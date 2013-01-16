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
   * @see sfStreamLogger
   */
  public function initialize($options = array())
  {
    $options['stream'] = defined('STDOUT') ? STDOUT : fopen('php://stdout', 'w');

    return parent::initialize($options);
  }
}
