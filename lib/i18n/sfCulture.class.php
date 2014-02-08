<?php
/*
 * This file is part of the Sift PHP framework.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * sfCulture class.
 *
 * Represents information about a specific culture including the
 * names of the culture, the calendar used, as well as access to
 * culture-specific objects that provide methods for common operations,
 * such as formatting dates, numbers, and currency.
 *
 * The sfCulture class holds culture-specific information, such as the
 * associated language, sublanguage, country/region, calendar, and cultural
 * conventions. This class also provides access to culture-specific
 * instances of sfI18nDateTimeFormat and sfI18nNumberFormat. These objects
 * contain the information required for culture-specific operations,
 * such as formatting dates, numbers and currency.
 *
 * The culture names follow the format "<languagecode>_<country/regioncode>",
 * where <languagecode> is a lowercase two-letter code derived from ISO 639
 * codes. You can find a full list of the ISO-639 codes at
 * http://www.ics.uci.edu/pub/ietf/http/related/iso639.txt
 *
 * The <country/regioncode2> is an uppercase two-letter code derived from
 * ISO 3166. A copy of ISO-3166 can be found at
 * http://www.chemie.fu-berlin.de/diverse/doc/ISO_3166.html
 *
 * For example, Australian English is "en_AU".
 *
 * @package Sift
 * @subpackage i18n
 */
class sfCulture
{
  /**
   * Instances holder
   *
   * @var array
   */
  protected static $instances = array();

  /**
   * Data filename extension.
   *
   * @var string
   */
  protected $dataFileExt = '.dat';

  /**
   * The CLDR data array.
   * @var array
   */
  protected $data = array();

  /**
   * The current culture.
   * @var string
   */
  protected $culture;

  /**
   * Directory where the culture data is stored.
   *
   * @var string
   */
  protected $dataDir;

  /**
   * A list of data files which are loaded.
   *
   * @var array
   */
  protected $dataFiles = array();

  /**
   * The current date time format info.
   *
   * @var sfI18nDateTimeFormat
   */
  protected $dateTimeFormat;

  /**
   * The current number format info.
   *
   * @var sfI18nNumberFormat
   */
  protected $numberFormat;

  /**
   * A list of properties that are accessable/writable.
   *
   * @var array
   */
  protected $properties = array();

  /**
   * Culture type, all.
   *
   * @see getCultures()
   * @var int
   */
  const ALL = 0;

  /**
   * Culture type, neutral.
   *
   * @see getCultures()
   * @var int
   */
  const NEUTRAL = 1;

  /**
   * Culture type, specific.
   *
   * @see getCultures()
   * @var int
   */
  const SPECIFIC = 2;

  /**
   * Culture validation regular expression
   *
   */
  const CULTURE_VALIDATE_REGEXP = '/^[a-z]{2}(_[A-Z]{2,5}){0,2}$/';

  /**
   * Gets the sfCulture that for this culture string.
   *
   * @param string  $culture The culture for this instance
   * @return sfCulture Invariant culture info is "en"
   */
  public static function getInstance($culture = 'en')
  {
    if (!isset(self::$instances[$culture])) {
      self::$instances[$culture] = new sfCulture($culture);
    }

    return self::$instances[$culture];
  }

  /**
   * Displays the culture name.
   *
   * @return string the culture name.
   * @see getName()
   */
  public function __toString()
  {
    return $this->getName();
  }

  /**
   * Allows functions that begins with 'set' to be called directly
   * as an attribute/property to retrieve the value.
   *
   * @param string $name The property to get
   * @return mixed
   */
  public function __get($name)
  {
    $getProperty = 'get' . $name;
    if (in_array($getProperty, $this->properties)) {
      return $this->$getProperty();
    } else {
      throw new sfException(sprintf('{sfCulture} Property %s does not exists.', $name));
    }
  }

