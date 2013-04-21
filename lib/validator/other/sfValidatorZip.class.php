<?php
/*
 * This file is part of the Sift PHP framework.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * sfValidatorUrl validates Urls.
 *
 * @package    Sift
 * @subpackage validator
 */
class sfValidatorZip extends sfValidatorBase {

  /**
   * Postcode patterns
   *
   * @var array
   */
  protected $patterns = array();

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
    // strict mode
    $this->addOption('strict', isset($options['strict']) ?
                    $options['strict'] : false);
    // custom pattern
    $this->addOption('pattern', isset($options['pattern']) ?
                    $options['pattern'] : false);

    parent::__construct($options, $messages);

    if(!$this->getOption('countries') && !$this->getOption('pattern'))
    {
      throw new InvalidArgumentException('Please specify an array of countries or PCRE regular expression pattern for your registered sfValidatorZip validator.');
    }

    $this->loadPatterns($this->getOption('countries'));
  }

  /**
   * @see sfValidatorBase
   */
  public function configure($options = array(), $messages = array())
  {
    $this->setMessage('invalid', '"%value%" is invalid zip code.');
  }

  /**
   * Loads validation patterns for given countries from sfCulture object
   *
   * @param array|string $countries
   */
  protected function loadPatterns($countries)
  {
    if($countries != 'all')
    {
      // upper case countries
      $countries = array_map('strtoupper', $countries);
    }

    if($countries == 'all')
    {
      $patterns = sfCulture::getInstance()->getPostCodes();
    }
    else
    {
      $patterns = sfCulture::getInstance()->getPostCodes($countries);
    }

    $this->patterns = $patterns;
  }

  /**
   * Does the job
   *
   * @param string $value
   * @return string or throws an sfValidatorError
   */
  public function doClean($value)
  {
    if(!$this->getOption('strict'))
    {
      $value = trim(preg_replace('/\s+/', '', $value));
    }

    $match = false;

    // we will first validate countries
    foreach($this->patterns as $country => $pattern)
    {
      if(preg_match('/^' . $pattern . '$/', $value))
      {
        $match = true;
        break;
      }
    }

    // we have found something
    if($match)
    {
      return $value;
    }

    $pattern = $this->getOption('pattern');

    if($pattern && preg_match($pattern, $value))
    {
      $match = true;
    }

    if(!$match)
    {
      throw new sfValidatorError($this, 'invalid');
    }

    return $value;
  }

  public function getJavascriptValidationRules()
  {
    $rules = parent::getJavascriptValidationRules();

    $patterns = array();
    foreach($this->patterns as $pattern)
    {
      $patterns[] = sprintf('new RegExp("^%s$")', preg_quote($pattern));
    }

    $pattern = $this->getOption('pattern');

    if(!empty($pattern))
    {
      $patterns[] = sprintf('new RegExp("^%s$")', preg_quote($pattern));
    }

    $rules[sfFormJavascriptValidation::CUSTOM_CALLBACK] = array(
        'callback' =>
        (sprintf('function(value, element, params)
{
  var r = [%s];
  var result = false;
  for(var i = 0; i < r.length; i++)
  {
    try
    {
      result = r[i].test(value);
    }
    catch(e)
    {
      result = false;
    }
  }
  return result;
}', join(',', $patterns)
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