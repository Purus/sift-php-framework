<?php
/*
 * This file is part of the Sift PHP framework.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * ISO 4217 Currency Codes - list of global currencies and the three-character currency codes.
 *
 * @package Sift
 * @subpackage i18n
 * @link http://en.wikipedia.org/wiki/ISO_4217
 */
class sfISO4217 {

  /**
   * Array of existing currencies
   *
   * @var array
   */
  public static $currencies = array(
    'AFA', // Afghan Afghani
    'AWG', // Aruban Florin
    'AUD', // Australian Dollars
    'ARS', // Argentine Pes
    'AZN', // Azerbaijanian Manat
    'BSD', // Bahamian Dollar
    'BDT', // Bangladeshi Taka
    'BBD', // Barbados Dollar
    'BYR', // Belarussian Rouble
    'BOB', // Bolivian Boliviano
    'BRL', // Brazilian Real
    'GBP', // British Pounds Sterling
    'BGN', // Bulgarian Lev
    'KHR', // Cambodia Riel
    'CAD', // Canadian Dollars
    'KYD', // Cayman Islands Dollar
    'CLP', // Chilean Peso
    'CNY', // Chinese Renminbi Yuan
    'COP', // Colombian Peso
    'CRC', // Costa Rican Colon
    'HRK', // Croatia Kuna
    'CPY', // Cypriot Pounds
    'CZK', // Czech Koruna
    'DKK', // Danish Krone
    'DOP', // Dominican Republic Peso
    'XCD', // East Caribbean Dollar
    'EGP', // Egyptian Pound
    'ERN', // Eritrean Nakfa
    'EEK', // Estonia Kroon
    'EUR', // Euro
    'GEL', // Georgian Lari
    'GHC', // Ghana Cedi
    'GIP', // Gibraltar Pound
    'GTQ', // Guatemala Quetzal
    'HNL', // Honduras Lempira
    'HKD', // Hong Kong Dollars
    'HUF', // Hungary Forint
    'ISK', // Icelandic Krona
    'INR', // Indian Rupee
    'IDR', // Indonesia Rupiah
    'ILS', // Israel Shekel
    'JMD', // Jamaican Dollar
    'JPY', // Japanese yen
    'KZT', // Kazakhstan Tenge
    'KES', // Kenyan Shilling
    'KWD', // Kuwaiti Dinar
    'LVL', // Latvia Lat
    'LBP', // Lebanese Pound
    'LTL', // Lithuania Litas
    'MOP', // Macau Pataca
    'MKD', // Macedonian Denar
    'MGA', // Malagascy Ariary
    'MYR', // Malaysian Ringgit
    'MTL', // Maltese Lira
    'BAM', // Marka
    'MUR', // Mauritius Rupee
    'MXN', // Mexican Pesos
    'MZM', // Mozambique Metical
    'NPR', // Nepalese Rupee
    'ANG', // Netherlands Antilles Guilder
    'TWD', // New Taiwanese Dollars
    'NZD', // New Zealand Dollars
    'NIO', // Nicaragua Cordoba
    'NGN', // Nigeria Naira
    'KPW', // North Korean Won
    'NOK', // Norwegian Krone
    'OMR', // Omani Riyal
    'PKR', // Pakistani Rupee
    'PYG', // Paraguay Guarani
    'PEN', // Peru New Sol
    'PHP', // Philippine Pesos
    'QAR', // Qatari Riyal
    'RON', // Romanian New Leu
    'RUB', // Russian Federation Ruble
    'SAR', // Saudi Riyal
    'CSD', // Serbian Dinar
    'SCR', // Seychelles Rupee
    'SGD', // Singapore Dollars
    'SKK', // Slovak Koruna
    'SIT', // Slovenia Tolar
    'ZAR', // South African Rand
    'KRW', // South Korean Won
    'LKR', // Sri Lankan Rupee
    'SRD', // Surinam Dollar
    'SEK', // Swedish Krona
    'CHF', // Swiss Francs
    'TZS', // Tanzanian Shilling
    'THB', // Thai Baht
    'TTD', // Trinidad and Tobago Dollar
    'TRY', // Turkish New Lira
    'AED', // UAE Dirham
    'USD', // US Dollars
    'UGX', // Ugandian Shilling
    'UAH', // Ukraine Hryvna
    'UYU', // Uruguayan Peso
    'UZS', // Uzbekistani Som
    'VEB', // Venezuela Bolivar
    'VND', // Vietnam Dong
    'AMK', // Zambian Kwacha
    'ZWD', // Zimbabwe Dollar
  );

  /**
   * Validate the currency code.
   *
   * @param string $currency The 3 letter currency code
   * @return boolean
   */
  public static function isValidCode($currency)
  {
    return in_array($currency, self::$currencies);
  }

  /**
   * Return array of all currencies
   *
   * @return array Array of all currency codes
   */
  public static function getCurrencyCodes()
  {
    return self::$currencies;
  }

}
