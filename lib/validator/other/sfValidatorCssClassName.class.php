<?php
/*
 * This file is part of the Sift PHP framework.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * sfValidatorCssClassName validates a value as CSS class
 *
 * @package    Sift
 * @subpackage validator
 */
class sfValidatorCssClassName extends sfValidatorRegex {

  /**
   * Configures the current validator.
   *
   * Available options:
   *
   *  * must_match: Whether the regex must match or not (true by default)
   *  * multiple:   Allow multiple values separated by space?
   *
   * @param array $options   An array of options
   * @param array $messages  An array of error messages
   *
   * @see sfValidatorString
   */
  protected function configure($options = array(), $messages = array())
  {
    parent::configure($options, $messages);

    // allow multiple values?
    $this->addOption('multiple_values', true);
    // see: http://stackoverflow.com/a/449000/515871
    $this->setOption('pattern', '/^-?[_a-zA-Z]+[_a-zA-Z0-9-]*$/');
    $this->setMessage('invalid', 'This value is invalid CSS class.');
  }

  /**
   *
   * @see sfValidatorBase
   */
  public function doClean($value)
  {
    // multiple allowed?
    if($this->getOption('multiple_values'))
    {
      $values = explode(' ', $value);
      $result = array();
      foreach($values as $value)
      {
        $value = trim($value);
        $result[] = parent::doClean($value);
      }
      return join(' ', $result);
    }
    return parent::doClean($value);
  }

  /**
   * @see sfValidatorBase
   */
  public function getJavascriptValidationRules()
  {
    if(!$this->getOption('multiple_values'))
    {
      return parent::getJavascriptValidationRules();
    }

    $rules = parent::getJavascriptValidationRules();

    unset($rules[sfFormJavascriptValidation::REGEX_PATTERN]);
    unset($rules[sfFormJavascriptValidation::REGEX_PATTERN_NEGATIVE]);

    // rewrite pattern to allow spaces
    $pattern = trim($this->getPattern(), '/');
    $callback = new sfJsonExpression(sprintf('function(value, element, params) {
    var parts = value.split(" ");
    var regex = new RegExp("%s")
    for(var i = 0; i < parts.length; i++)
    {
      if(%sregex.test(parts[i]))
      {
        return false;
      }
    }
    return true;
}', $pattern, $this->getOption('must_match') ? '!' : ''));

    $rules[sfFormJavascriptValidation::CUSTOM_CALLBACK] = array('callback' => $callback);

    return $rules;
  }

  public function getJavascriptValidationMessages()
  {
    if(!$this->getOption('multiple_values'))
    {
      return parent::getJavascriptValidationMessages();
    }

    $messages = parent::getJavascriptValidationMessages();
    unset($messages[sfFormJavascriptValidation::REGEX_PATTERN]);
    unset($messages[sfFormJavascriptValidation::REGEX_PATTERN_NEGATIVE]);

    $messages[sfFormJavascriptValidation::CUSTOM_CALLBACK] =
            sfFormJavascriptValidation::fixValidationMessage($this, 'invalid');

    return $messages;
  }

}