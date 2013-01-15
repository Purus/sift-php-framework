<?php
/*
 * This file is part of the Sift PHP framework.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * sfI18nNumberFormat class
 * 
 * Defines how numeric values are formatted and displayed,
 * depending on the culture. Numeric values are formatted using
 * standard or custom patterns stored in the properties of a 
 * sfI18nNumberFormat. 
 *
 * This class contains information, such as currency, decimal 
 * separators, and other numeric symbols.
 *
 * To create a sfI18nNumberFormat for a specific culture, 
 * create a sfI18nCulture for that culture and call sfI18nCultureInfo->getNumberFormat(). 
 * Or use sfI18nNumberFormat::getInstance($culture).
 * 
 * To create a sfI18nNumberFormat for the invariant culture, use the 
 * sfI18nCulture::getInvariantInfo(). 
 *
 * @package Sift
 * @subpackage i18n
 */
class sfI18nNumberFormat {

  /**
   * Format as decimal value
   */
  const DECIMAL = 0;
  
  /**
   * Format as currency value
   */
  const CURRENCY = 1;
  
  /**
   * Format as percentage value
   */
  const PERCENTAGE = 2;
  
  /**
   * Format as scientific value
   */
  const SCIENTIFIC = 3;
  
  /**
   * CLDR number formatting data.
   * 
   * @var array
   */
  protected $data = array();

  /**
   * A list of properties that are accessable/writable.
   * 
   * @var array
   */
  protected $properties = array();

  /**
   * The number pattern.
   * 
   * @var array
   */
  protected $pattern = array();

  /**
   * Invariant holder
   * 
   * @var sfI18nNumberFormat
   */
  protected static $invariant;

  /**
   * Allows functions that begins with 'set' to be called directly
   * as an attribute/property to retrieve the value.
   *
   * @return mixed
   */
  public function __get($name)
  {
    $getProperty = 'get' . $name;
    if(in_array($getProperty, $this->properties))
    {
      return $this->$getProperty();
    }
    else
    {
      throw new sfException(sprintf('Property %s does not exists.', $name));
    }
  }

  /**
   * Allows functions that begins with 'set' to be called directly
   * as an attribute/property to set the value.
   */
  public function __set($name, $value)
  {
    $setProperty = 'set' . $name;
    if(in_array($setProperty, $this->properties))
    {
      $this->$setProperty($value);
    }
    else
    {
      throw new sfException(sprintf('Property %s can not be set.', $name));
    }
  }

  /**
   * Initializes a new writable instance of the sfI18nNumberFormat class 
   * that is dependent on the CLDR data for number, decimal, and currency
   * formatting information. N.B. You should not initialize this 
   * class directly unless you know what you are doing. Please use use 
   * sfI18nNumberFormat::getInstance() to create an instance.
   *
   * @param array CLDR data for date time formatting.
   * @see getInstance()
   */
  public function __construct($data = array(), $type = self::DECIMAL)
  {
    if(empty($data))
    {
      throw new sfException('Please provide the I18N data to initialize.');
    }
    
    $this->properties = get_class_methods($this);
    $this->data = $data;
    $this->setPattern($type);
  }

  /**
   * Sets the pattern for a specific number pattern. The validate patterns
   * sfI18nNumberFormat::DECIMAL, sfI18nNumberFormat::CURRENCY,
   * sfI18nNumberFormat::PERCENTAGE, or sfI18nNumberFormat::SCIENTIFIC
   *
   * @param int pattern type.
   */
  public function setPattern($type = sfI18nNumberFormat::DECIMAL)
  {
    if(is_int($type))
    {
      $this->pattern = $this->parsePattern($this->data['numberPatterns'][$type]);
    }
    else
    {
      $this->pattern = $this->parsePattern($type);
    }

    $this->pattern['negInfty'] = $this->data['numberElements'][6] . $this->data['numberElements'][9];
    $this->pattern['posInfty'] = $this->data['numberElements'][11] . $this->data['numberElements'][9];
  }

  /**
   * Get the pattern for a specific number pattern. The validate patterns
   * sfI18nNumberFormat::DECIMAL, sfI18nNumberFormat::CURRENCY,
   * sfI18nNumberFormat::PERCENTAGE, or sfI18nNumberFormat::SCIENTIFIC
   * 
   * @return integer 
   */
  public function getPattern()
  {
    return $this->pattern;
  }

