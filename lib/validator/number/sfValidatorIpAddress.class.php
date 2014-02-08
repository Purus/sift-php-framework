<?php
/*
 * This file is part of the Sift PHP framework.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * sfValidatorIpAddress validates an IP address.
 *
 * @package    Sift
 * @subpackage validator
 */
class sfValidatorIpAddress extends sfValidatorBase
{
  /**
   * Configures the current validator.
   *
   * Available options:
   *
   *  * v4: Validate as IP address v4
   *  * v6: Validate as IP address v6
   *
   * Available error codes:
   *
   *  * invalid
   *
   * @param array $options   An array of options
   * @param array $messages  An array of error messages
   *
   * @see sfValidatorBase
   */
  protected function configure($options = array(), $messages = array())
  {
    $this->setMessage('invalid', '"%value%" is not a valid IP address.');

    // switch
    if (isset($options['v6']) && $options['v6']) {
      $this->addOption('v4', false);
    } else {
      $this->addOption('v4', true);
    }

    $this->addOption('v6', false);
  }

  /**
   * @see sfValidatorBase
   */
  protected function doClean($value)
  {
    $clean = trim(strval($value));

    if ($this->getOption('v4') && filter_var($clean, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4)) {
      return $clean;
    }

    // detect if it is a valid IPv6 Address
    if ($this->getOption('v6') && filter_var($clean, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6)) {
      return $clean;
    }

    throw new sfValidatorError($this, 'invalid', array('value' => $value));
  }

}