  /**
   * Allows functions that begins with 'set' to be called directly
   * as an attribute/property to set the value.
   *
   * @param string $name  The property to set
   * @param string $value The property value
   */
  public function __set($name, $value)
  {
    $setProperty = 'set' . $name;
    if (in_array($setProperty, $this->properties)) {
      $this->$setProperty($value);
    } else {
      throw new sfException(sprintf('{sfCulture} Property %s can not be set.', $name));
    }
  }

  /**
   * Initializes a new instance of the sfCulture class based on the
   * culture specified by name. E.g. <code>new sfCulture('en_AU');</code>
   * The culture indentifier must be of the form
   * "<language>_(country/region/variant)".
   *
   * @param string $culture a culture name, e.g. "en_AU".
   * @return return new sfCulture.
   */
  public function __construct($culture = 'en')
  {
    $this->properties = get_class_methods($this);

    if (empty($culture)) {
      $culture = 'en';
    }

    $this->dataDir = self::dataDir();
    $this->dataFileExt = self::fileExt();

    $this->setCulture($culture);

    $this->loadCultureData('root');
    $this->loadCultureData($culture);
  }

  /**
   * Gets the default directory for the CLDR data.
   *
   * @return string directory containing the CLDR data.
   * @throws LogicException If the "sf_sift_data_dir" configuration value is missing
   */
  protected static function dataDir()
  {
    if (!($dataDir = sfConfig::get('sf_sift_data_dir'))) {
      throw new LogicException('Missing "sf_sift_data_dir" configuration value. Please check the configuration.');
    }

    return sfConfig::get('sf_sift_data_dir') . '/i18n/cultures/';
  }

  /**
   * Gets the filename extension for CLDR data. Default is ".dat".
   *
   * @return string filename extension for CLDR data.
   */
  protected static function fileExt()
  {
    return '.dat';
  }

  /**
   * Determines if a given culture is valid. Simply checks that the
   * culture data exists.
   *
   * @param string $culture a culture
   * @return boolean true if valid, false otherwise.
   */
  public static function validCulture($culture)
  {
    return is_file(self::dataDir() . $culture . self::fileExt());
  }

  /**
   * Sets the culture for the current instance. The culture indentifier
   * must be of the form "<language>_(country/region)".
   *
   * @param string $culture culture identifier, e.g. "fr_FR_EURO".
   */
  protected function setCulture($culture)
  {
    if (!empty($culture)) {
      if (!self::validCulture($culture)) {
        throw new sfException(sprintf('Invalid culture supplied: %s', $culture));
      }
    }

    $this->culture = $culture;
  }

  /**
   * Loads the CLDR culture data for the specific culture identifier.
   *
   * @param string $culture the culture identifier.
   */
  protected function loadCultureData($culture)
  {
    $file_parts = explode('_', $culture);
    $current_part = $file_parts[0];

    $files = array($current_part);

    for ($i = 1, $max = count($file_parts); $i < $max; $i++) {
      $current_part .= '_' . $file_parts[$i];
      $files[] = $current_part;
    }

    foreach ($files as $file) {
      $filename = $this->dataDir . $file . $this->dataFileExt;

      if (is_file($filename) == false) {
        throw new sfException(sprintf('Data file for "%s" was not found.', $file));
      }

      if (in_array($filename, $this->dataFiles) == false) {
        array_unshift($this->dataFiles, $file);

        $data = &$this->getData($filename);
        $this->data[$file] = &$data;
        if (isset($data['__ALIAS'])) {
          $this->loadCultureData($data['__ALIAS']);
        }
        unset($data);
      }
    }
  }

  /**
   * Gets the data by unserializing the CLDR data from disk.
   * The data files are cached in a static variable inside
   * this function.
   *
   * @param string $filename the CLDR data filename
   * @return array CLDR data
   */
  protected function &getData($filename)
  {
    static $data = array();
    static $files = array();

    if (!in_array($filename, $files)) {
      $data[$filename] = unserialize(file_get_contents($filename));
      $files[] = $filename;
    }

    return $data[$filename];
  }

