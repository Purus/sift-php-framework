<?php
/*
 * This file is part of the Sift PHP framework.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * The sfI18nDateFormatter class allows you to format phone numbers
 * which are in E164 format (like +18004686455)
 *
 * @package Sift
 * @subpackage i18n
 * @see http://publications.europa.eu/code/en/en-390300.htm
 */
class sfI18nPhoneNumberFormatter {

  /**
   * Instance holder
   *
   * @var sfI18nPhoneNumberFormatter
   */
  protected static $instance;

  /**
   * Code expression which matches
   *
   * @var string
   */
  protected $codeExpression;

  /**
   * Constructor
   *
   * @return sfI18nPhoneNumberFormatter The formatter instance
   */
  public function __construct()
  {
    $codes = array();
    foreach(sfCulture::getInstance()->getPhoneNumbers() as $validation)
    {
      $codes[] = $validation['code'];
    }
    $this->codeExpression = sprintf('/^(\+(%s))+/', join('|', array_unique($codes)));
  }

  /**
   * Returns an instance of the formatter
   *
   * @return sfI18nPhoneNumberFormatter
   */
  public static function getInstance()
  {
    if(!isset(self::$instance))
    {
      self::$instance = new sfI18nPhoneNumberFormatter();
    }
    return self::$instance;
  }

  /**
   * Formats the normalized phone number
   *
   * @param string $phoneNumber
   * @param string $culture The culture specific formatting
   * @return string Formatted phone number
   */
  public function format($phoneNumber, $culture = null)
  {
    if(!$culture)
    {
      $culture = sfConfig::get('sf_culture');
    }

    if(preg_match($this->codeExpression, $phoneNumber, $matches, PREG_OFFSET_CAPTURE))
    {
      $number = substr($phoneNumber, $matches[0][1] + strlen($matches[0][0]));
      $prefix = substr($phoneNumber, $matches[0][1], strlen($matches[0][0]));

      // culture like en_GB -> en
      if(($pos = strpos($culture, '_')) !== false)
      {
        $culture = substr($culture, 0, 2);
      }

      if(class_exists($class = sprintf('sfI18nPhoneNumberCultureFormatter%s', strtoupper($culture))))
      {
        // format only the number part
        $number = call_user_func(array($class, 'format'), $number);
      }

      return sprintf('%s %s', $prefix, $number);
    }

    return $phoneNumber;
  }

}
