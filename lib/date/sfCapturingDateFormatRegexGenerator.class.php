<?php
/*
 * This file is part of the Sift package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * The <code>CapturingDateFormatRegexGenerator</code> class, which extends
 * <code>DateFormatRegexGenerator</code> to implement a regular expression
 * generator for both interpreting and tokenizing date and time strings based
 * on date format strings.
 *
 * A <code>sfDateFormatRegexGenerator</code> that generates regular expressions
 * that will separately capture each part of the date format string that is
 * matched.
 *
 * With this class, the following code:
 * <pre>
 *  $dateString     = "September 1st, 2010";
 *
 *  $regexGenerator = sfCapturingDateFormatRegexGenerator::getInstance();
 *  $regex          = $regexGenerator->generateRegex('F jS, Y', TRUE);
 *
 *  preg_match($regex, $dateString, $matches);
 *
 *  print_r($matches);
 * </pre>
 *
 * ... produces the following output:
 * <pre>
 *   Array
 *   (
 *       [0] => September 1st, 2010
 *       [1] => September
 *       [2] => 1
 *       [3] => st
 *       [4] => 2010
 *   )
 * </pre>
 *
 * This makes it trivial to generate a regular expression that can tokenize a
 * particular date and time string using only a date format string as input.
 *
 * For performance reasons, this class is a singleton. Use the
 * <code>getInstance()</code> method to obtain an instance.
 *
 * @package Sift
 * @subpackage date
 */
class sfCapturingDateFormatRegexGenerator extends sfDateFormatRegexGenerator
{
  /**
   * The current singleton instance of
   * <code>sfCapturingDateFormatRegexGenerator</code>.
   *
   * @var sfCapturingDateFormatRegexGenerator
   */
  protected static $instance;

  /**
   *
   * @return sfCapturingDateFormatRegexGenerator
   */
  public static function getInstance()
  {
    if (empty(self::$instance)) {
      self::$instance = new sfCapturingDateFormatRegexGenerator();
    }

    return self::$instance;
  }

  /**
   * Override of <code>DateFormatRegexGenerator::getDelimitedRegexPiece()</code>
   * that uses capturing-parentheses as the delimiter, so that each part of the
   * date string can be obtained after a match.
   *
   * @see sfDateFormatRegexGenerator::getDelimitedRegexPiece()
   *
   * @param string  $regexPiece
   *                The regular expression piece that needs delimiters.
   *
   * @return        string
   *                The resulting, properly-delimited regular expression piece.
   */
  protected function getDelimitedRegexPiece($regex)
  {
    // Capturing
    return '(' . $regex . ')';
  }
}
