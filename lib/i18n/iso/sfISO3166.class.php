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
   * Alpha 2
   */
  const ALPHA2 = 'aplha_2';

  /**
   * Alpha 3
   */
  const ALPHA3 = 'ALPHA3';

  /**
   * Array of valid countries (alpha 2 codes)
   *
   * @array
   */
  protected static $countriesAlpha2 = array(
    'AD', // Andorra
    'AE', // United Arab Emirates
    'AF', // Afghanistan
    'AG', // Antigua And Barbuda
    'AI', // Anguilla
    'AL', // Albania
    'AM', // Armenia
    'AN', // Netherlands Antilles
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
    'LY', // Libyan Arab Jamahiriya
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
    'PS', // Palestinian Territory, Occupied
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
    'ST', // Sao Tome And Principe
    'SV', // El Salvador
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
   * Array of valid countries (alpha 3 codes)
   *
   * @array
   */
  protected static $countriesAlpha3 = array(
    'ABW', // Aruba
    'AFG', // Afghanistan
    'AGO', // Angola
    'AIA', // Anguilla
    'ALA', // Åland Islands
    'ALB', // Albania
    'AND', // Andorra
    'ANT', // Netherlands Antilles
    'ARE', // United Arab Emirates
    'ARG', // Argentina
    'ARM', // Armenia
    'ASM', // American Samoa
    'ATA', // Antarctica
    'ATF', // French Southern Territories
    'ATG', // Antigua And Barbuda
    'AUS', // Australia
    'AUT', // Austria
    'AZE', // Azerbaijan
    'BDI', // Burundi
    'BEL', // Belgium
    'BEN', // Benin
    'BFA', // Burkina Faso
    'BGD', // Bangladesh
    'BGR', // Bulgaria
    'BHR', // Bahrain
    'BHS', // Bahamas
    'BIH', // Bosnia And Herzegovina
    'BLM', // Saint Barthélemy
    'BLR', // Belarus
    'BLZ', // Belize
    'BMU', // Bermuda
    'BOL', // Bolivia, Plurinational State Of
    'BRA', // Brazil
    'BRB', // Barbados
    'BRN', // Brunei Darussalam
    'BTN', // Bhutan
    'BVT', // Bouvet Island
    'BWA', // Botswana
    'CAF', // Central African Republic
    'CAN', // Canada
    'CCK', // Cocos (Keeling) Islands
    'CHE', // Switzerland
    'CHL', // Chile
    'CHN', // China
    'CIV', // Côte D'ivoire
    'CMR', // Cameroon
    'COD', // Congo, The Democratic Republic Of The
    'COG', // Congo
    'COK', // Cook Islands
    'COL', // Colombia
    'COM', // Comoros
    'CPV', // Cape Verde
    'CRI', // Costa Rica
    'CUB', // Cuba
    'CXR', // Christmas Island
    'CYM', // Cayman Islands
    'CYP', // Cyprus
    'CZE', // Czech Republic
    'DEU', // Germany
    'DJI', // Djibouti
    'DMA', // Dominica
    'DNK', // Denmark
    'DOM', // Dominican Republic
    'DZA', // Algeria
    'ECU', // Ecuador
    'EGY', // Egypt
    'ERI', // Eritrea
    'ESH', // Western Sahara
    'ESP', // Spain
    'EST', // Estonia
    'ETH', // Ethiopia
    'FIN', // Finland
    'FJI', // Fiji
    'FLK', // Falkland Islands (Malvinas)
    'FRA', // France
    'FRO', // Faroe Islands
    'FSM', // Micronesia, Federated States Of
    'GAB', // Gabon
    'GBR', // United Kingdom
    'GEO', // Georgia
    'GGY', // Guernsey
    'GHA', // Ghana
    'GIB', // Gibraltar
    'GIN', // Guinea
    'GLP', // Guadeloupe
    'GMB', // Gambia
    'GNB', // Guinea-Bissau
    'GNQ', // Equatorial Guinea
    'GRC', // Greece
    'GRD', // Grenada
    'GRL', // Greenland
    'GTM', // Guatemala
    'GUF', // French Guiana
    'GUM', // Guam
    'GUY', // Guyana
    'HKG', // Hong Kong
    'HMD', // Heard Island And Mcdonald Islands
    'HND', // Honduras
    'HRV', // Croatia
    'HTI', // Haiti
    'HUN', // Hungary
    'IDN', // Indonesia
    'IMN', // Isle Of Man
    'IND', // India
    'IOT', // British Indian Ocean Territory
    'IRL', // Ireland
    'IRN', // Iran, Islamic Republic Of
    'IRQ', // Iraq
    'ISL', // Iceland
    'ISR', // Israel
    'ITA', // Italy
    'JAM', // Jamaica
    'JEY', // Jersey
    'JOR', // Jordan
    'JPN', // Japan
    'KAZ', // Kazakhstan
    'KEN', // Kenya
    'KGZ', // Kyrgyzstan
    'KHM', // Cambodia
    'KIR', // Kiribati
    'KNA', // Saint Kitts And Nevis
    'KOR', // Korea, Republic Of
    'KWT', // Kuwait
    'LAO', // Lao People's Democratic Republic
    'LBN', // Lebanon
    'LBR', // Liberia
    'LBY', // Libyan Arab Jamahiriya
    'LCA', // Saint Lucia
    'LIE', // Liechtenstein
    'LKA', // Sri Lanka
    'LSO', // Lesotho
    'LTU', // Lithuania
    'LUX', // Luxembourg
    'LVA', // Latvia
    'MAC', // Macao
    'MAF', // Saint Martin (French Part)
    'MAR', // Morocco
    'MCO', // Monaco
    'MDA', // Moldova, Republic Of
    'MDG', // Madagascar
    'MDV', // Maldives
    'MEX', // Mexico
    'MHL', // Marshall Islands
    'MKD', // Macedonia, The Former Yugoslav Republic Of
    'MLI', // Mali
    'MLT', // Malta
    'MMR', // Myanmar
    'MNE', // Montenegro
    'MNG', // Mongolia
    'MNP', // Northern Mariana Islands
    'MOZ', // Mozambique
    'MRT', // Mauritania
    'MSR', // Montserrat
    'MTQ', // Martinique
    'MUS', // Mauritius
    'MWI', // Malawi
    'MYS', // Malaysia
    'MYT', // Mayotte
    'NAM', // Namibia
    'NCL', // New Caledonia
    'NER', // Niger
    'NFK', // Norfolk Island
    'NGA', // Nigeria
    'NIC', // Nicaragua
    'NIU', // Niue
    'NLD', // Netherlands
    'NOR', // Norway
    'NPL', // Nepal
    'NRU', // Nauru
    'NZL', // New Zealand
    'OMN', // Oman
    'PAK', // Pakistan
    'PAN', // Panama
    'PCN', // Pitcairn
    'PER', // Peru
    'PHL', // Philippines
    'PLW', // Palau
    'PNG', // Papua New Guinea
    'POL', // Poland
    'PRI', // Puerto Rico
    'PRK', // Korea, Democratic People's Republic Of
    'PRT', // Portugal
    'PRY', // Paraguay
    'PSE', // Palestinian Territory, Occupied
    'PYF', // French Polynesia
    'QAT', // Qatar
    'REU', // Réunion
    'ROU', // Romania
    'RUS', // Russian Federation
    'RWA', // Rwanda
    'SAU', // Saudi Arabia
    'SDN', // Sudan
    'SEN', // Senegal
    'SGP', // Singapore
    'SGS', // South Georgia And The South Sandwich Islands
    'SHN', // Saint Helena, Ascension And Tristan Da Cunha
    'SJM', // Svalbard And Jan Mayen
    'SLB', // Solomon Islands
    'SLE', // Sierra Leone
    'SLV', // El Salvador
    'SMR', // San Marino
    'SOM', // Somalia
    'SPM', // Saint Pierre And Miquelon
    'SRB', // Serbia
    'STP', // Sao Tome And Principe
    'SUR', // Suriname
    'SVK', // Slovakia
    'SVN', // Slovenia
    'SWE', // Sweden
    'SWZ', // Swaziland
    'SYC', // Seychelles
    'SYR', // Syrian Arab Republic
    'TCA', // Turks And Caicos Islands
    'TCD', // Chad
    'TGO', // Togo
    'THA', // Thailand
    'TJK', // Tajikistan
    'TKL', // Tokelau
    'TKM', // Turkmenistan
    'TLS', // Timor-Leste
    'TON', // Tonga
    'TTO', // Trinidad And Tobago
    'TUN', // Tunisia
    'TUR', // Turkey
    'TUV', // Tuvalu
    'TWN', // Taiwan, Province Of China
    'TZA', // Tanzania, United Republic Of
    'UGA', // Uganda
    'UKR', // Ukraine
    'UMI', // United States Minor Outlying Islands
    'URY', // Uruguay
    'USA', // United States
    'UZB', // Uzbekistan
    'VAT', // Holy See (Vatican City State)
    'VCT', // Saint Vincent And The Grenadines
    'VEN', // Venezuela, Bolivarian Republic Of
    'VGB', // Virgin Islands, British
    'VIR', // Virgin Islands, U.s.
    'VNM', // Viet Nam
    'VUT', // Vanuatu
    'WLF', // Wallis And Futuna
    'WSM', // Samoa
    'YEM', // Yemen
    'ZAF', // South Africa
    'ZMB', // Zambia
    'ZWE', // Zimbabwe
  );

  /**
   * Map of alpha2 to alpha3 codes
   *
   * @var array
   */
  protected static $alpha2ToAlpha3Map = array(
    'AF' => 'AFG',
    'AX' => 'ALA',
    'AL' => 'ALB',
    'DZ' => 'DZA',
    'AS' => 'ASM',
    'AD' => 'AND',
    'AO' => 'AGO',
    'AI' => 'AIA',
    'AQ' => 'ATA',
    'AG' => 'ATG',
    'AR' => 'ARG',
    'AM' => 'ARM',
    'AW' => 'ABW',
    'AU' => 'AUS',
    'AT' => 'AUT',
    'AZ' => 'AZE',
    'BS' => 'BHS',
    'BH' => 'BHR',
    'BD' => 'BGD',
    'BB' => 'BRB',
    'BY' => 'BLR',
    'BE' => 'BEL',
    'BZ' => 'BLZ',
    'BJ' => 'BEN',
    'BM' => 'BMU',
    'BT' => 'BTN',
    'BO' => 'BOL',
    'BA' => 'BIH',
    'BW' => 'BWA',
    'BV' => 'BVT',
    'BR' => 'BRA',
    'IO' => 'IOT',
    'BN' => 'BRN',
    'BG' => 'BGR',
    'BF' => 'BFA',
    'BI' => 'BDI',
    'KH' => 'KHM',
    'CM' => 'CMR',
    'CA' => 'CAN',
    'CV' => 'CPV',
    'KY' => 'CYM',
    'CF' => 'CAF',
    'TD' => 'TCD',
    'CL' => 'CHL',
    'CN' => 'CHN',
    'CX' => 'CXR',
    'CC' => 'CCK',
    'CO' => 'COL',
    'KM' => 'COM',
    'CG' => 'COG',
    'CD' => 'COD',
    'CK' => 'COK',
    'CR' => 'CRI',
    'CI' => 'CIV',
    'HR' => 'HRV',
    'CU' => 'CUB',
    'CY' => 'CYP',
    'CZ' => 'CZE',
    'DK' => 'DNK',
    'DJ' => 'DJI',
    'DM' => 'DMA',
    'DO' => 'DOM',
    'EC' => 'ECU',
    'EG' => 'EGY',
    'SV' => 'SLV',
    'GQ' => 'GNQ',
    'ER' => 'ERI',
    'EE' => 'EST',
    'ET' => 'ETH',
    'FK' => 'FLK',
    'FO' => 'FRO',
    'FJ' => 'FJI',
    'FI' => 'FIN',
    'FR' => 'FRA',
    'GF' => 'GUF',
    'PF' => 'PYF',
    'TF' => 'ATF',
    'GA' => 'GAB',
    'GM' => 'GMB',
    'GE' => 'GEO',
    'DE' => 'DEU',
    'GH' => 'GHA',
    'GI' => 'GIB',
    'GR' => 'GRC',
    'GL' => 'GRL',
    'GD' => 'GRD',
    'GP' => 'GLP',
    'GU' => 'GUM',
    'GT' => 'GTM',
    'GG' => 'GGY',
    'GN' => 'GIN',
    'GW' => 'GNB',
    'GY' => 'GUY',
    'HT' => 'HTI',
    'HM' => 'HMD',
    'VA' => 'VAT',
    'HN' => 'HND',
    'HK' => 'HKG',
    'HU' => 'HUN',
    'IS' => 'ISL',
    'IN' => 'IND',
    'ID' => 'IDN',
    'IR' => 'IRN',
    'IQ' => 'IRQ',
    'IE' => 'IRL',
    'IM' => 'IMN',
    'IL' => 'ISR',
    'IT' => 'ITA',
    'JM' => 'JAM',
    'JP' => 'JPN',
    'JE' => 'JEY',
    'JO' => 'JOR',
    'KZ' => 'KAZ',
    'KE' => 'KEN',
    'KI' => 'KIR',
    'KP' => 'PRK',
    'KR' => 'KOR',
    'KW' => 'KWT',
    'KG' => 'KGZ',
    'LA' => 'LAO',
    'LV' => 'LVA',
    'LB' => 'LBN',
    'LS' => 'LSO',
    'LR' => 'LBR',
    'LY' => 'LBY',
    'LI' => 'LIE',
    'LT' => 'LTU',
    'LU' => 'LUX',
    'MO' => 'MAC',
    'MK' => 'MKD',
    'MG' => 'MDG',
    'MW' => 'MWI',
    'MY' => 'MYS',
    'MV' => 'MDV',
    'ML' => 'MLI',
    'MT' => 'MLT',
    'MH' => 'MHL',
    'MQ' => 'MTQ',
    'MR' => 'MRT',
    'MU' => 'MUS',
    'YT' => 'MYT',
    'MX' => 'MEX',
    'FM' => 'FSM',
    'MD' => 'MDA',
    'MC' => 'MCO',
    'MN' => 'MNG',
    'ME' => 'MNE',
    'MS' => 'MSR',
    'MA' => 'MAR',
    'MZ' => 'MOZ',
    'MM' => 'MMR',
    'NA' => 'NAM',
    'NR' => 'NRU',
    'NP' => 'NPL',
    'NL' => 'NLD',
    'AN' => 'ANT',
    'NC' => 'NCL',
    'NZ' => 'NZL',
    'NI' => 'NIC',
    'NE' => 'NER',
    'NG' => 'NGA',
    'NU' => 'NIU',
    'NF' => 'NFK',
    'MP' => 'MNP',
    'NO' => 'NOR',
    'OM' => 'OMN',
    'PK' => 'PAK',
    'PW' => 'PLW',
    'PS' => 'PSE',
    'PA' => 'PAN',
    'PG' => 'PNG',
    'PY' => 'PRY',
    'PE' => 'PER',
    'PH' => 'PHL',
    'PN' => 'PCN',
    'PL' => 'POL',
    'PT' => 'PRT',
    'PR' => 'PRI',
    'QA' => 'QAT',
    'RE' => 'REU',
    'RO' => 'ROU',
    'RU' => 'RUS',
    'RW' => 'RWA',
    'BL' => 'BLM',
    'SH' => 'SHN',
    'KN' => 'KNA',
    'LC' => 'LCA',
    'MF' => 'MAF',
    'PM' => 'SPM',
    'VC' => 'VCT',
    'WS' => 'WSM',
    'SM' => 'SMR',
    'ST' => 'STP',
    'SA' => 'SAU',
    'SN' => 'SEN',
    'RS' => 'SRB',
    'SC' => 'SYC',
    'SL' => 'SLE',
    'SG' => 'SGP',
    'SK' => 'SVK',
    'SI' => 'SVN',
    'SB' => 'SLB',
    'SO' => 'SOM',
    'ZA' => 'ZAF',
    'GS' => 'SGS',
    'ES' => 'ESP',
    'LK' => 'LKA',
    'SD' => 'SDN',
    'SR' => 'SUR',
    'SJ' => 'SJM',
    'SZ' => 'SWZ',
    'SE' => 'SWE',
    'CH' => 'CHE',
    'SY' => 'SYR',
    'TW' => 'TWN',
    'TJ' => 'TJK',
    'TZ' => 'TZA',
    'TH' => 'THA',
    'TL' => 'TLS',
    'TG' => 'TGO',
    'TK' => 'TKL',
    'TO' => 'TON',
    'TT' => 'TTO',
    'TN' => 'TUN',
    'TR' => 'TUR',
    'TM' => 'TKM',
    'TC' => 'TCA',
    'TV' => 'TUV',
    'UG' => 'UGA',
    'UA' => 'UKR',
    'AE' => 'ARE',
    'GB' => 'GBR',
    'US' => 'USA',
    'UM' => 'UMI',
    'UY' => 'URY',
    'UZ' => 'UZB',
    'VU' => 'VUT',
    'VE' => 'VEN',
    'VN' => 'VNM',
    'VG' => 'VGB',
    'VI' => 'VIR',
    'WF' => 'WLF',
    'EH' => 'ESH',
    'YE' => 'YEM',
    'ZM' => 'ZMB',
    'ZW' => 'ZWE',
  );

  /**
   * Map of alpha3 to alpha2 codes
   *
   * @var array
   */
  protected static $alpha3ToAlpha2Map = array(
    'AFG' => 'AF',
    'ALA' => 'AX',
    'ALB' => 'AL',
    'DZA' => 'DZ',
    'ASM' => 'AS',
    'AND' => 'AD',
    'AGO' => 'AO',
    'AIA' => 'AI',
    'ATA' => 'AQ',
    'ATG' => 'AG',
    'ARG' => 'AR',
    'ARM' => 'AM',
    'ABW' => 'AW',
    'AUS' => 'AU',
    'AUT' => 'AT',
    'AZE' => 'AZ',
    'BHS' => 'BS',
    'BHR' => 'BH',
    'BGD' => 'BD',
    'BRB' => 'BB',
    'BLR' => 'BY',
    'BEL' => 'BE',
    'BLZ' => 'BZ',
    'BEN' => 'BJ',
    'BMU' => 'BM',
    'BTN' => 'BT',
    'BOL' => 'BO',
    'BIH' => 'BA',
    'BWA' => 'BW',
    'BVT' => 'BV',
    'BRA' => 'BR',
    'IOT' => 'IO',
    'BRN' => 'BN',
    'BGR' => 'BG',
    'BFA' => 'BF',
    'BDI' => 'BI',
    'KHM' => 'KH',
    'CMR' => 'CM',
    'CAN' => 'CA',
    'CPV' => 'CV',
    'CYM' => 'KY',
    'CAF' => 'CF',
    'TCD' => 'TD',
    'CHL' => 'CL',
    'CHN' => 'CN',
    'CXR' => 'CX',
    'CCK' => 'CC',
    'COL' => 'CO',
    'COM' => 'KM',
    'COG' => 'CG',
    'COD' => 'CD',
    'COK' => 'CK',
    'CRI' => 'CR',
    'CIV' => 'CI',
    'HRV' => 'HR',
    'CUB' => 'CU',
    'CYP' => 'CY',
    'CZE' => 'CZ',
    'DNK' => 'DK',
    'DJI' => 'DJ',
    'DMA' => 'DM',
    'DOM' => 'DO',
    'ECU' => 'EC',
    'EGY' => 'EG',
    'SLV' => 'SV',
    'GNQ' => 'GQ',
    'ERI' => 'ER',
    'EST' => 'EE',
    'ETH' => 'ET',
    'FLK' => 'FK',
    'FRO' => 'FO',
    'FJI' => 'FJ',
    'FIN' => 'FI',
    'FRA' => 'FR',
    'GUF' => 'GF',
    'PYF' => 'PF',
    'ATF' => 'TF',
    'GAB' => 'GA',
    'GMB' => 'GM',
    'GEO' => 'GE',
    'DEU' => 'DE',
    'GHA' => 'GH',
    'GIB' => 'GI',
    'GRC' => 'GR',
    'GRL' => 'GL',
    'GRD' => 'GD',
    'GLP' => 'GP',
    'GUM' => 'GU',
    'GTM' => 'GT',
    'GGY' => 'GG',
    'GIN' => 'GN',
    'GNB' => 'GW',
    'GUY' => 'GY',
    'HTI' => 'HT',
    'HMD' => 'HM',
    'VAT' => 'VA',
    'HND' => 'HN',
    'HKG' => 'HK',
    'HUN' => 'HU',
    'ISL' => 'IS',
    'IND' => 'IN',
    'IDN' => 'ID',
    'IRN' => 'IR',
    'IRQ' => 'IQ',
    'IRL' => 'IE',
    'IMN' => 'IM',
    'ISR' => 'IL',
    'ITA' => 'IT',
    'JAM' => 'JM',
    'JPN' => 'JP',
    'JEY' => 'JE',
    'JOR' => 'JO',
    'KAZ' => 'KZ',
    'KEN' => 'KE',
    'KIR' => 'KI',
    'PRK' => 'KP',
    'KOR' => 'KR',
    'KWT' => 'KW',
    'KGZ' => 'KG',
    'LAO' => 'LA',
    'LVA' => 'LV',
    'LBN' => 'LB',
    'LSO' => 'LS',
    'LBR' => 'LR',
    'LBY' => 'LY',
    'LIE' => 'LI',
    'LTU' => 'LT',
    'LUX' => 'LU',
    'MAC' => 'MO',
    'MKD' => 'MK',
    'MDG' => 'MG',
    'MWI' => 'MW',
    'MYS' => 'MY',
    'MDV' => 'MV',
    'MLI' => 'ML',
    'MLT' => 'MT',
    'MHL' => 'MH',
    'MTQ' => 'MQ',
    'MRT' => 'MR',
    'MUS' => 'MU',
    'MYT' => 'YT',
    'MEX' => 'MX',
    'FSM' => 'FM',
    'MDA' => 'MD',
    'MCO' => 'MC',
    'MNG' => 'MN',
    'MNE' => 'ME',
    'MSR' => 'MS',
    'MAR' => 'MA',
    'MOZ' => 'MZ',
    'MMR' => 'MM',
    'NAM' => 'NA',
    'NRU' => 'NR',
    'NPL' => 'NP',
    'NLD' => 'NL',
    'ANT' => 'AN',
    'NCL' => 'NC',
    'NZL' => 'NZ',
    'NIC' => 'NI',
    'NER' => 'NE',
    'NGA' => 'NG',
    'NIU' => 'NU',
    'NFK' => 'NF',
    'MNP' => 'MP',
    'NOR' => 'NO',
    'OMN' => 'OM',
    'PAK' => 'PK',
    'PLW' => 'PW',
    'PSE' => 'PS',
    'PAN' => 'PA',
    'PNG' => 'PG',
    'PRY' => 'PY',
    'PER' => 'PE',
    'PHL' => 'PH',
    'PCN' => 'PN',
    'POL' => 'PL',
    'PRT' => 'PT',
    'PRI' => 'PR',
    'QAT' => 'QA',
    'REU' => 'RE',
    'ROU' => 'RO',
    'RUS' => 'RU',
    'RWA' => 'RW',
    'BLM' => 'BL',
    'SHN' => 'SH',
    'KNA' => 'KN',
    'LCA' => 'LC',
    'MAF' => 'MF',
    'SPM' => 'PM',
    'VCT' => 'VC',
    'WSM' => 'WS',
    'SMR' => 'SM',
    'STP' => 'ST',
    'SAU' => 'SA',
    'SEN' => 'SN',
    'SRB' => 'RS',
    'SYC' => 'SC',
    'SLE' => 'SL',
    'SGP' => 'SG',
    'SVK' => 'SK',
    'SVN' => 'SI',
    'SLB' => 'SB',
    'SOM' => 'SO',
    'ZAF' => 'ZA',
    'SGS' => 'GS',
    'ESP' => 'ES',
    'LKA' => 'LK',
    'SDN' => 'SD',
    'SUR' => 'SR',
    'SJM' => 'SJ',
    'SWZ' => 'SZ',
    'SWE' => 'SE',
    'CHE' => 'CH',
    'SYR' => 'SY',
    'TWN' => 'TW',
    'TJK' => 'TJ',
    'TZA' => 'TZ',
    'THA' => 'TH',
    'TLS' => 'TL',
    'TGO' => 'TG',
    'TKL' => 'TK',
    'TON' => 'TO',
    'TTO' => 'TT',
    'TUN' => 'TN',
    'TUR' => 'TR',
    'TKM' => 'TM',
    'TCA' => 'TC',
    'TUV' => 'TV',
    'UGA' => 'UG',
    'UKR' => 'UA',
    'ARE' => 'AE',
    'GBR' => 'GB',
    'USA' => 'US',
    'UMI' => 'UM',
    'URY' => 'UY',
    'UZB' => 'UZ',
    'VUT' => 'VU',
    'VEN' => 'VE',
    'VNM' => 'VN',
    'VGB' => 'VG',
    'VIR' => 'VI',
    'WLF' => 'WF',
    'ESH' => 'EH',
    'YEM' => 'YE',
    'ZMB' => 'ZM',
    'ZWE' => 'ZW',
  );

  /**
   * Countries which are in EU (alpha 2 codes)
   *
   * @var array
   */
  protected static $euCountriesAlpha2 = array(
    'AT',
    'BE',
    'BG',
    'CY',
    'CZ',
    'DE',
    'DK',
    'EE',
    'ES',
    'FI',
    'FR',
    'GB',
    'GR',
    'HU',
    'IE',
    'IT',
    'LT',
    'LU',
    'LV',
    'MT',
    'NL',
    'PL',
    'PT',
    'RO',
    'SE',
    'SI',
    'SK',
  );

  /**
   * Countries which are in EU (alpha 3 codes)
   *
   * @var array
   */
  protected static $euCountriesAlpha3 = array(
    'AUT',
    'BEL',
    'BGR',
    'CYP',
    'CZE',
    'DNK',
    'EST',
    'FIN',
    'FRA',
    'DEU',
    'GRC',
    'HUN',
    'IRL',
    'ITA',
    'LVA',
    'LTU',
    'LUX',
    'MLT',
    'NLD',
    'POL',
    'PRT',
    'ROU',
    'SVK',
    'SVN',
    'ESP',
    'SWE',
    'GBR',
  );

  /**
   * Validate the country code.
   *
   * @param string $currency The 2 letter country code
   * @param string $alpha Which alpha code to validate?
   * @return boolean
   */
  public static function isValidCode($code, $alpha = self::ALPHA2)
  {
    return in_array($code, ($alpha == self::ALPHA2 ? self::$countriesAlpha2 : self::$countriesAlpha3));
  }

  /**
   * Return array of all countries
   *
   * @param string $alpha Which alpha code to use?
   * @return array Array of all country codes
   */
  public static function getCountryCodes($alpha = self::ALPHA2)
  {
    return $alpha == self::ALPHA2 ? self::$countriesAlpha2 : self::$countriesAlpha3;
  }

  /**
   * Return an array of EU countries
   *
   * @param string $alpha Which alpha code to use?
   * @return array
   */
  public static function getEuropeanUnionCountries($alpha = self::ALPHA2)
  {
    return $alpha == self::ALPHA2 ? self::$euCountriesAlpha2 : self::$euCountriesAlpha3;
  }

  /**
   * Is given country in EU?
   *
   * @param string $country
   * @param string $alpha Which alpha code to use?
   * @return boolean
   */
  public static function isInEuropeanUnion($country, $alpha = self::ALPHA2)
  {
    return in_array($country, self::getEuropeanUnionCountries($alpha));
  }

  /**
   * Converts the alpha2 code to alpha3 code
   *
   * @param string $code2
   * @return string|false False if the code is invalid
   */
  public static function code2ToCode3($code2)
  {
    return isset(self::$alpha2ToAlpha3Map[$code2]) ? self::$alpha2ToAlpha3Map[$code2] : false;
  }

  /**
   * Converts the alpha3 code to alpha2 code
   *
   * @param string $code3
   * @return string|false False if the code is invalid
   */
  public static function code3toCode2($code3)
  {
    return isset(self::$alpha3ToAlpha2Map[$code3]) ? self::$alpha3ToAlpha2Map[$code3] : false;
  }

}