  /**
   * Gets the default sfI18nNumberFormat that is culture-independent (invariant).
   *
   * @return sfI18nNumberFormat default sfI18nNumberFormat. 
   */
  public static function getInvariantInfo($type = sfI18nNumberFormat::DECIMAL)
  {
    if(is_null(self::$invariant))
    {
      $culture = sfCulture::getInvariantCulture();
      self::$invariant = $culture->getNumberFormat();
      self::$invariant->setPattern($type);
    }
    return self::$invariant;
  }

  /**
   * Returns the sfI18nNumberFormat associated with the specified culture.
   *
   * @param sfCulture the culture that gets the sfNumberFormat property.
   * @param int the number formatting type, it should be 
   * sfI18nNumberFormat::DECIMAL, sfI18nNumberFormat::CURRENCY,
   * sfI18nNumberFormat::PERCENTAGE, or sfI18nNumberFormat::SCIENTIFIC
   * @return sfI18nNumberFormat sfI18nNumberFormat for the specified culture.
   * @see getCurrencyInstance();
   * @see getPercentageInstance();
   * @see getScientificInstance();
   */
  public static function getInstance($culture = null, $type = sfI18nNumberFormat::DECIMAL)
  {
    if($culture instanceof sfCulture)
    {
      $formatInfo = $culture->getNumberFormat();
      $formatInfo->setPattern($type);

      return $formatInfo;
    }
    else if(is_string($culture))
    {
      $sfCulture = new sfCulture($culture);
      $formatInfo = $sfCulture->getNumberFormat();
      $formatInfo->setPattern($type);

      return $formatInfo;
    }
    else
    {
      $sfCulture = new sfCulture();
      $formatInfo = $sfCulture->getNumberFormat();
      $formatInfo->setPattern($type);
      return $formatInfo;
    }
  }

  /**
   * Returns the currency format info associated with the specified culture.
   *
   * @param sfCulture the culture that gets the NumberFormat property.
   * @return sfI18nNumberFormat sfI18nNumberFormat for the specified culture.
   */
  public static function getCurrencyInstance($culture = null)
  {
    return self::getInstance($culture, self::CURRENCY);
  }

  /**
   * Returns the percentage format info associated with the specified culture.
   *
   * @param sfCulture the culture that gets the NumberFormat property.
   * @return sfI18nNumberFormat sfI18nNumberFormat for the specified culture. 
   */
  public static function getPercentageInstance($culture = null)
  {
    return self::getInstance($culture, self::PERCENTAGE);
  }

  /**
   * Returns the scientific format info associated with the specified culture.
   *
   * @param sfCulture the culture that gets the NumberFormat property.
   * @return sfI18nNumberFormat sfI18nNumberFormat for the specified culture. 
   */
  public static function getScientificInstance($culture = null)
  {
    return self::getInstance($culture, self::SCIENTIFIC);
  }

  /**
   * Parses the given pattern and return a list of known properties.
   *
   * @param string a number pattern.
   * @return array list of pattern properties.
   */
  protected function parsePattern($pattern)
  {
    $pattern = explode(';', $pattern);

    $negative = null;
    if(count($pattern) > 1)
    {
      $negative = $pattern[1];
    }
    $pattern = $pattern[0];

    $comma = ',';
    $dot = '.';
    $digit = '0';
    $hash = '#';

    // find the first group point, and decimal point
    $groupPos1 = strrpos($pattern, $comma);
    $decimalPos = strrpos($pattern, $dot);

    $groupPos2 = false;
    $groupSize1 = false;
    $groupSize2 = false;
    $decimalPoints = is_int($decimalPos) ? -1 : false;

    $info['negPref'] = $this->data['numberElements'][6];
    $info['negPost'] = '';

    $info['negative'] = $negative;
    $info['positive'] = $pattern;

    // find the negative prefix and postfix
    if($negative)
    {
      $prefixPostfix = $this->getPrePostfix($negative);
      $info['negPref'] = $prefixPostfix[0];
      $info['negPost'] = $prefixPostfix[1];
    }

    $posfix = $this->getPrePostfix($pattern);
    $info['posPref'] = $posfix[0];
    $info['posPost'] = $posfix[1];

    if(is_int($groupPos1))
    {
      // get the second group
      $groupPos2 = strrpos(substr($pattern, 0, $groupPos1), $comma);

      // get the number of decimal digits
      if(is_int($decimalPos))
      {
        $groupSize1 = $decimalPos - $groupPos1 - 1;
      }
      else
      {
        // no decimal point, so traverse from the back
        // to find the groupsize 1.
        for($i = strlen($pattern) - 1; $i >= 0; $i--)
        {
          if($pattern{$i} == $digit || $pattern{$i} == $hash)
          {
            $groupSize1 = $i - $groupPos1;
            break;
          }
        }
      }

      // get the second group size
      if(is_int($groupPos2))
      {
        $groupSize2 = $groupPos1 - $groupPos2 - 1;
      }
    }

    if(is_int($decimalPos))
    {
      for($i = strlen($pattern) - 1; $i >= 0; $i--)
      {
        if($pattern{$i} == $dot)
        {
          break;
        }
        if($pattern{$i} == $digit)
        {
          $decimalPoints = $i - $decimalPos;
          break;
        }
      }
    }

    $info['groupPos1'] = $groupPos1;
    $info['groupSize1'] = $groupSize1;
    $info['groupPos2'] = $groupPos2;
    $info['groupSize2'] = $groupSize2;
    $info['decimalPos'] = $decimalPos;
    $info['decimalPoints'] = $decimalPoints;

    return $info;
  }

