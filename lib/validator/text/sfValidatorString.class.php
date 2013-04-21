<?php
/*
 * This file is part of the Sift PHP framework.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * sfValidatorString validates a string. It also converts the input value to a string.
 *
 * @package    Sift
 * @subpackage validator
 */
class sfValidatorString extends sfValidatorBase {

  /**
   * Configures the current validator.
   *
   * Available options:
   *
   *  * max_length: The maximum length of the string
   *  * min_length: The minimum length of the string
   *  * sanitize:   Sanitizes the value using sfSanitizer::sanitize()
   *
   * Available error codes:
   *
   *  * max_length
   *  * min_length
   *
   * @param array $options   An array of options
   * @param array $messages  An array of error messages
   *
   * @see sfValidatorBase
   */
  protected function configure($options = array(), $messages = array())
  {
    $this->addMessage('max_length', 'Value is too long (%max_length% characters max).');
    $this->addMessage('min_length', 'Value is too short (%min_length% characters min).');

    $this->addOption('max_length');
    $this->addOption('min_length');

    $this->addOption('trim', true);
    $this->addOption('sanitize', false);

    $this->setOption('empty_value', '');
  }

  /**
   * Cleans the input value.
   *
   * This method is also responsible for trimming the input value
   * and checking the required option.
   *
   * @param  mixed $value  The input value
   *
   * @return mixed The cleaned value
   *
   * @throws sfValidatorError
   */
  public function clean($value)
  {
    $clean = $value;

    // first sanitize!
    if($sanitize = $this->getOption('sanitize'))
    {
      $clean = sfSanitizer::sanitize($clean, $sanitize);
    }

    if($this->options['trim'] && is_string($clean))
    {
      $clean = trim($clean);
    }

    // empty value?
    if($this->isEmpty($clean))
    {
      // required?
      if($this->options['required'])
      {
        throw new sfValidatorError($this, 'required');
      }

      return $this->getEmptyValue();
    }

    return $this->doClean($clean);
  }

  /**
   * @see sfValidatorBase
   */
  protected function doClean($value)
  {
    $clean = (string) $value;

    $length = function_exists('mb_strlen') ? mb_strlen($clean, $this->getCharset()) : strlen($clean);

    if($this->hasOption('max_length') && $length > $this->getOption('max_length'))
    {
      throw new sfValidatorError($this, 'max_length', array('value' => $value, 'max_length' => $this->getOption('max_length')));
    }

    if($this->hasOption('min_length') && $length < $this->getOption('min_length'))
    {
      throw new sfValidatorError($this, 'min_length', array('value' => $value, 'min_length' => $this->getOption('min_length')));
    }

    return $clean;
  }

  /**
   * Returns active messages (based on active options). This is usefull for
   * i18n extract task.
   *
   * @return array
   */
  public function getActiveMessages()
  {
    $messages = array();
    if($this->getOption('required'))
    {
      $messages[] = $this->getMessage('required');
    }
    if($this->getOption('min_length'))
    {
      $messages[] = $this->getMessage('min_length');
    }
    if($this->getOption('max_length'))
    {
      $messages[] = $this->getMessage('max_length');
    }
    return $messages;
  }

  /**
   * @see sfValidatorBase
   */
  public function getJavascriptValidationMessages()
  {
    $messages = parent::getJavascriptValidationMessages();
    if($this->getOption('min_length'))
    {
      $messages[sfFormJavascriptValidation::MIN_LENGTH] =
              sfFormJavascriptValidation::fixValidationMessage($this, 'min_length');
    }

    if($this->getOption('max_length'))
    {
      $messages[sfFormJavascriptValidation::MAX_LENGTH] =
              sfFormJavascriptValidation::fixValidationMessage($this, 'max_length');
    }
    return $messages;
  }

  /**
   * @see sfValidatorBase
   */
  public function getJavascriptValidationRules()
  {
    $rules = parent::getJavascriptValidationRules();

    if($this->getOption('min_length'))
    {
      $rules[sfFormJavascriptValidation::MIN_LENGTH] = $this->getOption('min_length');
    }

    if($this->getOption('max_length'))
    {
      $rules[sfFormJavascriptValidation::MAX_LENGTH] = $this->getOption('max_length');
    }

    return $rules;
  }

}