  /**
   * Finds the specific CLDR data information from the data.
   * The path to the specific CLDR data is separated with a slash "/".
   * E.g. To find the default calendar used by the culture, the path
   * "calendar/default" will return the corresponding default calendar.
   * Use merge=true to return the CLDR including the parent culture.
   * E.g. The currency data for a variant, say "en_AU" contains one
   * entry, the currency for AUD, the other currency data are stored
   * in the "en" data file. Thus to retrieve all the data regarding
   * currency for "en_AU", you need to use findInfo("Currencies,true);.
   *
   * @param string  $path   the data you want to find.
   * @param boolean $merge  merge the data from its parents.
   * @return mixed the specific CLDR data.
   */
  protected function findInfo($path = '/', $merge = false)
  {
    $result = array();
    foreach ($this->dataFiles as $section) {
      $info = $this->searchArray($this->data[$section], $path);

      if ($info) {
        if ($merge) {
          $result = $this->arrayAdd($result, $info);
        } else {
          return $info;
        }
      }
    }

    return $result;
  }

  /**
   * Adds an array to an already existing array.
   * If an element is already existing in array1 it is not overwritten.
   * If this element is an array this logic will be applied recursively.
   */
  private function arrayAdd($array1, $array2)
  {
    foreach ($array2 as $key => $value) {
      if (isset($array1[$key])) {
        if (is_array($array1[$key]) && is_array($value)) {
          $array1[$key] = $this->arrayAdd($array1[$key], $value);
        }
      } else {
        $array1[$key] = $value;
      }
    }

    return $array1;
  }

  /**
   * Searches the array for a specific value using a path separated using
   * slash "/" separated path. e.g to find $info['hello']['world'],
   * the path "hello/world" will return the corresponding value.
   *
   * @param array   $info  the array for search
   * @param string  $path  slash "/" separated array path.
   * @return mixed the value array using the path
   */
  protected function searchArray($info, $path = '/')
  {
    $index = explode('/', $path);

    $array = $info;

    for ($i = 0, $max = count($index); $i < $max; $i++) {
      $k = $index[$i];
      if ($i < $max - 1 && isset($array[$k])) {
        $array = $array[$k];
      } else if ($i == $max - 1 && isset($array[$k])) {
        return $array[$k];
      }
    }
  }

  /**
   * Gets the culture name in the format
   * "<languagecode2>_(country/regioncode2)".
   *
   * @return string culture name.
   */
  public function getName()
  {
    return $this->culture;
  }

  /**
   * Gets the sfI18nDateTimeFormat that defines the culturally appropriate
   * format of displaying dates and times.
   *
   * @return sfI18nDateTimeFormat date time format information for the culture.
   */
  public function getDateTimeFormat()
  {
    if (null === $this->dateTimeFormat) {
      $calendar = $this->getCalendar();

      $info = $this->findInfo("calendar/{$calendar}", true);
      $this->setDateTimeFormat(new sfI18nDateTimeFormat($info));
    }

    return $this->dateTimeFormat;
  }

  /**
   * Sets the date time format information.
   *
   * @param sfI18nDateTimeFormat $dateTimeFormat the new date time format info.
   */
  public function setDateTimeFormat($dateTimeFormat)
  {
    $this->dateTimeFormat = $dateTimeFormat;
  }

  /**
   * Gets the default calendar used by the culture, e.g. "gregorian".
   *
   * @return string the default calendar.
   */
  public function getCalendar()
  {
    return $this->findInfo('calendar/default');
  }

