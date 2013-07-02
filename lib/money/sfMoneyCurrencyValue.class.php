<?php
/*
 * This file is part of the Sift PHP framework.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * sfMoneyCurrencyValue represents a money value in a currency
 *
 * @package Sift
 * @subpackage money
 * @link http://codereview.stackexchange.com/questions/19338/php-currency-conversion-class-with-caching
 */
class sfMoneyCurrencyValue implements sfIMoneyCurrencyValue {

  /**
   * Precision used in the calculations
   *
   * @var integer
   */
  public static $calculationPrecision = 10;

  /**
   * @var float
   */
  protected $amount;

  /**
   * @var string
   */
  protected $currency;

  /**
   * Constructs the value
   *
   * @param string|float $amount
   * @param string $currency
   * @throws InvalidArgumentException If currency is invalid
   */
  public function __construct($amount, $currency)
  {
    if(!$currency instanceof sfMoneyCurrency)
    {
      $currency = sfMoneyCurrency::getInstance($currency);
    }

    $this->amount = $amount;
    $this->currency = $currency;
  }

  /**
   * Are the values in the same currency?
   *
   * @param sfMoneyCurrencyValue $other Other value
   * @throws InvalidArgumentException If the values are not in the same currency
   */
  protected function assertSameCurrency(sfIMoneyCurrencyValue $other)
  {
    if(!$this->isInSameCurrency($other))
    {
      throw new InvalidArgumentException(sprintf('Can only operate with same currencies. Crrency is "%s", given "%s".',
          (string)$this->currency, (string) $other->getCurrency()));
    }
  }

  /**
   * Adds amount. Must be in the same currency
   *
   * @param sfIMoneyCurrencyValue $money Amount to add
   * @return sfMoneyCurrencyValue
   * @throws InvalidArgumentException If the values are not in the same currency
   */
  public function add(sfIMoneyCurrencyValue $money)
  {
    $this->assertSameCurrency($money);

    return new sfMoneyCurrencyValue(
      sfMath::add($this->amount, $money->getAmount(), self::$calculationPrecision),
      $this->getCurrency()
    );
  }

  /**
   * Subtracts the amount
   *
   * @param sfIMoneyCurrencyValue $money
   * @return sfMoneyCurrencyValue
   * @throws InvalidArgumentException If the values are not in the same currency
   */
  public function subtract(sfIMoneyCurrencyValue $money)
  {
    $this->assertSameCurrency($money);

    return new sfMoneyCurrencyValue(
      sfMath::substract($this->amount, $money->getAmount(), self::$calculationPrecision),
      $this->getCurrency()
    );
  }

  /**
   * Multiply the amount with given number
   *
   * @param string $multiplier
   * @return sfMoneyCurrencyValue
   */
  public function multiply($multiplier)
  {
    return new sfMoneyCurrencyValue(
      sfMath::multiply($this->amount, $multiplier, self::$calculationPrecision),
      $this->getCurrency()
    );
  }

  /**
   * Raise the amount to the power of $exponent
   *
   * @param string $exponent
   * @return sfMoneyCurrencyValue
   */
  public function power($exponent)
  {
    return new sfMoneyCurrencyValue(
      sfMath::power($this->amount, $exponent, self::$calculationPrecision),
      $this->getCurrency()
    );
  }

  /**
   * Divides the amount with given number
   *
   * @param string $divider
   * @return sfMoneyCurrencyValue
   */
  public function divide($divider)
  {
    return new sfMoneyCurrencyValue(
      sfMath::divide($this->amount, $divider, self::$calculationPrecision),
      $this->getCurrency()
    );
  }

  /**
   * Returns the amount
   *
   * @param integer $scale Scale of the amount?
   * @param string $roundingMode Mode of rounding
   * @return string
   */
  public function getAmount($scale = null, $roundingMode = sfRounding::HALF_EVEN)
  {
    return sfMath::clean(is_null($scale) ? $this->amount : sfRounding::round($this->amount, $scale, $roundingMode));
  }

  /**
   * Is the amount zero?
   *
   * @return boolean
   */
  public function isZero()
  {
    return $this->amount == 0;
  }

  /**
   * Compares with another value
   *
   * @param sfIMoneyCurrencyValue $other
   * @throws InvalidArgumentException If the values are not in the same currency
   * @return int
   */
  public function compare(sfIMoneyCurrencyValue $other)
  {
    $this->assertSameCurrency($other);

    if($this->getAmount() < $other->getAmount())
    {
      return -1;
    }
    elseif($this->getAmount() == $other->getAmount())
    {
      return 0;
    }
    else
    {
      return 1;
    }
  }

  /**
   * Is the value equal to $other value?
   *
   * @param sfIMoneyCurrencyValue $other
   * @return bool
   */
  public function isEqual(sfIMoneyCurrencyValue $other)
  {
    return $this->isInSameCurrency($other) && 0 == $this->compare($other);
  }

  /**
   * Is the value more than $other value?
   *
   * @param sfIMoneyCurrencyValue $other
   * @return bool
   */
  public function isMoreThan(sfIMoneyCurrencyValue $other)
  {
    return 1 == $this->compare($other);
  }

  /**
   * Is the value more or equal than $other value?
   *
   * @param sfIMoneyCurrencyValue $other
   * @return bool
   */
  public function isMoreThanOrEqual(sfIMoneyCurrencyValue $other)
  {
    return $this->isMoreThan($other) || $this->isEqual($other);
  }

  /**
   * Is the value lower than $other value?
   *
   * @param sfIMoneyCurrencyValue $other
   * @return bool
   */
  public function isLessThan(sfIMoneyCurrencyValue $other)
  {
    return -1 == $this->compare($other);
  }

  /**
   * Is the value lower or equal than $other value?
   *
   * @param sfIMoneyCurrencyValue $other
   * @return bool
   */
  public function isLessThanOrEqual(sfIMoneyCurrencyValue $other)
  {
    return $this->isLessThan($other) || $this->isEqual($other);
  }

  /**
   * Is the value greater than zero?
   *
   * @return boolean
   */
  public function isPositive()
  {
    return $this->amount > 0;
  }

  /**
   * Is the value lower than zero?
   *
   * @return boolean
   */
  public function isNegative()
  {
    return $this->amount < 0;
  }

  /**
   * Return the currency
   *
   * @return string
   */
  public function getCurrency()
  {
    return $this->currency;
  }

  /**
   * Is the value in same currency?
   *
   * @param sfIMoneyCurrencyValue $value The other value
   * @return boolean
   */
  public function isInSameCurrency(sfIMoneyCurrencyValue $value)
  {
    return $this->currency->equals($value->getCurrency());
  }

  /**
   * Converts the object to string, so the calculations can be performed with it
   *
   * @return string
   */
  public function __toString()
  {
    return (string)$this->getAmount();
  }

  /**
   * Formats the value for given culture
   *
   * @param string $format Format
   * @param string $culture
   * @param integer $scale Precision
   * @param string $roundingMode Rounding mode
   * @return string
   */
  public function format($format = 'c', $culture = null, $scale = null, $roundingMode = sfRounding::HALF_EVEN)
  {
    return sfI18nNumberFormatter::getInstance($culture ? $culture : sfConfig::get('sf_culture'))
              ->format($this->getAmount($scale, $roundingMode), $format, (string)$this->getCurrency());
  }

}
