<?php
/*
 * This file is part of the Sift PHP framework.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * sfValidatorBlacklist validates than the value is not one of the configured
 * forbidden values. This is a kind of opposite of the sfValidatorChoice
 * validator.
 *
 * @package    Sift
 * @subpackage validator
 */
class sfValidatorBlacklist extends sfValidatorBase
{
  /**
   * Configures the current validator.
   *
   * Available options:
   *
   *  * forbidden_values: An array of forbidden values (required)
   *  * case_sensitive:   Case sensitive comparison (default true)
   *
   * @param array $options    An array of options
   * @param array $messages   An array of error messages
   *
   * @see sfValidatorBase
   */
  protected function configure($options = array(), $messages = array())
  {
    $this->addRequiredOption('forbidden_values');
    $this->addOption('case_sensitive', true);
    $this->addMessage('forbidden', 'Value "%value%" is forbidden.');
  }

  /**
   * @see sfValidatorBase
   */
  protected function doClean($value)
  {
    $forbiddenValues = $this->getOption('forbidden_values');
    if ($forbiddenValues instanceof sfCallable) {
      $forbiddenValues = $forbiddenValues->call();
    }

    $checkValue = $value;

    if (false === $this->getOption('case_sensitive')) {
      $checkValue = sfUtf8::lower($checkValue);
      $forbiddenValues = array_map(sfUtf8::lower, $forbiddenValues);
    }

    if (in_array($checkValue, $forbiddenValues)) {
      throw new sfValidatorError($this, 'forbidden', array('value' => $value));
    }

    return $value;
  }

  public function getActiveMessages()
  {
    $messages = array();
    if ($this->getOption('required')) {
      $messages[] = $this->getMessage('required');
    }
    $messages[] = $this->getMessage('forbidden');

    return $messages;
  }

}
