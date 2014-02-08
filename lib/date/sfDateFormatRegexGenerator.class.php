<?php
/*
 * This file is part of the Sift package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * The <code>sfDateFormatRegexGenerator</code> class, which implements a regular
 * expression generator for interpreting date and time strings based on date
 * format strings.
 *
 * A Perl-Compatible Regular Expression (PCRE) regular expression generator for
 * matching date formats.
 *
 * This class will convert a date format string, in the format accepted by the
 * PHP <code>date()</code> function (http://www.php.net/manual/en/function.date.php),
 * to a PCRE regular expression that can be used to match date strings of the
 * specified format.
 *
 * For performance reasons, this class is a singleton. Use the
 * <code>getInstance()</code> method to obtain an instance.
 *
 * @author Guy Paddock (guy.paddock@redbottledesign.com)
 * @link http://www.redbottledesign.com/blog/generating-pcre-regular-expressions-date-format-strings-php
 * @package Sift
 * @subpackage date
 */
class sfDateFormatRegexGenerator
{
  /**
   * The current singleton instance of <code>sfDateFormatRegexGenerator</code>.
   *
   * @var DateFormatRegexGenerator
   */
  protected static $instance;

  /**
   * A map of date format characters to the corresponding regular expression
   * that will match their output.
   *
   * @var array
   */
  protected $formatCharToRegexMap =
    array
    (
      /*** Day ***/
      // Day of the month, 2 digits with leading zeros
      'd' => '0[1-9]|[1-2][0-9]|3[0-1]',

      // A textual representation of a day, three letters
      'D' => 'Mon|Tue|Wed|Thu|Fri|Sat|Sun',

      // Day of the month without leading zeros
      'j' => '[1-9]|[1-2][0-9]||3[0-1]',

      // A full textual representation of the day of the week
      'l' => 'Monday|Tuesday|Wednesday|Thursday|Friday|Saturday|Sunday',

      // ISO-8601 numeric representation of the day of the week (added in PHP 5.1.0)
      'N' => '[1-7]',

      // English ordinal suffix for the day of the month, 2 characters
      'S' => 'st|nd|rd|th',

      // Numeric representation of the day of the week
      'w' => '[0-6]',

      // The day of the year (starting from 0)
      'z' => '[0-9]|[1-9][0-9]|[1-2][0-9]{2}|3[0-5][0-9]|36[0-5]',

      /*** Week ***/
      // ISO-8601 week number of year, weeks starting on Monday (added in PHP 4.1.0)
      'W' => '0[1-9]|[1-4][0-9]|5[0-3]',

      /*** Month ***/
      // A full textual representation of a month, such as January or March
      'F' => 'January|February|March|April|May|June|July|August|September|October|November|December',

      // Numeric representation of a month, with leading zeros
      'm' => '0[1-9]|1[0-2]',

      // A short textual representation of a month, three letters
      'M' => 'Jan|Feb|Mar|Apr|May|Jun|Jul|Aug|Sep|Oct|Nov|Dec',

      // Numeric representation of a month, without leading zeros
      'n' => '[1-9]|1[0-2]',

      // Number of days in the given month
      't' => '2[8-9]|3[0-1]',

      /*** Year ***/
      // Whether it's a leap year
      'L' => '0|1',

      /* ISO-8601 year number. This has the same value as Y, except that if the
       * ISO week number (W) belongs to the previous or next year, that year is
       * used instead. (added in PHP 5.1.0)
       */
      'o' => '[0-9]{4}',

      // A full numeric representation of a year, 4 digits
      'Y' => '[0-9]{4}',

      // A two digit representation of a year
      'y' => '[0-9]{2}',

      /*** Time ***/
      // Lowercase Ante meridiem and Post meridiem
      'a' => 'am|pm',

      // Uppercase Ante meridiem and Post meridiem
      'A' => 'AM|PM',

      // Swatch Internet time
      'B' => '[0-9]{3}',

      // 12-hour format of an hour without leading zeros
      'g' => '[0-9]|1[0-2]',

      // 24-hour format of an hour without leading zeros
      'G' => '[0-9]|1[0-9]|2[0-3]',

      // 12-hour format of an hour with leading zeros
      'h' => '0[0-9]|1[0-2]',

      // 24-hour format of an hour with leading zeros
      'H' => '0[0-9]|1[0-9]|2[0-3]',

      // Minutes with leading zeros
      'i' => '[0-5][0-9]',

      // Seconds, with leading zeros
      's' => '[0-5][0-9]',

      // Microseconds (added in PHP 5.2.2)
      'u' => '[0-9]{6}',

      /*** Timezone ***/
      // Timezone identifier (added in PHP 5.1.0)
      'e' => '[0-9a-zA-Z\/\-_]+|(?:.*\/)?(?:GMT(?:\+|-)[0-9]{1,2})',

      // Whether or not the date is in daylight saving time
      'I' => '0|1',

      // Difference to Greenwich time (GMT) in hours
      'O' => '(?:-|\+)(?:[0-1][0-9]|2[0-3])(?:[0-5][0-9])',

      /* Difference to Greenwich time (GMT) with colon between hours and minutes
       * (added in PHP 5.1.3)
       */
      'P' => '(?:-|\+)(?:[0-1][0-9]|2[0-3]):(?:[0-5][0-9])',

      // Timezone abbreviation
      'T' => '[a-zA-Z]{3,5}|GMT(?:\+|-)[0-9]{1,2}',

      // Timezone offset in seconds. The offset for timezones west of UTC is
      // always negative, and for those east of UTC is always positive.
      'Z' => '(?:-)?(?:[0-9]{1,4}|[1-4][0-9]{4}|50[0-3][0-9]{2}|50400)',

      /*** Full Date/Time ***/
      // ISO 8601 date (added in PHP 5)
      'c' => '(?:[0-9]{4})-(?:0[1-9]|1[0-2])-(?:0[1-9]|[1-2][0-9]|3[0-1])T(?:0[0-9]|1[0-9]|2[0-3]):(?:[0-5][0-9]):(?:[0-5][0-9])(?:(?:-|\+)(?:[0-1][0-9]|2[0-3]):(?:[0-5][0-9]))',

      // RFC 2822 formatted date
      'r' => '(?:Mon|Tue|Wed|Thu|Fri|Sat|Sun), (?:0[1-9]|[1-2][0-9]|3[0-1]) (?:Jan|Feb|Mar|Apr|May|Jun|Jul|Aug|Sep|Oct|Nov|Dec) (?:[0-9]{4}) (?:0[0-9]|1[0-9]|2[0-3]):(?:[0-5][0-9]):(?:[0-5][0-9]) (?:(?:-|\+)(?:[0-1][0-9]|2[0-3])(?:[0-5][0-9]))',

      // Seconds since the Unix Epoch (January 1 1970 00:00:00 GMT)
      'U' => '(?:-)?[0-9]{1,12}'
    );

