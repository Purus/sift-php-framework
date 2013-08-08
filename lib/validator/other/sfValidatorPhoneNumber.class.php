<?php
/*
 * This file is part of the Sift PHP framework.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * sfValidatorPhoneNumber validates phone numbers
 *
 * @package    Sift
 * @subpackage validator
 * @see http://snipplr.com/view/20539/
 */
class sfValidatorPhoneNumber extends sfValidatorBase {

  /**
   * Phone validation information
   *
   * @var array
   */
  protected $validationInfo = array();

  /**
   * Internal value representing the maximum number of characters an input number may contain
   *
   * @var integer
   */
  protected $maxInputLength = 32;

  /**
   * Replacement mappings
   *
   * @var array
   */
  public static $searchReplaceMapping = array(
    '++' => '+',
    '+' => '',
    '(0)' => '',
    // funny user input goulash
    'i' => '1', 'I' => '1', 'l' => '1',
    'o' => '0', 'O' => '0',
    // ([^\diIloO\+]*)
    // ...brackets
    '(' => '', ')' => '',
    '[' => '', ']' => '',
    // slashes
    '/' => '', '\\\\' => '',
    // dashes
    '-' => '', '_' => '',
    // whitespaces
    ' ' => '',
  );

  /**
   * Mappings for conversion phone numbers like: 123CALLME
   *
   * Common Phone Keypads for Alpha code translation (we only use the international standard)
   *
   *                           1   2   3   4   5   6    7    8   9    0
   * International Standard        ABC DEF GHI JKL MNO PQRS TUV WXYZ
   * North American Classic        ABC DEF GHI JKL MN  PRS  TUV WXY
   * Australian Classic        QZ  ABC DEF GHI JKL MNO PRS  TUV WXY
   * UK Classic                    ABC DEF GHI JKL MN  PRS  TUV WXY    OQ
   * Mobile 1                      ABC DEF GHI JKL MN  PRS  TUV WXY    OQZ
   * @var type
   */
  public static $searchKeypadMapping = array(
    'a' => '2','b' => '2','c' => '2', 'A' => '2','B' => '2','C' => '2',
    'd' => '3','e' => '3','f' => '3', 'D' => '3','E' => '3','F' => '3',
    'g' => '4','h' => '4','i' => '4', 'G' => '4','H' => '4','I' => '4',
    'j' => '5','k' => '5','l' => '5', 'J' => '5','K' => '5','L' => '5',
    'm' => '6','n' => '6','o' => '6', 'M' => '6','N' => '6','O' => '6',
    'p' => '7','q' => '7','r' => '7', 's' => '7','Q' => '7','Q' => '7','R' => '2','S' => '7',
    't' => '8','u' => '8','v' => '8', 'T' => '8','U' => '8','V' => '8',
    'x' => '9','y' => '9','z' => '9', 'X' => '9','Y' => '9','Z' => '9'
  );

  /**
   * Constructs the validator.
   *
   *
   * @param array $options Array of options
   * @param array $messages Array of messages
   *
   * @throws InvalidArgumentException
   */
  public function __construct($options = array(), $messages = array())
  {
    // countries
    $this->addOption('countries', isset($options['countries']) ?
                    $options['countries'] : 'all');

    $this->addOption('keypad_conversion', true);

    parent::__construct($options, $messages);

    if(!$this->getOption('countries') && !$this->getOption('pattern'))
    {
      throw new InvalidArgumentException(sprintf('Please specify an array of countries or PCRE regular expression pattern for your registered %s validator.', get_class($this)));
    }

    $this->loadValidationInfo($this->getOption('countries'));
  }

  /**
   * @see sfValidatorBase
   */
  public function configure($options = array(), $messages = array())
  {
    $this->setMessage('invalid', '"%value%" is invalid phone number.');
  }

  /**
   * Loads phone number validation information for given countries from sfCulture object
   *
   * @param array|string $countries
   */
  protected function loadValidationInfo($countries)
  {
    if($countries != 'all')
    {
      // upper case countries
      $countries = array_map('strtoupper', $countries);
    }

    if($countries == 'all')
    {
      $info = sfCulture::getInstance()->getPhoneNumbers();
    }
    else
    {
      $info = sfCulture::getInstance()->getPhoneNumbers($countries);
    }

    $this->validationInfo = $info;
  }

