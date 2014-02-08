<?php
/*
 * This file is part of the Sift PHP framework.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * sfValidatorFirstName validates first name of a person.
 *
 * @package    Sift
 * @subpackage validator
 */
class sfValidatorFirstName extends sfValidatorAnd {

  /**
   * Pattern used to verify the name. Excludes
   * < > & \ | digits and +
   *
   * @var string
   */
  public static $pattern = '/^[^<>&\|0-9\+]+$/';

  public function __construct($options = array(), $messages = array())
  {
    // add default messages
    $this->addOption('min_length', 3);
    $this->addOption('max_length', 128);
    $this->addOption('required', false);
    $this->addOption('pattern', self::$pattern);
    $this->addMessage('invalid', 'First name is not valid.');
    $this->addMessage('required', 'First name is required.');

    parent::__construct(null, $options, $messages);

    $this->setValidators();
  }

  public function setValidators()
  {
    // Disallow <, >, & and | in full names. We forbid | because
    // it is part of our preferred microformat for lists of disambiguated
    // full names in sfGuard apps: Full Name (username) | Full Name (username) | Full Name (username)
    $this->addValidator(new sfValidatorString(
        array(
        'required' => true,
        'trim' => true,
        'min_length' => $this->getOption('min_length'),
        'max_length' => $this->getOption('max_length')),
        array(
          'required'   => $this->getMessage('required'),
          'min_length' => $this->getMessage('invalid'),
          'max_length' => $this->getMessage('invalid')
        )
    ));

    $this->addValidator(new sfValidatorRegex(
      array('pattern' => $this->getOption('pattern')),
      array('invalid' => $this->getMessage('invalid'))));
  }

  /**
   * Strtouppers first character of the name
   *
   * @param string $value
   */
  public function doClean($value)
  {
    return sfUtf8::ucfirst($value);
  }

  /**
   * @see sfValidatorBase
   */
  public function getJavascriptValidationRules()
  {
    return array(
      sfFormJavascriptValidation::REQUIRED => $this->getOption('required'),
      sfFormJavascriptValidation::MIN_LENGTH => $this->getOption('min_length'),
      sfFormJavascriptValidation::MAX_LENGTH => $this->getOption('max_length'),
      sfFormJavascriptValidation::REGEX_PATTERN => $this->getOption('pattern')
    );
  }

  /**
   * @see sfValidatorBase
   */
  public function getJavascriptValidationMessages()
  {
    return array(
      sfFormJavascriptValidation::REQUIRED =>
            sfFormJavascriptValidation::fixValidationMessage($this, 'required'),
      sfFormJavascriptValidation::MIN_LENGTH =>
            sfFormJavascriptValidation::fixValidationMessage($this, 'invalid'),
      sfFormJavascriptValidation::MAX_LENGTH =>
            sfFormJavascriptValidation::fixValidationMessage($this, 'invalid'),
      sfFormJavascriptValidation::REGEX_PATTERN =>
            sfFormJavascriptValidation::fixValidationMessage($this, 'invalid')
    );
  }

  /**
   * @see sfValidatorBase
   */
  public function getActiveMessages()
  {
    $messages = array();
    $messages[] = $this->getMessage('invalid');
    if($this->getOption('required'))
    {
      $messages[] = $this->getMessage('required');
    }

    return $messages;
  }

}