  /**
   * Get the active <code>DateFormatRegexGenerator</code> instance.
   *
   * If no instance is currently active, one will be instantiated and returned.
   *
   * @return  DateFormatRegexGenerator
   *          The active <code>DateFormatRegexGenerator</code> instance.
   */
  public static function getInstance()
  {
    if(empty(self::$instance))
    {
      self::$instance = new sfDateFormatRegexGenerator();
    }

    return self::$instance;
  }

  /**
   * Generate a regular expression that will match date strings of the specified
   * format.
   *
   * If the date format string accounts for all of the text that will be in
   * strings that the regular expression will match, the regular expression can
   * be generated to match the entire input string
   * (<code>$matchEntireString</code> is <code>TRUE</code>).
   *
   * For example, when <code>$matchEntireString</code> is <code>FALSE</code>,
   * the date format string "m d, Y" would be converted into the following
   * regular expression:
   *
   * <pre>
   *  /(?:0[1-9]|1[0-2]) (?:0[1-9]|[1-2][0-9]|3[0-1]), (?:[0-9]{4})/
   * </pre>
   *
   * ...which would match a two-digit month, two-digit day, comma, and
   * four-digit year appearing in any part of an input string.
   *
   * On the other hand, if <code>$matchEntireString</code> is <code>TRUE</code>,
   * the same date format string would produce the following regular expression:
   *
   * <pre>
   *  /^(?:0[1-9]|1[0-2]) (?:0[1-9]|[1-2][0-9]|3[0-1]), (?:[0-9]{4})$/
   * </pre>
   *
   * ...which would match a string consisting only of a two-digit month,
   * two-digit day, comma, and four-digit year.
   *
   * If possible, passing <code>TRUE</code> for <code>$matchEntireString</code>
   * is encouraged, as it will significantly reduce the number of false
   * positives/partial matches that can be caused by similar-looking date and
   * time values appearing in the input string (for example, 2010 might be
   * mistakenly matched piecewise as the 20th day of the month and/or the month
   * of October, the 10th month).
   *
   * Unless you wish for certain characters in the date format string to
   * appear as they are in regular expression output, it is recommended that
   * <code>$escapeSpecialChars</code> be set to the default of <code>TRUE</code>
   * to prevent characters in the string from unexpectedly altering how the
   * regular expression will be interpreted.
   *
   * For example, with <code>$escapeSpecialChars</code> set to
   * <code>FALSE</code>, the date format string "m/d/Y" would be converted to
   * the following regular expression:
   *
   * <pre>
   *  /(?:0[1-9]|1[0-2])/(?:0[1-9]|[1-2][0-9]|3[0-1])/(?:[0-9]{4})/
   * </pre>
   *
   * ...which would not be parsed correctly by PCRE functions due to
   * unescaped forward-slashes appearing in the pattern and forward-slashes
   * being used to indicate the start and end of the regular expression.
   *
   * With code>$escapeSpecialChars</code> set to <code>TRUE</code>, the
   * regular expression output for the same date format string would be:
   *
   * <pre>
   *  /(?:0[1-9]|1[0-2])\\/(?:0[1-9]|[1-2][0-9]|3[0-1])\\/(?:[0-9]{4})/
   * </pre>
   *
   * ...which would be parsed correctly.
   *
   * @see http://www.php.net/manual/en/function.date.php
   *
   * @param string  $format
   *                The date format string, which will be converted into a
   *                regular expression, in the format accepted by the PHP
   *                <code>date()</code> function.
   *
   * @param boolean $matchEntireString
   *                Whether or not the generated regular expression should
   *                attempt to match entire input strings, or just part of
   *                each input string. The default is <code>FALSE</code>.
   *
   * @param boolean $escapeSpecialChars
   *                Whether or not any characters in the date format string that
   *                may have special significance in a regular expression should
   *                be escaped, or included raw in the regular expression that
   *                is generated.
   *
   * @return        string
   *                A regular expression that can be used to match date strings
   *                in the format specified for <code>$format</code>.
   */
  public function generateRegex($format, $matchEntireString = false, $escapeSpecialChars = true)
  {
    $patternStart = ($matchEntireString) ? '/^' : '/';
    $patternEnd   = ($matchEntireString) ? '$/' : '/';

    if ($escapeSpecialChars)
    {
      // Escape all PCRE special characters
      $format = preg_quote($format, '/');
    }

    $regex = $patternStart . strtr($format, $this->formatCharToRegexMap) . $patternEnd;

    return $regex;
  }

