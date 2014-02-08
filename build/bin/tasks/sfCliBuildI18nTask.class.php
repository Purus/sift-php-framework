<?php

/*
 * This file is part of the Sift PHP framework.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * Builds i18n data files
 *
 * @package    Sift
 * @subpackage build
 */
class sfCliBuildI18nTask extends sfCliBaseBuildTask
{

    /**
     * @see sfCliTask
     */
    protected function configure()
    {
        $this->addOptions(array());

        $this->aliases = array();
        $this->namespace = '';
        $this->name = 'i18n';
        $this->briefDescription = 'Builds I18n data files';

        $scriptName = $this->environment->get('script_name');

        $this->detailedDescription
            = <<<EOF
The [i18n|INFO] task builds i18n data files (culture information) from CLDR and phone metadata.

EOF;
    }

    /**
     * @see sfCliTask
     */
    protected function execute($arguments = array(), $options = array())
    {
        $this->logSection($this->getFullName(), 'Preparing ...');

        $this->buildCldrData($this->getCultures());
        $this->buildPhoneData();

        $this->logSection($this->getFullName(), 'Done.');
    }

    protected function buildPhoneData()
    {
        $phoneDatabase = $this->environment->get('build_data_dir') . '/PhoneNumberMetadata.xml';
        $phoneXml = simplexml_load_file($phoneDatabase, 'sfXmlElement');
        $countries = sfISO3166::getCountryCodes();

        $data = array();
        foreach ($countries as $countryCode) {
            $territory = current($phoneXml->xpath(sprintf('//territory[@id="%s"]', $countryCode)));

            if (!$territory) {
                $this->logSection($this->getFullName(), sprintf('Phone data does not exist for "%s"', $countryCode));
                continue;
            }

            $a = $territory->attributes();

            $countryData['iprefix'] = (string)$a['internationalPrefix'];
            $numberFormats = $territory->xpath(
                sprintf('//territory[@id="%s"]/availableFormats/numberFormat', $countryCode)
            );

            $countryData = array(
                'iprefix' => (string)$a['internationalPrefix'],
                'code'    => (string)$a['countryCode']
            );

            $formats = array();

            if ($numberFormats) {
                foreach ($numberFormats as $numberFormat) {
                    $numberFormatAttributes = $numberFormat->attributes();
                    $numberFormatArray = $numberFormat->toArray();
                    $formats[] = array(
                        'format' => (string)$numberFormatArray['format'],
                        'pattern' => (string)$numberFormatAttributes['pattern'],
                    );
                }
            }

            $countryData['patterns'] = $formats;
            $data[$countryCode] = $countryData;
        }

        ksort($data);

        $i18nDir = $this->environment->get('i18n_data_dir') . '/cultures';
        // load root
        $rootData = unserialize(file_get_contents($i18nDir . '/root.dat'));
        $rootData['phoneNumbers'] = $data;
        $rootData = file_put_contents($i18nDir . '/root.dat', serialize($rootData));
    }

