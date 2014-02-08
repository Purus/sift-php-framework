<?php
/*
 * This file is part of the Sift PHP framework.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * NumberHelper.
 *
 * @package    Sift
 * @subpackage helper
 */

/**
 * Formats given number using I18n specification
 *
 * @param integer $number
 * @param string $culture
 * @return mixed
 */
function format_number($number, $culture = null)
{
  if(is_null($number))
  {
    return null;
  }

  if(is_null($culture))
  {
    $culture = sfContext::getInstance()->getUser()->getCulture();
  }

  $numberFormat = new sfI18nNumberFormatter($culture);

  return $numberFormat->format($number);
}

/**
 * Formats given number as currency
 *
 * @param mixed $amount
 * @param string $currency
 * @param string $culture
 * @return mixed
 */
function format_currency($amount, $currency = null, $culture = null)
{
  if(is_null($amount))
  {
    return null;
  }

  if(is_null($culture))
  {
    $culture = sfContext::getInstance()->getUser()->getCulture();
  }

  $numberFormat = new sfI18nNumberFormatter($culture);
  return $numberFormat->format($amount, 'c', $currency);
}
