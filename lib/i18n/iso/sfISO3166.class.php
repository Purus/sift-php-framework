<?php
/*
 * This file is part of the Sift PHP framework.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * ISO 3166 country codes
 *
 * @package Sift
 * @subpackage i18n
 * @link http://en.wikipedia.org/wiki/ISO_3166
 */
class sfISO3166 {

  /**
   * Array of valid countries
   *
   * @array
   */
  protected static $countries = array(
    'AD', // Andorra
    'AE', // United Arab Emirates
    'AF', // Afghanistan
    'AG', // Antigua And Barbuda
    'AI', // Anguilla
    'AL', // Albania
    'AM', // Armenia
    'AO', // Angola
    'AQ', // Antarctica
    'AR', // Argentina
    'AS', // American Samoa
    'AT', // Austria
    'AU', // Australia
    'AW', // Aruba
    'AX', // Åland Islands
    'AZ', // Azerbaijan
    'BA', // Bosnia And Herzegovina
    'BB', // Barbados
    'BD', // Bangladesh
    'BE', // Belgium
    'BF', // Burkina Faso
    'BG', // Bulgaria
    'BH', // Bahrain
    'BI', // Burundi
    'BJ', // Benin
    'BL', // Saint Barthélemy
    'BM', // Bermuda
    'BN', // Brunei Darussalam
    'BO', // Bolivia, Plurinational State Of
    'BQ', // Bonaire, Sint Eustatius And Saba
    'BR', // Brazil
    'BS', // Bahamas
    'BT', // Bhutan
    'BV', // Bouvet Island
    'BW', // Botswana
    'BY', // Belarus
    'BZ', // Belize
    'CA', // Canada
    'CC', // Cocos (Keeling) Islands
    'CD', // Congo, The Democratic Republic Of The
    'CF', // Central African Republic
    'CG', // Congo
    'CH', // Switzerland
    'CI', // Côte D'ivoire
    'CK', // Cook Islands
    'CL', // Chile
    'CM', // Cameroon
    'CN', // China
    'CO', // Colombia
    'CR', // Costa Rica
    'CU', // Cuba
    'CV', // Cape Verde
    'CW', // Curaçao
    'CX', // Christmas Island
    'CY', // Cyprus
    'CZ', // Czech Republic
    'DE', // Germany
    'DJ', // Djibouti
    'DK', // Denmark
    'DM', // Dominica
    'DO', // Dominican Republic
    'DZ', // Algeria
    'EC', // Ecuador
    'EE', // Estonia
    'EG', // Egypt
    'EH', // Western Sahara
    'ER', // Eritrea
    'ES', // Spain
    'ET', // Ethiopia
    'FI', // Finland
    'FJ', // Fiji
    'FK', // Falkland Islands (Malvinas)
    'FM', // Micronesia, Federated States Of
    'FO', // Faroe Islands
    'FR', // France
    'GA', // Gabon
    'GB', // United Kingdom
    'GD', // Grenada
    'GE', // Georgia
    'GF', // French Guiana
    'GG', // Guernsey
    'GH', // Ghana
    'GI', // Gibraltar
    'GL', // Greenland
    'GM', // Gambia
    'GN', // Guinea
    'GP', // Guadeloupe
    'GQ', // Equatorial Guinea
    'GR', // Greece
    'GS', // South Georgia And The South Sandwich Islands
    'GT', // Guatemala
    'GU', // Guam
    'GW', // Guinea-Bissau
    'GY', // Guyana
    'HK', // Hong Kong
    'HM', // Heard Island And Mcdonald Islands
    'HN', // Honduras
    'HR', // Croatia
    'HT', // Haiti
    'HU', // Hungary
    'ID', // Indonesia
    'IE', // Ireland
    'IL', // Israel
    'IM', // Isle Of Man
    'IN', // India
    'IO', // British Indian Ocean Territory
    'IQ', // Iraq
    'IR', // Iran, Islamic Republic Of
    'IS', // Iceland
    'IT', // Italy
    'JE', // Jersey
    'JM', // Jamaica
    'JO', // Jordan
    'JP', // Japan
    'KE', // Kenya
    'KG', // Kyrgyzstan
    'KH', // Cambodia
    'KI', // Kiribati
    'KM', // Comoros
    'KN', // Saint Kitts And Nevis
    'KP', // Korea, Democratic People's Republic Of
    'KR', // Korea, Republic Of
    'KW', // Kuwait
    'KY', // Cayman Islands
    'KZ', // Kazakhstan
    'LA', // Lao People's Democratic Republic
    'LB', // Lebanon
    'LC', // Saint Lucia
    'LI', // Liechtenstein
    'LK', // Sri Lanka
    'LR', // Liberia
    'LS', // Lesotho
    'LT', // Lithuania
    'LU', // Luxembourg
    'LV', // Latvia
    'LY', // Libya
    'MA', // Morocco
    'MC', // Monaco
    'MD', // Moldova, Republic Of
    'ME', // Montenegro
    'MF', // Saint Martin (French Part)
    'MG', // Madagascar
    'MH', // Marshall Islands
    'MK', // Macedonia, The Former Yugoslav Republic Of
    'ML', // Mali
    'MM', // Myanmar
    'MN', // Mongolia
    'MO', // Macao
    'MP', // Northern Mariana Islands
    'MQ', // Martinique
    'MR', // Mauritania
    'MS', // Montserrat
    'MT', // Malta
    'MU', // Mauritius
    'MV', // Maldives
    'MW', // Malawi
    'MX', // Mexico
    'MY', // Malaysia
    'MZ', // Mozambique
    'NA', // Namibia
    'NC', // New Caledonia
    'NE', // Niger
    'NF', // Norfolk Island
    'NG', // Nigeria
    'NI', // Nicaragua
    'NL', // Netherlands
    'NO', // Norway
    'NP', // Nepal
    'NR', // Nauru
    'NU', // Niue
    'NZ', // New Zealand
    'OM', // Oman
    'PA', // Panama
    'PE', // Peru
    'PF', // French Polynesia
    'PG', // Papua New Guinea
    'PH', // Philippines
    'PK', // Pakistan
    'PL', // Poland
    'PM', // Saint Pierre And Miquelon
    'PN', // Pitcairn
    'PR', // Puerto Rico
    'PS', // Palestine, State Of
    'PT', // Portugal
    'PW', // Palau
    'PY', // Paraguay
    'QA', // Qatar
    'RE', // Réunion
    'RO', // Romania
    'RS', // Serbia
    'RU', // Russian Federation
    'RW', // Rwanda
    'SA', // Saudi Arabia
    'SB', // Solomon Islands
    'SC', // Seychelles
    'SD', // Sudan
    'SE', // Sweden
    'SG', // Singapore
    'SH', // Saint Helena, Ascension And Tristan Da Cunha
    'SI', // Slovenia
    'SJ', // Svalbard And Jan Mayen
    'SK', // Slovakia
    'SL', // Sierra Leone
    'SM', // San Marino
    'SN', // Senegal
    'SO', // Somalia
    'SR', // Suriname
    'SS', // South Sudan
    'ST', // Sao Tome And Principe
    'SV', // El Salvador
    'SX', // Sint Maarten (Dutch Part)
    'SY', // Syrian Arab Republic
    'SZ', // Swaziland
    'TC', // Turks And Caicos Islands
    'TD', // Chad
    'TF', // French Southern Territories
    'TG', // Togo
    'TH', // Thailand
    'TJ', // Tajikistan
    'TK', // Tokelau
    'TL', // Timor-Leste
    'TM', // Turkmenistan
    'TN', // Tunisia
    'TO', // Tonga
    'TR', // Turkey
    'TT', // Trinidad And Tobago
    'TV', // Tuvalu
    'TW', // Taiwan, Province Of China
    'TZ', // Tanzania, United Republic Of
    'UA', // Ukraine
    'UG', // Uganda
    'UM', // United States Minor Outlying Islands
    'US', // United States
    'UY', // Uruguay
    'UZ', // Uzbekistan
    'VA', // Holy See (Vatican City State)
    'VC', // Saint Vincent And The Grenadines
    'VE', // Venezuela, Bolivarian Republic Of
    'VG', // Virgin Islands, British
    'VI', // Virgin Islands, U.s.
    'VN', // Viet Nam
    'VU', // Vanuatu
    'WF', // Wallis And Futuna
    'WS', // Samoa
    'YE', // Yemen
    'YT', // Mayotte
    'ZA', // South Africa
    'ZM', // Zambia
    'ZW', // Zimbabwe
  );

  /**
   * Countries which are in EU
   *
   * @var array
   */
  protected static $euCountries = array(
    'AT', 'BE', 'BG', 'CY', 'CZ', 'DE', 'DK', 'EE', 'ES', 'FI', 'FR',
    'GB', 'GR', 'HU', 'IE', 'IT', 'LT', 'LU', 'LV', 'MT', 'NL', 'PL',
    'PT', 'RO', 'SE', 'SI', 'SK'
  );

  /**
   * Validate the country code.
   *
   * @param string $currency The 2 letter country code
   * @return boolean
   */
  public static function isValidCode($code)
  {
    return in_array($code, self::$countries);
  }

  /**
   * Return array of all countries
   *
   * @return array Array of all country codes
   */
  public static function getCountryCodes()
  {
    return self::$countries;
  }

  /**
   * Return an array of EU countries
   *
   * @return array
   */
  public static function getEuropeanUnionCountries()
  {
    return self::$euCountries;
  }

  /**
   * Is given country in EU?
   *
   * @param string $country
   * @return boolean
   */
  public static function isInEuropeanUnion($country)
  {
    return in_array($country, self::getEuropeanUnionCountries());
  }

}
