<?php
/*
 * This file is part of the Sift PHP framework.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * sfValidatorBoolean validates a boolean. It also converts the input value to a valid boolean.
 *
 * @package    Sift
 * @subpackage validator
 */
class sfValidatorBoolean extends sfValidatorBase {

  /**
   * Configures the current validator.
   *
   * Available options:
   *
   *  * true_values:  The list of true values
   *  * false_values: The list of false values
   *
   * @param array $options    An array of options
   * @param array $messages   An array of error messages
   *
   * @see sfValidatorBase
   */
  protected function configure($options = array(), $messages = array())
  {
    $this->addOption('true_values', array('true', 't', 'yes', 'y', 'on', '1'));
    $this->addOption('false_values', array('false', 'f', 'no', 'n', 'off', '0'));

    $this->setOption('required', false);
    $this->setOption('empty_value', false);
  }

  /**
   * @see sfValidatorBase
   */
  protected function doClean($value)
  {
    if(in_array($value, $this->getOption('true_values')))
    {
      return true;
    }

    if(in_array($value, $this->getOption('false_values')))
    {
      return false;
    }

    throw new sfValidatorError($this, 'invalid', array('value' => $value));
  }

}