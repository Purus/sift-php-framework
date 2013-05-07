<?php
/*
 * This file is part of the Sift PHP framework.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * sfValidatorSlug validates a string to be a valid Url "slug".
 *
 * @package    Sift
 * @subpackage validator
 */
class sfValidatorSlug extends sfValidatorString {

  /**
   * @see sfValidatorString
   */
  public function configure($options = array(), $messages = array())
  {
    parent::configure($options, $messages);

    $this->addOption('pattern', '/^[a-zA-Z0-9-]+$/');
    $this->setMessage('invalid', 'The value is invalid slug.');
  }

  /**
   * @see sfValidatorBase
   */
  public function doClean($value)
  {
    $clean = parent::doClean($value);

    // sanitize the value
    $clean = $this->sanitizeValue($value);

    if(!preg_match($this->getOption('pattern'), $clean))
    {
      throw new sfValidatorError($this, 'invalid', array('value' => $value));
    }

    return $clean;
  }

  /**
   * Sanitizes the value. Replaces equivalent characters for dashes.
   * em-dash and en-dash will be replaced by normal dash.
   *
   * @param string $value
   * @return string
   */
  protected function sanitizeValue($value)
  {
    return str_replace(array('—', '–'), array('-'), $value);
  }

  /**
   * @see sfValidatorBase
   */
  public function getJavascriptValidationRules()
  {
    $rules = parent::getJavascriptValidationRules();
    $rules[sfFormJavascriptValidation::REGEX_PATTERN] = $this->getOption('pattern');
    return $rules;
  }

}
