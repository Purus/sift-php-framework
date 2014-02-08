<?php
/*
 * This file is part of the Sift PHP framework.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * sfMoneyCurrencyConverter converts monetary values between currencies
 *
 * @package Sift
 * @subpackage money
 */
class sfMoneyCurrencyConverter implements sfIMoneyCurrencyValue {

  /**
   * @var sfIMoneyCurrency
   */
  protected $money;

  /**
   * @var string
   */
  protected $conversionRate;

  /**
   * @var string
   */
  protected $sourceCurrency;

  /**
   * @var string
   */
  protected $targetCurrency;

  /**
   * Constructs the converter
   *
   * @param float $conversionRate The conversion rate between source currency and target currency
   * @param string $sourceCurrency
   * @param string $targetCurrency
   */
  public function __construct($conversionRate, $sourceCurrency, $targetCurrency)
  {
    $conversionRate = (string)$conversionRate;

    // we have an expression like: 1/57.25
    if(strpos($conversionRate, '/') !== false)
    {
      $parts = explode('/', $conversionRate);

      if(count($parts) !== 2)
      {
        throw new InvalidArgumentException(sprintf('Invalid conversion rate "%s" given.', $conversionRate));
      }

      list($a, $b) = $parts;

      if($b == 0)
      {
        throw new InvalidArgumentException(sprintf('Invalid conversion rate "%s". The conversion rate divides by zero.', $conversionRate));
      }

      $conversionRate = sfMath::divide($a, $b, 100);
    }

    if(empty($conversionRate))
    {
      throw new InvalidArgumentException(sprintf('Invalid conversion rate "%s" given.', $conversionRate));
    }

    $this->conversionRate = $conversionRate;
    $this->sourceCurrency = is_string($sourceCurrency) ? sfMoneyCurrency::getInstance($sourceCurrency) : $sourceCurrency;
    $this->targetCurrency = is_string($targetCurrency) ? sfMoneyCurrency::getInstance($targetCurrency) : $targetCurrency;
  }

  /**
   * Creates the converter from ISO string representation like: EUR/USD 1.2500
   *
   * @param  string $iso String representation of the form "EUR/USD 1.2500"
   * @throws InvalidArgumentException
   * @return sfMoneyCurrencyConverter
   */
  public static function createFromIso($iso)
  {
    $currency = '([A-Z]{2,3})';
    $ratio = '((\d+/[0-9]*\.?[0-9]+)|([0-9]*\.?[0-9]+))'; // @see http://www.regular-expressions.info/floatingpoint.html
    $pattern = sprintf('#%s/%s %s#', $currency, $currency, $ratio);

    $matches = array();

    if(!preg_match($pattern, $iso, $matches))
    {
      throw new InvalidArgumentException(sprintf('Error parsing the ISO string "%s".', $iso));
    }

    $conversionRate = $matches[3];

    return new sfMoneyCurrencyConverter($conversionRate, $matches[1], $matches[2]);
  }

  /**
   * Converts the money to different currency
   *
   * @param sfIMoneyCurrencyValue $money
   * @param float $conversionRate The conversion rate
   * @param string $sourceCurrency Source currency
   * @param string $targetCurrency Target currency
   * @return string The converted value
   */
  public static function convert(sfIMoneyCurrencyValue $money, $conversionRate, $sourceCurrency, $targetCurrency)
  {
    $c = new sfMoneyCurrencyConverter($conversionRate, $sourceCurrency, $targetCurrency);

    return $c->setMoney($money)->getAmount();
  }

  /**
   * Sets a money
   *
   * @param sfIMoneyCurrency $money
   * @throws InvalidArgumentException
   * @return sfMoneyCurrencyConverter
   */
  public function setMoney(sfIMoneyCurrencyValue $money)
  {
    if(!$this->isInSameCurrency($money))
    {
      throw new InvalidArgumentException(
        sprintf('The money value "%s" is in an incorrect currency "%s" for this converter. Expected currency: "%s".',
                $money->getAmount(),
                (string)$this->getSourceCurrency(),
                (string)$money->getCurrency())
      );
    }

    $this->money = $money;

    return $this;
  }

  /**
   * Returns an amount
   *
   * @param integer $scale Scale of the amount
   * @param string $roundingMode Rounding mode
   * @return float
   */
  public function getAmount($scale = null, $roundingMode = sfRounding::HALF_EVEN)
  {
    $result = sfMath::multiply($this->money->getAmount(), $this->conversionRate, sfMoneyCurrencyValue::$calculationPrecision);

    if(!is_null($scale))
    {
      return sfRounding::round($result, $scale, $roundingMode);
    }

    return $result;
  }

  /**
   * Returns target currency
   *
   * @return string
   */
  public function getCurrency()
  {
    return $this->targetCurrency;
  }

  /**
   * Returns target currency
   *
   * @return string
   */
  public function getTargetCurrency()
  {
    return $this->targetCurrency;
  }

  /**
   * Returns source currency
   *
   * @return string
   */
  public function getSourceCurrency()
  {
    return $this->sourceCurrency;
  }

  /**
   * Is the value in same currency?
   *
   * @param sfIMoneyCurrencyValue $value The other value
   * @return boolean
   */
  public function isInSameCurrency(sfIMoneyCurrencyValue $value)
  {
    return $this->sourceCurrency->equals($value->getCurrency());
  }

}