  /**
   * Gets the culture name in the language that the culture is set
   * to display. Returns <code>array('Language','Country');</code>
   * 'Country' is omitted if the culture is neutral.
   *
   * @return array array with language and country as elements, localized.
   */
  public function getNativeName()
  {
    $lang = substr($this->culture, 0, 2);
    $reg = substr($this->culture, 3, 2);
    $language = $this->findInfo("languages/{$lang}");
    $region = $this->findInfo("countries/{$reg}");
    if ($region) {
      return $language . ' (' . $region . ')';
    } else {
      return $language;
    }
  }

  /**
   * Gets the culture name in English.
   * Returns <code>array('Language','Country');</code>
   * 'Country' is omitted if the culture is neutral.
   *
   * @return array array with language and country as elements.
   */
  public function getEnglishName()
  {
    $lang = substr($this->culture, 0, 2);
    $reg = substr($this->culture, 3, 2);
    $culture = $this->getInvariantCulture();

    $language = $culture->findInfo("languages/{$lang}");
    if (count($language) == 0) {
      return $this->culture;
    }

    $region = $culture->findInfo("countries/{$reg}");

    return $region ? $language . ' (' . $region . ')' : $language;
  }

  /**
   * Gets the sfCulture that is culture-independent (invariant).
   * Any changes to the invariant culture affects all other
   * instances of the invariant culture.
   * The invariant culture is assumed to be "en";
   *
   * @return sfCulture invariant culture info is "en".
   */
  public static function getInvariantCulture()
  {
    static $invariant;

    if (null === $invariant) {
      $invariant = new sfCulture();
    }

    return $invariant;
  }

  /**
   * Gets a value indicating whether the current sfCulture
   * represents a neutral culture. Returns true if the culture
   * only contains two characters.
   *
   * @return boolean true if culture is neutral, false otherwise.
   */
  public function getIsNeutralCulture()
  {
    return strlen($this->culture) == 2;
  }

  /**
   * Gets the sfI18nNumberFormat that defines the culturally appropriate
   * format of displaying numbers, currency, and percentage.
   *
   * @return sfI18nNumberFormat the number format info for current culture.
   */
  public function getNumberFormat()
  {
    if (null === $this->numberFormat) {
      $elements = $this->findInfo('numberElements', true);
      $patterns = $this->findInfo('numberPatterns', true);
      $currencies = $this->getCurrencies(null, true);

      $this->setNumberFormat(new sfI18nNumberFormat(
                      array('numberElements' => $elements,
                          'numberPatterns' => $patterns,
                          'currencies' => $currencies)));
    }

    return $this->numberFormat;
  }

  /**
   * Sets the number format information.
   *
   * @param sfI18nNumberFormat $numberFormat the new number format info.
   */
  public function setNumberFormat($numberFormat)
  {
    $this->numberFormat = $numberFormat;
  }

  /**
   * Gets the sfCulture that represents the parent culture of the
   * current sfCulture
   *
   * @return sfCulture parent culture information.
   */
  public function getParent()
  {
    if (strlen($this->culture) == 2) {
      return $this->getInvariantCulture();
    }

    return new sfCulture(substr($this->culture, 0, 2));
  }

  /**
   * Gets the list of supported cultures filtered by the specified
   * culture type. This is an EXPENSIVE function, it needs to traverse
   * a list of CLDR files in the data directory.
   * This function can be called statically.
   *
   * @param int $type culture type, sfCulture::ALL, sfCulture::NEUTRAL
   * or sfCulture::SPECIFIC.
   * @return array list of culture information available.
   */
  public static function getCultures($type = sfCulture::ALL)
  {
    $dataDir = sfCulture::dataDir();
    $dataExt = sfCulture::fileExt();
    $dir = dir($dataDir);

    $neutral = array();
    $specific = array();

    while (false !== ($entry = $dir->read())) {
      if (is_file($dataDir . $entry) && substr($entry, -4) == $dataExt && $entry != 'root' . $dataExt) {
        $culture = substr($entry, 0, -4);
        if (strlen($culture) == 2) {
          $neutral[] = $culture;
        } else {
          $specific[] = $culture;
        }
      }
    }
    $dir->close();

    switch ($type) {
      case sfCulture::ALL:
        $all = array_merge($neutral, $specific);
        sort($all);

        return $all;
        break;
      case sfCulture::NEUTRAL:
        return $neutral;
        break;
      case sfCulture::SPECIFIC:
        return $specific;
        break;
    }
  }