    /**
     * Builds CLDR data
     */
    protected function buildCldrData($buildCultures)
    {
        $cldrDir = $this->environment->get('build_data_dir') . '/cldr/common';
        $i18nDir = $this->environment->get('i18n_data_dir') . '/cultures';

        $files = glob($cldrDir . '/main/*.xml');

        // supported zones by current php version
        $supportedZones = timezone_identifiers_list();
        // invalid!
        $invalidLocales = array('th', 'th_TH');

        // Zero-based index for first day of the week,
        // e.g. Sunday (returns 0), or Monday (returns 1)
        $weekDayMap = array(
            'sun' => 0,
            'mon' => 1,
            'tue' => 2,
            'wed' => 3,
            'thu' => 4,
            'fri' => 5,
            'sat' => 6,
        );

        foreach ($files as $file) {
            $locale = basename($file);
            $locale = substr($locale, 0, -4);
            $territory = false;
            if (strpos($locale, '_') !== false) {
                $parts = explode('_', $locale);
                $territory = $parts[count($parts) - 1];
            }

            if (in_array($locale, $invalidLocales)) {
                continue;
            }

            // we build only common cultures!
            if (!in_array($locale, $buildCultures) && $locale != 'root') {
                continue;
            }

            $this->logSection($this->getFullName(), sprintf('Parsing locale "%s"', $locale));

            $data = array();

            $xml = simplexml_load_file($file);

            $v = current($xml->xpath('/ldml/identity/version'));
            $v = $v->attributes();

            $data['version'] = (string)$v['number'];

            // keys
            $data['keys'] = array();
            $keys = $xml->xpath('/ldml/localeDisplayNames/keys/key');
            foreach ($keys as $key) {
                $a = $key->attributes();
                $keyName = (string)$a['type'];
                $data['keys'][$keyName] = (string)($key);
            }

            $languages = $xml->xpath('/ldml/localeDisplayNames/languages');
            $data['languages'] = array();
            foreach ($languages as $_language) {
                foreach ($_language as $lang) {
                    $data['languages'][] = (string)$lang;
                }
            }

            $scripts = $xml->xpath('/ldml/localeDisplayNames/scripts');
            $data['scripts'] = array();
            foreach ($scripts as $_script) {
                foreach ($_script as $script) {
                    $data['scripts'][] = (string)$script;
                }
            }

            // types
            $types = $xml->xpath('/ldml/localeDisplayNames/types/type');
            $data['types'] = array();
            foreach ($types as $_type) {
                $a = $_type->attributes();
                $typeType = (string)$a['type'];

                // FIXME: what to do with other types?
                if ($typeType != 'calendar') {
                    continue;
                }

                if (!isset($data['type'][$typeType])) {
                    $data['type'][$typeType] = array();
                }

                $data['type'][$typeType][] = (string)$_type;
            }


            // Countries
            $data['countries'] = array();
            $territories = $xml->xpath('/ldml/localeDisplayNames/territories/territory');
            foreach ($territories as $_territory) {
                $a = $_territory->attributes();
                $territoryType = (string)$a->type;
                if (preg_match("/^\d+$/", $territoryType) || $territoryType == 'ZZ'
                    || (string)$a->draft == 'unconfirmed'
                ) {
                    continue;
                }
                $data['countries'][$territoryType] = (string)$_territory;
            }

            // scripts
            $data['scripts'] = array();
            $scripts = $xml->xpath('/ldml/localeDisplayNames/scripts/script');
            foreach ($scripts as $script) {
                $a = $script->attributes();
                if ((string)$a->draft == 'unconfirmed' || (string)$a->alt == 'variant') {
                    continue;
                }
                $data['scripts'][(string)$a->type] = (string)$script;
            }

            // Currencies
            $data['currencies'] = array();
            $currencies = $xml->xpath('/ldml/numbers/currencies/currency');

            foreach ($currencies as $currency) {
                // special handling for root.xml
                $a = $currency->attributes();
                $key = (string)$a->type;
                $name = (string)current($currency->xpath('displayName'));

                if (!sfISO4217::isValidCode($key)) {
                    // printf("Skipping invalid currency %s\n", $key);
                    continue;
                }

                if ($locale == 'root') {
                    $symbol = (string)current($currency->xpath('symbol'));
                    $data['currencies'][$key][0] = $symbol;
                    $data['currencies'][$key][1] = null;
                } else {
                    if (empty($name)) {
                        continue;
                    }
                    $symbol = (string)current($currency->xpath('symbol'));
                    $data['currencies'][$key][0] = $symbol ? $symbol : null;
                    $data['currencies'][$key][1] = $name;
                }
            }

            // Languages
            $data['languages'] = array();
            $languages = $xml->xpath('/ldml/localeDisplayNames/languages/language');
            foreach ($languages as $language) {
                $a = $language->attributes();
                $key = (string)$a->type;
                $data['languages'][$key] = (string)$language;
            }

            // calendar
            $data['calendar'] = array();

            // get default calendar
            // <default choice="gregorian"/>
            $default = $xml->xpath('/ldml/dates/calendars/default');
            if ($default) {
                $default = current($default);
                $defaultAttributes = $default->attributes();
                $choice = (string)$defaultAttributes->choice;
                $data['calendar']['default'] = $choice;
            }

            $calendars = $xml->xpath('/ldml/dates/calendars/calendar');
            foreach ($calendars as $calendar) {
                $a = $calendar->attributes();
                $type = (string)$a->type;

                $data['calendar'][$type] = array();

                // eras
                $eras = $calendar->xpath('eras/eraAbbr/era');
                $data['calendar'][$type]['eras'] = array();
                foreach ($eras as $era) {
                    $a = $era->attributes();
                    $eraDraft = (string)$a->draft;
                    if ($eraDraft == 'unconfirmed') {
                        continue;
                    }
                    $data['calendar'][$type]['eras'][] = (string)$era;
                }

                // months
                $data['calendar'][$type]['monthNames'] = array();

                $monthContexts = $calendar->xpath('months/monthContext');

                foreach ($monthContexts as $monthContext) {
                    $monthWidths = $monthContext->xpath('monthWidth');
                    $a = $monthContext->attributes();
                    $contextType = (string)$a->type;

                    if (!isset($data['calendar'][$type]['monthNames'][$contextType])) {
                        $data['calendar'][$type]['monthNames'][$contextType] = array();
                    }

                    foreach ($monthWidths as $monthWidth) {
                        $a = $monthWidth->attributes();
                        $format = (string)$a->type;
                        $months = $monthWidth->xpath('month');

                        if (!isset($data['calendar'][$type]['monthNames'][$contextType][$format])) {
                            $data['calendar'][$type]['monthNames'][$contextType][$format] = array();
                        }

                        foreach ($months as $month) {
                            $monthAttributes = $month->attributes();
                            $monthDraft = (string)$monthAttributes->draft;
                            if ($monthDraft == 'unconfirmed') {
                                continue;
                            }
                            $data['calendar'][$type]['monthNames'][$contextType][$format][] = (string)$month;
                        }
                    }
                }

                // months
                $data['calendar'][$type]['dayNames'] = array();
                $dayContexts = $calendar->xpath('days/dayContext');

                foreach ($dayContexts as $dayContext) {
                    $dayWidths = $dayContext->xpath('dayWidth');
                    $a = $dayContext->attributes();
                    $contextType = (string)$a->type;

                    if (!isset($data['calendar'][$type]['monthNames'][$contextType])) {
                        $data['calendar'][$type]['monthNames'][$contextType] = array();
                    }

                    foreach ($dayWidths as $dayWidth) {
                        $a = $dayWidth->attributes();
                        $format = (string)$a->type;
                        $days = $dayWidth->xpath('day');

                        if (!isset($data['calendar'][$type]['dayNames'][$contextType][$format])) {
                            $data['calendar'][$type]['dayNames'][$contextType][$format] = array();
                        }

                        foreach ($days as $day) {
                            $data['calendar'][$type]['dayNames'][$contextType][$format][] = (string)$day;
                        }
                    }
                }

                // supplemental data
                $supplementalDataXml = simplexml_load_file($cldrDir . '/supplemental/supplementalData.xml');

                $weekData = $supplementalDataXml->xpath('/supplementalData/weekData/firstDay');

                // first day of week
                // monday is default for whole world
                $firstDayOfWeek = 1;

                foreach ($weekData as $wd) {
                    $attributes = $wd->attributes();
                    $territories = explode(' ', $attributes['territories']);
                    if ($territory !== false && in_array($territory, $territories)) {
                        $day = (string)$attributes['day'];
                        $firstDayOfWeek = $weekDayMap[$day];
                        // dont break if item is found, since GB is listen in monday and then
                        // redefined on sunday!
                    }
                }

                // first day of week
                $data['calendar'][$type]['firstDayOfWeek'] = $firstDayOfWeek;

                // dayPeriods
                $dayPeriodWidths = $calendar->xpath('dayPeriods/dayPeriodContext/dayPeriodWidth');
                $markers = array();
                foreach ($dayPeriodWidths as $dayPeriodWidth) {
                    $dAttributes = $dayPeriodWidth->attributes();

                    if ((string)$dAttributes['type'] != 'wide') {
                        // continue;
                    }

                    foreach ($dayPeriodWidth->xpath('dayPeriod') as $dayPeriod) {
                        $attributes = $dayPeriod->attributes();
                        $dType = (string)$attributes['type'];

                        if ((string)$attributes['alt'] == 'variant') {
                            continue;
                        }

                        // we have to catch only am, pm
                        if ($dType == 'am') {
                            $markers['am'] = (string)$dayPeriod;
                        } elseif ($dType == 'pm') {
                            $markers['pm'] = (string)$dayPeriod;
                        }
                    }
                }

                if (isset($markers['am']) && isset($markers['pm'])) {
                    $data['calendar'][$type]['amPmMarkers'] = array($markers['am'], $markers['pm']);
                }

                // timeFormats
                $data['calendar'][$type]['timeFormats'] = array();

                $formats = $calendar->xpath('timeFormats/timeFormatLength');
                foreach ($formats as $format) {
                    $a = $format->attributes();
                    $format_type = (string)$a['type'];
                    if ((string)$a->draft == 'unconfirmed') {
                        continue;
                    }
                    foreach ($format->xpath('timeFormat/pattern') as $pattern) {
                        $data['calendar'][$type]['timeFormats'][$format_type] = (string)$pattern;
                    }
                }

                // dateFormats
                $data['calendar'][$type]['dateFormats'] = array();
                $formats = $calendar->xpath('dateFormats/dateFormatLength');
                foreach ($formats as $format) {
                    $a = $format->attributes();
                    $format_type = (string)$a['type'];
                    foreach ($format->xpath('dateFormat/pattern') as $pattern) {
                        $data['calendar'][$type]['dateFormats'][$format_type] = (string)$pattern;
                        // get only first one
                        break;
                    }
                }

                // numberSystem
                $data['numberSystem'] = array();
                $numberSystem = current($xml->xpath('/ldml/numbers/defaultNumberingSystem'));

                if ($numberSystem) {
                    $data['numberSystem'][] = (string)$numberSystem;
                }

                // numberFormats
                $data['numberPatterns'] = array();
                $decimalFormatLengths = $xml->xpath('/ldml/numbers/decimalFormats/decimalFormatLength');

                foreach ($decimalFormatLengths as $decimalFormatLength) {
                    $df = $decimalFormatLength->attributes();
                    if (isset($df['type']) && $df['type'] == 'short') {
                        continue;
                    }

                    $decimalFormats = $decimalFormatLength->xpath('decimalFormat');

                    foreach ($decimalFormats as $decimalFormat) {
                        $pattern = (string)current($decimalFormat->xpath('pattern'));
                        $data['numberPatterns'][0] = $pattern;
                        break 2;
                    }
                }

                $currencyFormats = $xml->xpath(
                    '/ldml/numbers/currencyFormats/currencyFormatLength/currencyFormat/pattern'
                );
                foreach ($currencyFormats as $currencyFormat) {
                    $data['numberPatterns'][1] = (string)$currencyFormat;
                    break;
                }

                $percentFormats = $xml->xpath('/ldml/numbers/percentFormats/percentFormatLength/percentFormat/pattern');
                foreach ($percentFormats as $percentFormat) {
                    $data['numberPatterns'][2] = (string)$percentFormat;
                    break;
                }

                $scientificFormats = $xml->xpath(
                    '/ldml/numbers/scientificFormats/scientificFormatLength/scientificFormat/pattern'
                );
                foreach ($scientificFormats as $scientificFormat) {
                    $data['numberPatterns'][3] = (string)$scientificFormat;
                    break;
                }

                // numberElements
                $data['numberElements'] = array();

                $decimal = (string)current($xml->xpath('/ldml/numbers/symbols/decimal'));

                if ($decimal != '') {
                    $data['numberElements'][0] = $decimal;
                }

                $group = (string)current($xml->xpath('/ldml/numbers/symbols/group'));
                if ($group != '') {
                    $data['numberElements'][1] = $group;
                }

                $list = (string)current($xml->xpath('/ldml/numbers/symbols/list'));
                if ($list != '') {
                    $data['numberElements'][2] = $list;
                }

                $percentSign = (string)current($xml->xpath('/ldml/numbers/symbols/percentSign'));
                if ($percentSign != '') {
                    $data['numberElements'][3] = $percentSign;
                }

                $nativeZeroDigit = (string)current($xml->xpath('/ldml/numbers/symbols/nativeZeroDigit'));
                if ($nativeZeroDigit != '') {
                    $data['numberElements'][4] = $nativeZeroDigit;
                }

                $patternDigit = (string)current($xml->xpath('/ldml/numbers/symbols/patternDigit'));
                if ($patternDigit != '') {
                    $data['numberElements'][5] = $patternDigit;
                }

                $minusSign = (string)current($xml->xpath('/ldml/numbers/symbols/minusSign'));
                if ($minusSign != '') {
                    $data['numberElements'][6] = $minusSign;
                }

                $exponential = (string)current($xml->xpath('/ldml/numbers/symbols/exponential'));
                if ($exponential != '') {
                    $data['numberElements'][7] = $exponential;
                }

                $perMille = (string)current($xml->xpath('/ldml/numbers/symbols/perMille'));
                if ($perMille != '') {
                    $data['numberElements'][8] = $perMille;
                }

                $infinity = (string)current($xml->xpath('/ldml/numbers/symbols/infinity'));
                if ($infinity != '') {
                    $data['numberElements'][9] = $infinity;
                }

                $nan = (string)current($xml->xpath('/ldml/numbers/symbols/nan'));
                if ($nan != '') {
                    $data['numberElements'][10] = $nan;
                }

                $plusSign = (string)current($xml->xpath('/ldml/numbers/symbols/plusSign'));
                if ($plusSign != '') {
                    $data['numberElements'][11] = $plusSign;
                }

                $data['timeZones'] = array();

                // only for root!
                if ($locale == 'root') {
                    // postcodes
                    $postcodesXml = simplexml_load_file($cldrDir . '/supplemental/postalCodeData.xml');
                    $postCodes = $postcodesXml->xpath('/supplementalData/postalCodeData/postCodeRegex');
                    $data['postCodes'] = array();
                    foreach ($postCodes as $postCode) {
                        $a = $postCode->attributes();
                        $countryId = (string)$a->territoryId;
                        if (!sfISO3166::isValidCode($countryId)) {
                            // printf("Skipiping invalid country code %s\n", $countryId);
                            continue;
                        }
                        $data['postCodes'][$countryId] = (string)$postCode;
                    }

                    $timeZonesXml = simplexml_load_file($cldrDir . '/supplemental/metaZones.xml');
                    $timezoneNames = $timeZonesXml->xpath('/supplementalData/metaZones/metazoneInfo/timezone');

                    foreach ($timezoneNames as $zone) {
                        $a = $zone->attributes();
                        $zoneType = (string)$a['type'];
                        if (!in_array($zoneType, $supportedZones)) {
                            continue; // skip
                        }

                        // we will make groups of timezones
                        $groupName = str_replace('_', ' ', substr($zoneType, 0, strpos($zoneType, '/')));
                        $zoneName = str_replace('_', ' ', substr($zoneType, strpos($zoneType, '/') + 1));

                        if (!isset($data['timeZones'][$groupName])) {
                            $data['timeZones'][$groupName] = array();
                        }

                        $data['timeZones'][$groupName][$zoneType] = str_replace('/', ' - ', $zoneName);
                    }

                    ksort($data['timeZones']);
                }
            } // calendars loop

            file_put_contents($i18nDir . '/' . $locale . '.dat', serialize($data));
        } // files loop
    }

}