  /**
   * Does the job
   *
   * @param string $value
   * @return string or throws an sfValidatorError
   */
  public function doClean($value)
  {
    $match = false;

    $cleaned = $this->preClean($value);

    // input is too long
    if(strlen($cleaned) > $this->maxInputLength)
    {
      throw new sfValidatorError($this, 'invalid', array('value' => $value));
    }

    // we will first validate countries
    foreach($this->validationInfo as $country => $validation)
    {
      foreach($validation['patterns'] as $pattern)
      {
        $expression = $this->buildExpression($pattern, $validation);
        // prepare the expression
        if(preg_match($expression, $cleaned))
        {
          $match = true;
          $cleaned = self::normalizePhoneNumber($cleaned, $country);
          break;
        }
      }
    }

    if(!$match)
    {
      throw new sfValidatorError($this, 'invalid', array('value' => $value));
    }

    return $cleaned;
  }

  /**
   * Cleans the value before testing
   *
   * @param string $value The value
   * @param array $validation Validation data
   */
  public function preClean($value)
  {
    // clean spaces
    $value = preg_replace('#\s+#', '', $value);

    if($this->getOption('keypad_conversion'))
    {
      // fetch search and replace arrays
      $search = array_keys(self::$searchKeypadMapping);
      $replace = array_values(self::$searchKeypadMapping);
      $value = str_replace($search, $replace, $value);
    }

    // fetch search and replace arrays
    $search = array_keys(self::$searchReplaceMapping);
    $replace = array_values(self::$searchReplaceMapping);

    // simple string replacement
    $value = str_replace($search, $replace, $value);

    // lets kick out all dutty stuff which is left...
    $value = preg_replace('~[^\d]~', '', $value);

    return $value;
  }

  /**
   * Normalize the phone number to fit the +CCCXXXYYYY (E.164) format
   *
   * @param string $number
   * @param string $culture
   */
  public static function normalizePhoneNumber($number, $country)
  {
    $format = current(sfCulture::getInstance()->getPhoneNumbers(array($country)));

    // strip +420 prefix for CZ
    if(preg_match(sprintf('/^(\+?%s)/', $format['code']), $number, $matches, PREG_OFFSET_CAPTURE))
    {
      $number = substr($number, $matches[0][1] + strlen($matches[0][0]));
    }
    // strip international prefix in form 00420 for CZ
    elseif(preg_match(sprintf('/^%s%s/', $format['iprefix'], $format['code']), $number, $matches, PREG_OFFSET_CAPTURE))
    {
      $number = substr($number, $matches[0][1] + strlen($matches[0][0]));
    }

    // normalize the number to international format
    $number = sprintf('+%s%s', $format['code'], $number);

    return $number;
  }

  /**
   * Build the regular expression
   *
   * @param array $pattern
   * @param array $validation
   * @return string
   */
  protected function buildExpression($pattern, $validation, $delimiter = '/')
  {
    return sprintf('%s^((%s%s)|(%s))?%s$%s',
              $delimiter,
              $validation['iprefix'],
              $validation['code'],
              $validation['code'],
              $pattern['pattern'],
              $delimiter);
  }

  public function getJavascriptValidationRules()
  {
    $rules = parent::getJavascriptValidationRules();

    $patterns = array();

    foreach($this->validationInfo as $country => $validation)
    {
      foreach($validation['patterns'] as $pattern)
      {
        $patterns[] = sprintf('%s', $this->buildExpression($pattern, $validation));
      }
    }

    $replacements = array();
    foreach(self::$searchReplaceMapping as $search => $replace)
    {
      $replacements[] = sprintf('replace(/%s/g, "%s")', preg_quote($search, '/'), $replace);
    }

    if($this->getOption('keypad_conversion'))
    {
      foreach(self::$searchKeypadMapping as $search => $replace)
      {
        $replacements[] = sprintf('replace(/%s/g, "%s")', preg_quote($search, '/'), $replace);
      }
    }

    $rules[sfFormJavascriptValidation::CUSTOM_CALLBACK] = array(
        'callback' =>
        (sprintf('function(value, element, params)
{
  /**
   * Cleans up the number
   *
   */
  var cleanNumber = function(number)
  {
    var cleanedNumber = number.%s;
    return cleanedNumber;
  }

  var r = [%s];
  var result = false;
  var cleaned = cleanNumber(value);
  for(var i = 0; i < r.length; i++)
  {
    try
    {
      result = r[i].test(cleaned);
      if(result)
      {
        return result;
      }
    }
    catch(e)
    {
    }
  }
  return result;
}',
    join(".", $replacements),
    join(',', $patterns)
    )));
    return $rules;
  }

  public function getJavascriptValidationMessages()
  {
    $messages = parent::getJavascriptValidationMessages();
    $messages[sfFormJavascriptValidation::CUSTOM_CALLBACK] =
            sfFormJavascriptValidation::fixValidationMessage($this, 'invalid');
    return $messages;
  }

}