  /**
   * Get the country name in the current culture for the given code.
   *
   * @param  string $code A valid country code
   *
   * @return string The country name in the current culture
   */
  public function getCountry($code)
  {
    $countries = $this->findInfo('countries', true);

    if (!isset($countries[$code])) {
      throw new InvalidArgumentException(sprintf('The country %s does not exist.', $code));
    }

    return $countries[$code];
  }

  /**
   * Get the currency name in the current culture for the given code.
   *
   * @param  string $code A valid currency code
   * @return string The currency name in the current culture
   * @throws InvalidArgumentException If the currency does not exist
   */
  public function getCurrency($code)
  {
    $currencies = $this->findInfo('currencies', true);

    if (!isset($currencies[$code])) {
      throw new InvalidArgumentException(sprintf('The currency %s does not exist.', $code));
    }

    return $currencies[$code][1];
  }

  /**
   * Get the currency symbol in the current culture for the given code.
   *
   * @param string $code A valid currency code
   * @return string|null The currency name in the current culture
   * @throws InvalidArgumentException If the currency does not exist
   */
  public function getCurrencySymbol($code)
  {
    $currencies = $this->findInfo('currencies', true);

    if (!isset($currencies[$code])) {
      throw new InvalidArgumentException(sprintf('The currency %s does not exist.', $code));
    }

    return $currencies[$code][0];
  }

  /**
   * Get the language name in the current culture for the given code.
   *
   * @param  string $code A valid language code
   *
   * @return string The language name in the current culture
   */
  public function getLanguage($code)
  {
    $languages = $this->findInfo('languages', true);

    if (!isset($languages[$code])) {
      throw new InvalidArgumentException(sprintf('The language %s does not exist.', $code));
    }

    return $languages[$code];
  }

  /**
   * Gets a list of countries in the language of the localized version.
   *
   * @param  array $countries An array of countries used to restrict the returned array (null by default, which means all countries)
   *
   * @return array a list of localized country names.
   */
  public function getCountries($countries = null)
  {
    $countries = $this->getCountryCodes($countries);
    $allCountries = $this->findInfo('countries', true);

    // restrict countries to a sub-set
    if (null !== $countries) {
      if ($problems = array_diff($countries, array_keys($allCountries))) {
        throw new InvalidArgumentException(sprintf('The following countries do not exist: %s.', implode(', ', $problems)));
      }

      $allCountries = array_intersect_key($allCountries, array_flip($countries));
    }

    $this->sortArray($allCountries);

    return $allCountries;
  }

  /**
   * Gets a list of currencies in the language of the localized version.
   *
   * @param  array   $currencies An array of currencies used to restrict the returned array (null by default, which means all currencies)
   * @param  Boolean $full       Whether to return the symbol and the name or not (false by default)
   *
   * @return array a list of localized currencies.
   */
  public function getCurrencies($currencies = null, $full = false)
  {
    $allCurrencies = $this->findInfo('currencies', true);

    // restrict countries to a sub-set
    if (null !== $currencies) {
      if ($problems = array_diff($currencies, array_keys($allCurrencies))) {
        throw new InvalidArgumentException(sprintf('The following currencies do not exist: %s.', implode(', ', $problems)));
      }

      $allCurrencies = array_intersect_key($allCurrencies, array_flip($currencies));
    }

    if (!$full) {
      foreach ($allCurrencies as $key => $value) {
        if (empty($value[1])) {
          unset($allCurrencies[$key]);
          continue;
        }
        $allCurrencies[$key] = $value[1];
      }
    }

    $this->sortArray($allCurrencies);

    return $allCurrencies;
  }

