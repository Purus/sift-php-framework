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
 * @package    Sift
 * @subpackage i18n
 * @link       http://en.wikipedia.org/wiki/ISO_4217
 */
class sfISO4217
{
    /**
     * Array of existing currencies
     *
     * @var array
     */
    protected static $currencies
        = array(
            'AED', // Uae Dirham
            'AFN', // Afghani
            'ALL', // Lek
            'AMD', // Armenian Dram
            'ANG', // Netherlands Antillean Guilder
            'AOA', // Kwanza
            'ARS', // Argentine Peso
            'AUD', // Australian Dollar
            'AWG', // Aruban Florin
            'AZN', // Azerbaijanian Manat
            'BAM', // Convertible Mark
            'BBD', // Barbados Dollar
            'BDT', // Taka
            'BGN', // Bulgarian Lev
            'BHD', // Bahraini Dinar
            'BIF', // Burundi Franc
            'BMD', // Bermudian Dollar
            'BND', // Brunei Dollar
            'BOB', // Boliviano
            'BOV', // Mvdol
            'BRL', // Brazilian Real
            'BSD', // Bahamian Dollar
            'BTN', // Ngultrum
            'BWP', // Pula
            'BYR', // Belarussian Ruble
            'BZD', // Belize Dollar
            'CAD', // Canadian Dollar
            'CDF', // Congolese Franc
            'CHE', // Wir Euro
            'CHF', // Swiss Franc
            'CHW', // Wir Franc
            'CLF', // Unidades De Fomento
            'CLP', // Chilean Peso
            'CNY', // Yuan Renminbi
            'COP', // Colombian Peso
            'COU', // Unidad De Valor Real
            'CRC', // Costa Rican Colon
            'CUC', // Peso Convertible
            'CUP', // Cuban Peso
            'CVE', // Cape Verde Escudo
            'CZK', // Czech Koruna
            'DJF', // Djibouti Franc
            'DKK', // Danish Krone
            'DOP', // Dominican Peso
            'DZD', // Algerian Dinar
            'EGP', // Egyptian Pound
            'ERN', // Nakfa
            'ETB', // Ethiopian Birr
            'EUR', // Euro
            'FJD', // Fiji Dollar
            'FKP', // Falkland Islands Pound
            'GBP', // Pound Sterling
            'GEL', // Lari
            'GHS', // Ghana Cedi
            'GIP', // Gibraltar Pound
            'GMD', // Dalasi
            'GNF', // Guinea Franc
            'GTQ', // Quetzal
            'GYD', // Guyana Dollar
            'HKD', // Hong Kong Dollar
            'HNL', // Lempira
            'HRK', // Croatian Kuna
            'HTG', // Gourde
            'HUF', // Forint
            'IDR', // Rupiah
            'ILS', // New Israeli Sheqel
            'INR', // Indian Rupee
            'IQD', // Iraqi Dinar
            'IRR', // Iranian Rial
            'ISK', // Iceland Krona
            'JMD', // Jamaican Dollar
            'JOD', // Jordanian Dinar
            'JPY', // Yen
            'KES', // Kenyan Shilling
            'KGS', // Som
            'KHR', // Riel
            'KMF', // Comoro Franc
            'KPW', // North Korean Won
            'KRW', // Won
            'KWD', // Kuwaiti Dinar
            'KYD', // Cayman Islands Dollar
            'KZT', // Tenge
            'LAK', // Kip
            'LBP', // Lebanese Pound
            'LKR', // Sri Lanka Rupee
            'LRD', // Liberian Dollar
            'LSL', // Loti
            'LTL', // Lithuanian Litas
            'LVL', // Latvian Lats
            'LYD', // Libyan Dinar
            'MAD', // Moroccan Dirham
            'MDL', // Moldovan Leu
            'MGA', // Malagasy Ariary
            'MKD', // Denar
            'MMK', // Kyat
            'MNT', // Tugrik
            'MOP', // Pataca
            'MRO', // Ouguiya
            'MUR', // Mauritius Rupee
            'MVR', // Rufiyaa
            'MWK', // Kwacha
            'MXN', // Mexican Peso
            'MXV', // Mexican Unidad De Inversion (Udi)
            'MYR', // Malaysian Ringgit
            'MZN', // Mozambique Metical
            'NAD', // Namibia Dollar
            'NGN', // Naira
            'NIO', // Cordoba Oro
            'NOK', // Norwegian Krone
            'NPR', // Nepalese Rupee
            'NZD', // New Zealand Dollar
            'OMR', // Rial Omani
            'PAB', // Balboa
            'PEN', // Nuevo Sol
            'PGK', // Kina
            'PHP', // Philippine Peso
            'PKR', // Pakistan Rupee
            'PLN', // Zloty
            'PYG', // Guarani
            'QAR', // Qatari Rial
            'RON', // New Romanian Leu
            'RSD', // Serbian Dinar
            'RUB', // Russian Ruble
            'RWF', // Rwanda Franc
            'SAR', // Saudi Riyal
            'SBD', // Solomon Islands Dollar
            'SCR', // Seychelles Rupee
            'SDG', // Sudanese Pound
            'SEK', // Swedish Krona
            'SGD', // Singapore Dollar
            'SHP', // Saint Helena Pound
            'SLL', // Leone
            'SOS', // Somali Shilling
            'SRD', // Surinam Dollar
            'SSP', // South Sudanese Pound
            'STD', // Dobra
            'SVC', // El Salvador Colon
            'SYP', // Syrian Pound
            'SZL', // Lilangeni
            'THB', // Baht
            'TJS', // Somoni
            'TMT', // Turkmenistan New Manat
            'TND', // Tunisian Dinar
            'TOP', // Pa’anga
            'TRY', // Turkish Lira
            'TTD', // Trinidad And Tobago Dollar
            'TWD', // New Taiwan Dollar
            'TZS', // Tanzanian Shilling
            'UAH', // Hryvnia
            'UGX', // Uganda Shilling
            'USD', // Us Dollar
            'USN', // Us Dollar (Next Day)
            'USS', // Us Dollar (Same Day)
            'UYI', // Uruguay Peso En Unidades Indexadas (Uruiurui)
            'UYU', // Peso Uruguayo
            'UZS', // Uzbekistan Sum
            'VEF', // Bolivar
            'VND', // Dong
            'VUV', // Vatu
            'WST', // Tala
            'XAF', // Cfa Franc Beac
            'XAG', // Silver
            'XAU', // Gold
            'XBA', // Bond Markets Unit European Composite Unit (Eurco)
            'XBB', // Bond Markets Unit European Monetary Unit (E.m.u.-6)
            'XBC', // Bond Markets Unit European Unit Of Account 9 (E.u.a.-9)
            'XBD', // Bond Markets Unit European Unit Of Account 17 (E.u.a.-17)
            'XCD', // East Caribbean Dollar
            'XDR', // Sdr (Special Drawing Right)
            'XFU', // Uic-Franc
            'XOF', // Cfa Franc Bceao
            'XPD', // Palladium
            'XPF', // Cfp Franc
            'XPT', // Platinum
            'XSU', // Sucre
            'XTS', // Codes Specifically Reserved For Testing Purposes
            'XUA', // Adb Unit Of Account
            'XXX', // The Codes Assigned For Transactions Where No Currency Is Involved
            'YER', // Yemeni Rial
            'ZAR', // Rand
            'ZMW', // Zambian Kwacha
            'ZWL', // Zimbabwe Dollar
        );

    /**
     * Validate the currency code.
     *
     * @param string $currency The 3 letter currency code
     *
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
