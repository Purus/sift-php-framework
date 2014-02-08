<?php
/*
 * This file is part of the Sift PHP framework.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * sfIMoneyTaxCalculator interface
 *
 * @package Sift
 * @subpackage money
 */
interface sfIMoneyTaxCalculator
{
  public function getTaxAmount(sfIMoneyCurrencyValue $priceWithTax, $tax, $scale = null, $roundingMode = sfRounding::HALF_EVEN);
  public function getPriceWithTax(sfIMoneyCurrencyValue $price, $tax, $scale = null, $roundingMode = sfRounding::HALF_EVEN);

}