  /**
   * Gets the prefix and postfix of a pattern.
   *
   * @param string pattern
   * @return array of prefix and postfix, array(prefix,postfix). 
   */
  protected function getPrePostfix($pattern)
  {
    $regexp = '/[#,\.0]+/';
    $result = preg_split($regexp, $pattern);
    return array($result[0], $result[1]);
  }

  /**
   * Indicates the number of decimal places.
   *
   * @return int number of decimal places.
   */
  public function getDecimalDigits()
  {
    return $this->pattern['decimalPoints'];
  }

  /**
   * Sets the number of decimal places.
   *
   * @param int number of decimal places.
   */
  public function setDecimalDigits($value)
  {
    return $this->pattern['decimalPoints'] = $value;
  }

  /**
   * Gets the string to use as the decimal separator.
   *
   * @return string decimal separator.
   */
  public function getDecimalSeparator()
  {
    return $this->data['numberElements'][0];
  }

  /**
   * Sets the string to use as the decimal separator.
   *
   * @param string the decimal point
   */
  public function setDecimalSeparator($value)
  {
    return $this->data['numberElements'][0] = $value;
  }

  /**
   * Gets the string that separates groups of digits to the left 
   * of the decimal in currency values.
   *
   * @param parameter
   * @return string currency group separator. 
   */
  public function getGroupSeparator()
  {
    return $this->data['numberElements'][1];
  }

  /**
   * Sets the string to use as the group separator.
   *
   * @param string the group separator.
   */
  public function setGroupSeparator($value)
  {
    return $this->data['numberElements'][1] = $value;
  }

  /**
   * Gets the number of digits in each group to the left of the decimal
   * There can be two grouping sizes, this fucntion
   * returns array(group1, group2), if there is only 1 grouping size,
   * group2 will be false.
   *
   * @return array grouping size(s). 
   */
  public function getGroupSizes()
  {
    $group1 = $this->pattern['groupSize1'];
    $group2 = $this->pattern['groupSize2'];

    return array($group1, $group2);
  }

  /**
   * Sets the number of digits in each group to the left of the decimal.
   * There can be two grouping sizes, the value should
   * be an array(group1, group2), if there is only 1 grouping size,
   * group2 should be false.
   *
   * @param array grouping size(s).
   */
  public function setGroupSizes($groupSize)
  {
    $this->pattern['groupSize1'] = $groupSize[0];
    $this->pattern['groupSize2'] = $groupSize[1];
  }

  /**
   * Gets the format pattern for negative values.
   * The negative pattern is composed of a prefix, and postfix.
   * This function returns array(prefix, postfix).
   *
   * @return arary negative pattern. 
   */
  public function getNegativePattern()
  {
    $prefix = $this->pattern['negPref'];
    $postfix = $this->pattern['negPost'];

    return array($prefix, $postfix);
  }

  /**
   * Sets the format pattern for negative values.
   * The negative pattern is composed of a prefix, and postfix in the form
   * array(prefix, postfix).
   *
   * @param arary negative pattern. 
   */
  public function setNegativePattern($pattern)
  {
    $this->pattern['negPref'] = $pattern[0];
    $this->pattern['negPost'] = $pattern[1];
  }

