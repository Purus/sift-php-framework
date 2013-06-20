<?php
/*
 * This file is part of the Sift PHP framework.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * sfMoneyCurrency
 *
 * @package Sift
 * @subpackage money
 * @link http://verraes.net/2011/04/fowler-money-pattern-in-php/
 */
class sfMoneyCurrency {

  /**
   * Instances holder
   *
   * @var array
   */
  protected static $instances = array();

  /**
   * Name
   *
   * @var string
   */
  protected $name;

  /**
   * Scale
   *
   * @var integer
   */
  public static $scale = 2;

  /**
   * Array of valid currencies
   *
   * @var array
   */
  public static $validCurrencies = array(
    'AED', 'AFN', 'ALL', 'AMD', 'ANG', 'AOA', 'ARS', 'AUD', 'AWG', 'AZN', 'BAM', 'BBD', 'BDT', 'BGN', 'BHD', 'BIF',
    'BMD', 'BND', 'BOB', 'BRL', 'BSD', 'BTN', 'BWP', 'BYR', 'BZD', 'CAD', 'CDF', 'CHF', 'CLF', 'CLP', 'CNY', 'COP',
    'CRC', 'CUP', 'CVE', 'CZK', 'DJF', 'DKK', 'DOP', 'DZD', 'EEK', 'EGP', 'ETB', 'EUR', 'FJD', 'FKP', 'GBP', 'GEL',
    'GHS', 'GIP', 'GMD', 'GNF', 'GTQ', 'GYD', 'HKD', 'HNL', 'HRK', 'HTG', 'HUF', 'IDR', 'ILS', 'INR', 'IQD', 'IRR',
    'ISK', 'JEP', 'JMD', 'JOD', 'JPY', 'KES', 'KGS', 'KHR', 'KMF', 'KPW', 'KRW', 'KWD', 'KYD', 'KZT', 'LAK', 'LBP',
    'LKR', 'LRD', 'LSL', 'LTL', 'LVL', 'LYD', 'MAD', 'MDL', 'MGA', 'MKD', 'MMK', 'MNT', 'MOP', 'MRO', 'MUR', 'MVR',
    'MWK', 'MXN', 'MYR', 'MZN', 'NAD', 'NGN', 'NIO', 'NOK', 'NPR', 'NZD', 'OMR', 'PAB', 'PEN', 'PGK', 'PHP', 'PKR',
    'PLN', 'PYG', 'QAR', 'RON', 'RSD', 'RUB', 'RWF', 'SAR', 'SBD', 'SCR', 'SDG', 'SEK', 'SGD', 'SHP', 'SLL', 'SOS',
    'SRD', 'STD', 'SVC', 'SYP', 'SZL', 'THB', 'TJS', 'TMT', 'TND', 'TOP', 'TRY', 'TTD', 'TWD', 'TZS', 'UAH', 'UGX',
    'USD', 'UYU', 'UZS', 'VEF', 'VND', 'VUV', 'WST', 'XAF', 'XCD', 'XDR', 'XOF', 'XPF', 'YER', 'ZAR', 'ZMK', 'ZWL'
  );

  /**
   * Constructs the currency
   *
   * @param string $name
   * @throws InvalidArgumentException
   */
  public function __construct($name)
  {
    $name = strtoupper($name);

    var_dump($name);

    if(!in_array($name, self::$validCurrencies))
    {
      throw new InvalidArgumentException(sprintf('Invalid currency "%s" given.', $name));
    }

    $this->name = $name;
  }

  /**
   * Creates new currency
   *
   * @param string $name Name of the currency
   * @param integer $scale Scale
   * @return sfMoneyCurrency
   */
  public static function create($name)
  {
    if(class_exists($class = sprintf('sfMoneyCurrency%s', $name)))
    {
      return new $class();
    }

    return new self($name);
  }

  /**
   * Returns an instance of the currency
   *
   * @param string $name
   * @param integer $scale
   * @return sfMoneyCurrency
   */
  public static function getInstance($name, $scale = null)
  {
    $key = $name . $scale;
    if(!isset(self::$instances[$key]))
    {
      self::$instances[$key] = self::create($name, $scale);
    }
    return self::$instances[$key];
  }

  /**
   * Returns currency name
   *
   * @return string
   */
  public function getName()
  {
    return $this->name;
  }

  /**
   * Returns scale
   *
   * @return string
   */
  public function getScale()
  {
    return self::$scale;
  }

  /**
   * To string
   *
   * @return string
   */
  public function toString()
  {
    return $this->name;
  }

  /**
   * To string magic method
   *
   * @return string
   */
  public function __toString()
  {
    return $this->name;
  }

  /**
   * Is the $other currency the same?
   *
   * @param sfCurrency $other
   * @return boolean
   */
  public function equals(sfMoneyCurrency $other)
  {
    return $this->getName() === $other->getName();
  }

}
