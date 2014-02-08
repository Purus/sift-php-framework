<?php
/*
 * This file is part of the Sift PHP framework.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * sfValidatorInteger validates an integer. It also converts the input value to an integer.
 *
 * @package    Sift
 * @subpackage validator
 */
class sfValidatorInteger extends sfValidatorBase
{
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
    $this->addMessage('max', '"%value%" must be at most %max%.');
    $this->addMessage('min', '"%value%" must be at least %min%.');

    $this->addOption('min');
    $this->addOption('max');

    $this->setMessage('invalid', '"%value%" is not an integer.');
  }

  /**
   * @see sfValidatorBase
   */
  protected function doClean($value)
  {
    $clean = intval($value);

    if (strval($clean) != $value) {
      throw new sfValidatorError($this, 'invalid', array('value' => $value));
    }

    if ($this->hasOption('max') && $clean > $this->getOption('max')) {
      throw new sfValidatorError($this, 'max', array('value' => $value, 'max' => $this->getOption('max')));
    }

    if ($this->hasOption('min') && $clean < $this->getOption('min')) {
      throw new sfValidatorError($this, 'min', array('value' => $value, 'min' => $this->getOption('min')));
    }

    return $clean;
  }

  public function getJavascriptValidationRules()
  {
    $rules = parent::getJavascriptValidationRules();

    $rules[sfFormJavascriptValidation::DIGITS] = true;

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

    $messages[sfFormJavascriptValidation::DIGITS] =
            sfFormJavascriptValidation::fixValidationMessage($this, 'invalid');

    if ($this->hasOption('max')) {
      $messages[sfFormJavascriptValidation::MAX] =
          sfFormJavascriptValidation::fixValidationMessage($this, 'max');
    }

    if ($this->hasOption('min')) {
      $messages[sfFormJavascriptValidation::MIN] =
          sfFormJavascriptValidation::fixValidationMessage($this, 'min');
    }

    return $messages;
  }

}
