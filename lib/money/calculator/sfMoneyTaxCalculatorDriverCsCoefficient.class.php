<?php
/*
 * This file is part of the Sift PHP framework.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * sfMoneyTaxCalculatorCsCoefficient calculates the taxes based on the law of Czech republic using coefficients.
 *
 * @package Sift
 * @subpackage money
 * @link http://www.zakonyprolidi.cz/cs/2004-235#p37-2
 * @link http://www.uctovani.net/vypocet-zakladu-dane-z-pridane-hodnoty.php
 */
class sfMoneyTaxCalculatorDriverCsCoefficient extends sfMoneyTaxCalculator {

  /**
   * Array of known coefficients
   *
   * @var array
   */
  protected $coefficients = array(
    15 => '0.1304',
    21 => '0.1736'
  );

  /**
   * Returns the tax amount from the given $priceWithTax and $tax
   *
   * @param string $priceWithTax The price with tax
   * @param string $tax The tax percentage
   */
  public function getTaxAmount(sfIMoneyCurrencyValue $priceWithTax, $tax, $scale = null, $roundingMode = sfRounding::NONE)
  {
    if(isset($this->coefficients[$tax]))
    {
      $coefficient = $this->coefficients[$tax];
    }
    else
    {
      $coefficient = sfMath::round(sfMath::divide($tax, sfMath::add('100', $tax), 100), 4);
    }

    $amount = sfRounding::round(sfMath::clean(
      sfMath::multiply($priceWithTax->getAmount(), $coefficient, 10)
    ), !is_null($scale) ? $scale : $priceWithTax->getCurrency()->getScale(), $roundingMode);

    return new sfMoneyCurrencyValue($amount, $priceWithTax->getCurrency());
  }

}
