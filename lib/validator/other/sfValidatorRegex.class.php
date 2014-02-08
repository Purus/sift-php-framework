<?php
/*
 * This file is part of the Sift PHP framework.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * sfValidatorRegex validates a value with a regular expression.
 *
 * @package    Sift
 * @subpackage validator
 */
class sfValidatorRegex extends sfValidatorString {

  /**
   * Configures the current validator.
   *
   * Available options:
   *
   *  * pattern:    A regex pattern compatible with PCRE or {@link sfCallable} that returns one (required)
   *  * must_match: Whether the regex must match or not (true by default)
   *
   * @param array $options   An array of options
   * @param array $messages  An array of error messages
   *
   * @see sfValidatorString
   */
  protected function configure($options = array(), $messages = array())
  {
    parent::configure($options, $messages);

    $this->addRequiredOption('pattern');
    $this->addOption('must_match', true);
  }

  /**
   * @see sfValidatorString
   */
  protected function doClean($value)
  {
    $clean = parent::doClean($value);

    $pattern = $this->getPattern();

    if(
            ($this->getOption('must_match') && !preg_match($pattern, $clean)) ||
            (!$this->getOption('must_match') && preg_match($pattern, $clean))
    )
    {
      throw new sfValidatorError($this, 'invalid', array('value' => $value, 'pattern' => $pattern));
    }

    return $clean;
  }

  /**
   * Returns the current validator's regular expression.
   *
   * @return string
   */
  public function getPattern()
  {
    $pattern = $this->getOption('pattern');

    return $pattern instanceof sfCallable ? $pattern->call() : $pattern;
  }

  /**
   * Returns active messages (based on active options). This is usefull for
   * i18n extract task.
   *
   * @return array
   */
  public function getActiveMessages()
  {
    return array($this->getMessage('invalid'));
  }

  /**
   * @see sfValidatorBase
   */
  public function getJavascriptValidationRules()
  {
    $rules = parent::getJavascriptValidationRules();

    if($this->getOption('must_match'))
    {
      $rules[sfFormJavascriptValidation::REGEX_PATTERN] = $this->getPattern();
    }
    else
    {
      $rules[sfFormJavascriptValidation::REGEX_PATTERN_NEGATIVE] = $this->getPattern();
    }

    return $rules;
  }

  /**
   * @see sfValidatorBase
   */
  public function getJavascriptValidationMessages()
  {
    $messages = parent::getJavascriptValidationMessages();
    $messages[sfFormJavascriptValidation::REGEX_PATTERN] =
            sfFormJavascriptValidation::fixValidationMessage($this, 'invalid');

    return $messages;
  }

}
