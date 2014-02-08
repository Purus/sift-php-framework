<?php
/*
 * This file is part of the Sift PHP framework.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * sfValidatorNumber validates a number (integer or float). It also converts the input value to a float.
 *
 * @package    Sift
 * @subpackage validator
 */
class sfValidatorNumber extends sfValidatorBase
{
  /**
   * Float pattern regular expression
   *
   */
  const FLOAT_PATTERN = '/^[+-]?(\d*\.\d+([eE]?[+-]?\d+)?|\d+[eE][+-]?\d+)$/';

  /**
   * Configures the current validator.
   *
   * Available options:
   *
   *  * max: The maximum value allowed
   *  * min: The minimum value allowed
   *
   * Available error codes:
   *
   *  * max
   *  * min
   *
   * @param array $options   An array of options
   * @param array $messages  An array of error messages
   *
   * @see sfValidatorBase
   */
  protected function configure($options = array(), $messages = array())
  {
    $this->addMessage('max', '"%value%" must be less than %max%.');
    $this->addMessage('min', '"%value%" must be greater than %min%.');

    $this->addOption('min');
    $this->addOption('max');

    $this->setMessage('invalid', '"%value%" is not a number.');
  }

  /**
   * @see sfValidatorBase
   */
  protected function doClean($value)
  {
    if (!is_numeric($value)) {
      throw new sfValidatorError($this, 'invalid', array('value' => $value));
    }

    $clean = floatval($value);

    if ($this->hasOption('max') && $clean > $this->getOption('max')) {
      throw new sfValidatorError($this, 'max', array('value' => $value, 'max' => $this->getOption('max')));
    }

    if ($this->hasOption('min') && $clean < $this->getOption('min')) {
      throw new sfValidatorError($this, 'min', array('value' => $value, 'min' => $this->getOption('min')));
    }

    // FIXME: convert to string, since php float values are a bit pain?
    // BUT! converting to string will cause tests to fail
    return $clean;
  }

  /**
   * Checks if given value is float.
   *
   * @param mixed $val
   * @return boolean
   */
  protected function isFloat($val)
  {
    return (!is_bool($val) && (is_float($val) || preg_match(self::FLOAT_PATTERN, trim($val))));
  }

  /**
   * @see sfValidatorBase
   */
  public function getActiveMessages()
  {
    $messages = parent::getActiveMessages();

    if ($this->getOption('required')) {
      $messages[] = $this->getMessage('required');
    }
    if (!is_null($this->getOption('min'))) {
      $messages[] = $this->getMessage('min');
    }
    if (!is_null($this->getOption('max'))) {
      $messages[] = $this->getMessage('max');
    }

    return array_unique($messages);
  }

  public function getJavascriptValidationRules()
  {
    $rules = parent::getJavascriptValidationRules();

    $rules[sfFormJavascriptValidation::NUMBER] = true;

    if ($this->hasOption('max')) {
      $rules[sfFormJavascriptValidation::MAX] = $this->getOption('max');
    }

    if ($this->hasOption('min')) {
      $rules[sfFormJavascriptValidation::MIN] = $this->getOption('min');
    }

    return $rules;
  }

  public function getJavascriptValidationMessages()
  {
    $messages = parent::getJavascriptValidationMessages();

    $messages[sfFormJavascriptValidation::NUMBER] =
            sfFormJavascriptValidation::fixValidationMessage($this, 'invalid');

    if ($this->hasOption('max')) {
      $messages[sfFormJavascriptValidation::MAX] = sfFormJavascriptValidation::fixValidationMessage($this, 'max');
    }

    if ($this->hasOption('min')) {
      $messages[sfFormJavascriptValidation::MIN] = sfFormJavascriptValidation::fixValidationMessage($this, 'min');
    }

    return $messages;
  }

}