  /**
   * Gets a list of languages in the language of the localized version.
   *
   * @param  array $languages An array of languages used to restrict the returned array (null by default, which means all languages)
   *
   * @return array list of localized language names.
   */
  public function getLanguages($languages = null)
  {
    $allLanguages = $this->findInfo('languages', true);

    // restrict languages to a sub-set
    if (null !== $languages) {
      if ($problems = array_diff($languages, array_keys($allLanguages))) {
        throw new InvalidArgumentException(sprintf('The following languages do not exist: %s.', implode(', ', $problems)));
      }

      $allLanguages = array_intersect_key($allLanguages, array_flip($languages));
    }

    $this->sortArray($allLanguages);

    return $allLanguages;
  }

  /**
   * Gets a list of scripts in the language of the localized version.
   *
   * @return array list of localized script names.
   */
  public function getScripts()
  {
    return $this->findInfo('scripts', true);
  }

  /**
   * Gets a list of postcodes regular expressions used for validation
   *
   * @param array $countries Array of countries
   * @return array
   */
  public function getPostCodes($countries = null)
  {
    $countries = $this->getCountryCodes($countries);

    $allPostCodes = $this->findInfo('postCodes', true);

    if ($countries != null) {
      // all countries are in uppercase
      $countries = array_map('strtoupper', $countries);

      if ($problems = array_diff($countries, array_keys($allPostCodes))) {
        throw new InvalidArgumentException(sprintf('The following postCodes do not exist: %s.', implode(', ', $problems)));
      }

      $allPostCodes = array_intersect_key($allPostCodes, array_flip($countries));
    }

    ksort($allPostCodes);

    return $allPostCodes;
  }

  /**
   * Gets a list of phone numbers regular expressions used for validation
   *
   * @param array $countries Array of countries
   * @param boolean $sort Sort the result by the country code?
   * @return array
   */
  public function getPhoneNumbers($countries = null, $sort = true)
  {
    $countries = $this->getCountryCodes($countries);

    $allPhoneNumbers = $this->findInfo('phoneNumbers', true);

    if ($countries != null) {
      // all countries are in uppercase
      $countries = array_map('strtoupper', $countries);

      if ($problems = array_diff($countries, array_keys($allPhoneNumbers))) {
        throw new InvalidArgumentException(sprintf('The following phoneNumbers do not exist: %s.', implode(', ', $problems)));
      }

      $result = array();
      foreach ($countries as $countryCode) {
        $result[$countryCode] = $allPhoneNumbers[$countryCode];
      }

      // We cannot use array_intersect_key because we need to preserve the order from countries
      $allPhoneNumbers = $result;
    }

    if ($sort) {
      ksort($allPhoneNumbers);
    }

    return $allPhoneNumbers;
  }

  /**
   * Gets a list of timezones in the language of the localized version.
   *
   * @return array list of localized timezones.
   */
  public function getTimeZones()
  {
    return $this->findInfo('timeZones', true);
  }

  /**
   * sorts the passed array according to the locale of this sfCulture class
   *
   * @param  array the array to be sorted wiht "asort" and this locale
   */
  public function sortArray(&$array)
  {
    $oldLocale = setlocale(LC_COLLATE, 0);
    setlocale(LC_COLLATE, $this->getName());
    asort($array, SORT_LOCALE_STRING);
    setlocale(LC_COLLATE, $oldLocale);
  }

  /**
   * Get country codes. Handle special case like "eu_only" for Eu only countries
   *
   * @param string|array $countries
   * @return array
   */
  protected function getCountryCodes($countries)
  {
    // handle special cases
    if (is_string($countries) && strtolower($countries) == 'eu_only') {
      $countries = sfISO3166::getEuropeanUnionCountries();
    }

    return $countries;
  }

}
