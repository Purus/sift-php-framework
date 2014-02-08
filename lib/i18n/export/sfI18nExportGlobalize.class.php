<?php
/*
 * This file is part of the Sift PHP framework.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * Exports culture definition for usage with jQuery Globalize
 *
 * @package Sift
 * @subpackage i18n_export
 * @see http://github.com/jquery/globalize
 */
class sfCultureExportGlobalize extends sfCultureExport
{
  /**
   * @var sfCulture
   */
  protected $cultureInstance;

  /**
   * @var sfI18nNumberFormat
   */
  protected $numberFormat;

  /**
   * @var sfI18nDateFormat
   */
  protected $dateFormat;

  /**
   *
   * @see sfCultureExport
   */
  public function __construct($culture, $options = array())
  {
    parent::__construct($culture, $options);

    $this->cultureInstance = sfCulture::getInstance($this->culture);
    $this->numberFormat = sfI18nNumberFormat::getInstance($this->culture);
    $this->dateFormat = sfI18nDateTimeFormat::getInstance($this->culture);
  }

  /**
   * Exports the data according to Globalize definition
   *
   * @link https://github.com/jquery/globalize#defining
   */
  public function export()
  {
    $data = array();

    $data['name'] = $this->cultureInstance->getName();
    $data['englishName'] = $this->cultureInstance->getEnglishName();
    // FIXME: isRTL is not included in CLDR definition
    $data['isRTL'] = false;

    $pattern = $this->numberFormat->getPercentageInstance($this->culture)->getPattern();
    $currency = $this->numberFormat->getCurrencyInstance();
    $symbol = $currency->getCurrencySymbol();

    $data['numberFormat'] = array(
      ',' => $this->numberFormat->getGroupSeparator(),
      '.' => $this->numberFormat->getDecimalSeparator(),
      'NaN' => $this->numberFormat->getNaNSymbol(),
        'negativeInfinity' => $this->numberFormat->getPositiveInfinitySymbol(),
        'positiveInfinity' => $this->numberFormat->getNegativeInfinitySymbol(),
      'groupSizes' => array($pattern['groupSize1']),
      'percent' => array(
        'pattern' => array(
          '-n%','n%' // what is this?
        ),
        ',' => ' ',
        '.' => ''
      ),
      'currency' => array(
          'pattern' => array(
            // [negativePattern, positivePattern]
            //   negativePattern: one of "($n)|-$n|$-n|$n-|(n$)|-n$|n-$|n$-|-n $|-$ n|n $-|$ n-|$ -n|n- $|($ n)|(n $)"
            //   positivePattern: one of "$n|n$|$ n|n $"
            // see: http://cldr.unicode.org/translation/number-patterns
            '($n)', '$n'
          ),
          ',' => $currency->getGroupSeparator(),
          '.' => $currency->getDecimalSeparator(),
          // Symbol -> THIS is nonsense, I should pass the currency code to forma function
          // not use currency for the culture as default
          'symbol' => $symbol
      )
    );

    // AM and PM designators in one of these forms:
    // The usual view, and the upper and lower case versions
    //      [standard,lowercase,uppercase]
    // The culture does not use AM or PM (likely all standard date
    // formats use 24 hour time)
    //      null
    // ["AM", "am", "AM" ],

    $markers = $this->dateFormat->getAMDesignator();

    $amMarkers = array(
      $markers[0], sfUtf8::lower($markers[0]), sfUtf8::upper($markers[0])
    );

    $pmMarkers = array(
      $markers[1], sfUtf8::lower($markers[1]), sfUtf8::upper($markers[1])
    );

    // "calendars" property defines all the possible calendars used by this
    // culture. There should be at least one defined with name "standard" which
    // is the default calendar used by the culture.
    // A calendar contains information about how dates are formatted,
    // information about the calendar's eras, a standard set of the date
    // formats, translations for day and month names, and if the calendar is
    // not based on the Gregorian calendar, conversion functions to and from
    // the Gregorian calendar.
    $data['calendars'] = array(
      'standard' => array(
        // name that identifies the type of calendar this is
        'name' => $this->cultureInstance->getCalendar(),
        // separator of parts of a date (e.g. "/" in 11/05/1955)
        // FIXME: separator of parts is not included! Not in CLDR?
        // '/' => '/',
        // separator of parts of a time (e.g. ":" in 05:44 PM)
        // FIXME: separator of parts is not included! Not in CLDR?
        // ':' => ':',
        // the first day of the week (0 = Sunday, 1 = Monday, etc)
        'firstDay' => $this->dateFormat->getFirstDayOfWeek(),
        'days' => array(
          'names' => $this->dateFormat->getDayNames(),
          'namesAbbr' => $this->dateFormat->getAbbreviatedDayNames(),
          'namesShort' => $this->dateFormat->getNarrowDayNames()
        ),
        'months' => array(
          'names' => $this->dateFormat->getStandAloneMonthNames(),
          'namesAbbr' => $this->dateFormat->getAbbreviatedMonthNames(),
          'namesShort' => $this->dateFormat->getNarrowMonthNames()
        ),
        // AM and PM designators in one of these forms:
        // The usual view, and the upper and lower case versions
        //      [standard,lowercase,uppercase]
        // The culture does not use AM or PM (likely all standard date
        // formats use 24 hour time)
        //      null
        // ["AM", "am", "AM" ],
        'AM' => $amMarkers,
        // [ "PM", "pm", "PM" ],
        'PM' => $pmMarkers,
        // eras in reverse chronological order.
        // name: the name of the era in this culture (e.g. A.D., C.E.)
        // start: when the era starts in ticks, null if it is the
        //        earliest supported era.
        // offset: offset in years from gregorian calendar
        'eras' => array(
          'name' => $this->dateFormat->getEra(1),
          'start' => null,
          'offset' => 0
         ),
        // when a two digit year is given, it will never be parsed as a
        // four digit year greater than this year (in the appropriate era
        // for the culture)
        // Set it as a full year (e.g. 2029) or use an offset format
        // starting from the current year: "+19" would correspond to 2029
        // if the current year is 2010.
        // FIXME: what is this for?
        'twoDigitYearMax' => 2029,
        // set of predefined date and time patterns used by the culture.
        // These represent the format someone in this culture would expect
        // to see given the portions of the date that are shown.
        'patterns' => array(
          // short date pattern
          // 'd' => "M/d/yyyy",
          'd' => $this->dateFormat->getShortDatePattern(),
          // long date pattern
          // 'D' => "dddd, MMMM dd, yyyy",
          'D' => $this->dateFormat->getLongDatePattern(),
          // short time pattern
          // 't' =>  "h:mm tt",
          't' => $this->dateFormat->getShortTimePattern(),
          // long time pattern
          // 'T' =>  "h:mm:ss tt",
          'T' => $this->dateFormat->getLongTimePattern(),
          // long date, short time pattern
          // 'f' =>  "dddd, MMMM dd, yyyy h:mm tt",
          'f' => $this->dateFormat->getFullDatePattern(),
          // long date, long time pattern
          // 'F' =>  "dddd, MMMM dd, yyyy h:mm:ss tt",
          'F' => $this->dateFormat->getFullTimePattern(),
          // month/day pattern
          // 'M' => "MMMM dd"
          // FIXME: what is this? This is not in CLDR
          'M' => 'MMMM dd',
          // month/year pattern
          // 'Y' =>  "yyyy MMMM",
          // FIXME: what is this? This is not in CLDR
          'Y' => 'MMMM yyyy'
        )
      )
    );

    return $data;
  }

}
