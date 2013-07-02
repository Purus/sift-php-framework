<?php
/*
 * This file is part of the Sift PHP framework.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * sfIMoneyCurrencyValue inteface
 *
 * @package Sift
 * @subpackage money
 */
interface sfIMoneyCurrencyValue
{
  public function getAmount($scale = null, $roundingMode = sfRounding::HALF_EVEN);
  public function getCurrency();
  public function isInSameCurrency(sfIMoneyCurrencyValue $value);

}