  /**
   * Protected constructor, to ensure that this class is a singleton.
   */
  protected function __construct()
  {
    $this->initializeMap();
  }

  /**
   * Initialize the "local" map of date format characters to regular expressions
   * for this instance.
   *
   * This method post-processes all of the patterns in the default map,
   * adding in appropriate delimiters around expressions, and adding in
   * the literal (i.e. escaped) versions of date format characters to the
   * map so that they are not converted into regular expressions. This allows
   * patterns that contain literal text to be handled correctly.
   * For example, this enables handling for a date format string like "\D\a\y",
   * which would produce the literal string "Day" (instead of being interpreted
   * the same as the format string "Day", which would produce something like
   * "Monam10").
   */
  protected function initializeMap()
  {
    $localMap =
      array
      (
        /* Translate triple back-slashes in the date format into a single
         * backslash.
         *
         * PHP interprets a quadruple backslash as a double backslash.
         *
         * Don't get a headache -- this isn't very intuitive, but it works.
         */
        '\\\\\\' => '\\',
      );

    foreach ($this->formatCharToRegexMap as $formatChar => $regexPiece)
    {
      $localMap[$formatChar] = $this->getDelimitedRegexPiece($regexPiece);

      // Add the literal, "escaped" versions of formats to the map.
      $localMap["\\\\" . $formatChar] = $formatChar;
    }

    $this->formatCharToRegexMap = $localMap;
  }

  /**
   * Returns the "delimited" version of the specified piece of a regular
   * expression.
   *
   * The delimiters around the piece prevent it from affecting how other pieces
   * of the same regular expression are interpreted.
   *
   * For example, consider two pieces: "a|b" and "c|d". If the second were
   * concatenated with the first, the resulting expression would be "a|bc|d",
   * which would match "a", "bc", or "d", which is likely not the intended
   * result. If, however, each piece was delimited by parentheses, neither
   * would affect how the other is matched and the resulting expression would
   * be "(a|b)(c|d)", which would match "ac", "ad", "bc", or "bd". This
   * method returns such delimited pieces.
   *
   * In this implementation, pieces are delimited by non-capturing
   * parentheses (i.e. "(?:" and ")"). Sub-classes like
   * <code>sfCapturingDateFormatRegexGenerator</code> can override this behavior
   * to affect how pieces are interpreted.
   *
   * @param string  $regexPiece
   *                The regular expression piece that needs delimiters.
   *
   * @return        string
   *                The resulting, properly-delimited regular expression piece.
   */
  protected function getDelimitedRegexPiece($regexPiece)
  {
    // Non-capturing
    return '(?:' . $regexPiece . ')';
  }

}