  /**
   * Gets the format pattern for positive values.
   * The positive pattern is composed of a prefix, and postfix.
   * This function returns array(prefix, postfix).
   *
   * @return arary positive pattern. 
   */
  public function getPositivePattern()
  {
    $prefix = $this->pattern['posPref'];
    $postfix = $this->pattern['posPost'];

    return array($prefix, $postfix);
  }

  /**
   * Sets the format pattern for positive values.
   * The positive pattern is composed of a prefix, and postfix in the form
   * array(prefix, postfix).
   *
   * @param arary positive pattern. 
   */
  public function setPositivePattern($pattern)
  {
    $this->pattern['posPref'] = $pattern[0];
    $this->pattern['posPost'] = $pattern[1];
  }

  /**
   * Gets the string to use as the currency symbol.
   *
   * @return string currency symbol. 
   */
  public function getCurrencySymbol($currency = 'USD')
  {
    if(isset($this->pattern['symbol']))
    {
      return $this->pattern['symbol'];
    }
    else
    {
      return $this->data['currencies'][$currency][0];
    }
  }

  /**
   * Sets the string to use as the currency symbol.
   *
   * @param string currency symbol.
   */
  public function setCurrencySymbol($symbol)
  {
    $this->pattern['symbol'] = $symbol;
  }

  /**
   * Gets the string that represents negative infinity.
   *
   * @return string negative infinity.
   */
  public function getNegativeInfinitySymbol()
  {
    return $this->pattern['negInfty'];
  }

  /**
   * Sets the string that represents negative infinity.
   *
   * @param string negative infinity. 
   */
  public function setNegativeInfinitySymbol($value)
  {
    $this->pattern['negInfty'] = $value;
  }

  /**
   * Gets the string that represents positive infinity.
   *
   * @return string positive infinity. 
   */
  public function getPositiveInfinitySymbol()
  {
    return $this->pattern['posInfty'];
  }

  /**
   * Sets the string that represents positive infinity.
   *
   * @param string positive infinity. 
   */
  public function setPositiveInfinitySymbol($value)
  {
    $this->pattern['posInfty'] = $value;
  }

  /**
   * Gets the string that denotes that the associated number is negative.
   *
   * @return string negative sign. 
   */
  public function getNegativeSign()
  {
    return $this->data['numberElements'][6];
  }

  /**
   * Sets the string that denotes that the associated number is negative.
   *
   * @param string negative sign. 
   */
  public function setNegativeSign($value)
  {
    $this->data['numberElements'][6] = $value;
  }

  /**
   * Gets the string that denotes that the associated number is positive.
   *
   * @return string positive sign. 
   */
  public function getPositiveSign()
  {
    return $this->data['numberElements'][11];
  }

  /**
   * Sets the string that denotes that the associated number is positive.
   *
   * @param string positive sign. 
   */
  public function setPositiveSign($value)
  {
    $this->data['numberElements'][11] = $value;
  }

  /**
   * Gets the string that represents the IEEE NaN (not a number) value.
   *
   * @return string NaN symbol.
   */
  public function getNaNSymbol()
  {
    return $this->data['numberElements'][10];
  }

  /**
   * Sets the string that represents the IEEE NaN (not a number) value.
   *
   * @param string NaN symbol.
   */
  public function setNaNSymbol($value)
  {
    $this->data['numberElements'][10] = $value;
  }

  /**
   * Gets the string to use as the percent symbol.
   *
   * @return string percent symbol.
   */
  public function getPercentSymbol()
  {
    return $this->data['numberElements'][3];
  }

  /**
   * Sets the string to use as the percent symbol.
   *
   * @param string percent symbol.
   */
  public function setPercentSymbol($value)
  {
    $this->data['numberElements'][3] = $value;
  }

  /**
   * Gets the string to use as the per mille symbol.
   *
   * @return string percent symbol.
   */
  public function getPerMilleSymbol()
  {
    return $this->data['numberElements'][8];
  }

  /**
   * Sets the string to use as the per mille symbol.
   *
   * @param string percent symbol.
   */
  public function setPerMilleSymbol($value)
  {
    $this->data['numberElements'][8] = $value;
  }

}
