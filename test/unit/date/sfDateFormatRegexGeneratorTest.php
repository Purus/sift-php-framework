<?php

require_once(dirname(__FILE__) . '/../../bootstrap/unit.php');

$t = new lime_test(1, new lime_output_color());

$t->todo('convert tests to lime');

//
///**
// * @file
// *  Tests for the <code>DateFormatRegexGenerator</code> and
// *  <code>CapturingDateFormatRegexGenerator</code> classes.
// *
// *  ř 2010 Red Bottle Design, LLC. All rights reserved.
// *
// *  http://www.redbottledesign.com
// *
// *  This source code is free software: you can redistribute it and/or modify
// *  it under the terms of the Lesser GNU General Public License as published by
// *  the Free Software Foundation, either version 3 of the License, or
// *  (at your option) any later version.
// *
// *  This program is distributed in the hope that it will be useful,
// *  but WITHOUT ANY WARRANTY; without even the implied warranty of
// *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// *  Lesser GNU General Public License for more details.
// *
// *  You should have received a copy of the Lesser GNU General Public License
// *  along with this program.  If not, see <http://www.gnu.org/licenses/>.
// *
// * @author Guy Paddock (guy.paddock@redbottledesign.com)
// */
//
//// Local includes
//require_once('../DateFormatRegexGenerator.class.inc');
//require_once('../CapturingDateFormatRegexGenerator.class.inc');
//
//// PHPUnit includes
//require_once('PHPUnit/Framework.php');
//
///**
// * Unit tests for the DateFormatRegexGenerator and
// * CapturingDateFormatRegexGenerator classes.
// *
// * @author Guy Paddock (guy.paddock@redbottledesign.com)
// */
//class DateFormatRegexGeneratorTest
//extends PHPUnit_Framework_TestCase
//{
//  /**
//   * Tests the patterns of DateFormatRegexGenerator::generateRegex() for
//   * recognition of days, weeks, months, hours, minutes, seconds, and timezones.
//   *
//   * @dataProvider dateFormatTestArgumentProvider
//   *
//   * @param string  $formatChar
//   *                The time format string for which a regex is being tested.
//   *
//   * @param string  $dateString
//   *                The date / time string that is being used to test the regex.
//   */
//  public function testGenerateRegex($formatChar, $dateString)
//  {
//    // DateFormatRegexGenerator
//    $regexGenerator = DateFormatRegexGenerator::getInstance();
//    $regex          = $regexGenerator->generateRegex($formatChar, TRUE);
//
//    preg_match($regex, $dateString, $matches);
//
//    $this->assertTrue(!empty($matches));
//
//    $matchedValue = $matches[0];
//
//    $this->assertEquals($dateString, $matchedValue);
//
//    // CapturingDateFormatRegexGenerator
//    $regexGenerator = CapturingDateFormatRegexGenerator::getInstance();
//    $regex          = $regexGenerator->generateRegex($formatChar, TRUE);
//
//    preg_match($regex, $dateString, $matches);
//
//    $this->assertTrue(!empty($matches));
//
//    $matchedValue = $matches[1];
//
//    $this->assertEquals($dateString, $matchedValue);
//  }
//
//  /**
//   * Data provider for <code>testGenerateRegex()</code>.
//   *
//   * This generates all combinations of format strings and date strings that
//   * are test-worthy.
//   */
//  public function dateFormatTestArgumentProvider()
//  {
//    $arguments = $this->generateDaysWeeksAndMonthsTestArguments();
//    $arguments = array_merge($arguments, $this->generateYearsTestArguments());
//    $arguments = array_merge($arguments, $this->generateHoursTestArguments());
//    $arguments = array_merge($arguments, $this->generateMinutesTestArguments());
//    $arguments = array_merge($arguments, $this->generateSecondsTestArguments());
//    $arguments = array_merge($arguments, $this->generateTimeZonesTestArguments());
//
//    return $arguments;
//  }
//
//  /**
//   * Method called by <code>dateFormatTestArgumentProvider()</code> to generate
//   * test-worthy combinations of date format string and day of the year for use
//   * as data in the <code>testGenerateRegex()</code> test.
//   */
//  protected function generateDaysWeeksAndMonthsTestArguments()
//  {
//    $formatChars =
//      array
//      (
//        'd',
//        'D',
//        'j',
//        'l',
//        'N',
//        'S',
//        'w',
//        'z',
//        'W',
//        'F',
//        'm',
//        'M',
//        'n',
//        't',
//
//        /* Common tests */
//        'c',
//        'r',
//        'U'
//      );
//
//    $arguments = array();
//
//    $date = new DateTime('2010-01-01');
//
//    foreach ($formatChars as $formatChar)
//    {
//      for ($dayIndex = 0; $dayIndex < 365; ++$dayIndex)
//      {
//        $arguments[] = array($formatChar, $date->format($formatChar));
//
//        $date->modify('+1 day');
//      }
//    }
//
//    return $arguments;
//  }
//
//  /**
//   * Method called by <code>dateFormatTestArgumentProvider()</code> to generate
//   * test-worthy combinations of date format string and year for use as data in
//   * the <code>testGenerateRegex()</code> test.
//   */
//  protected function generateYearsTestArguments()
//  {
//    $formatChars =
//      array
//      (
//        'L',
//        'o',
//        'Y',
//        'y',
//
//        /* Common tests */
//        'c',
//        'r',
//        'U'
//      );
//
//    $arguments = array();
//
//    foreach ($formatChars as $formatChar)
//    {
//      for ($year = 1000; $year < 9999; ++$year)
//      {
//        $date = new DateTime("January 1st, $year");
//
//        $arguments[] = array($formatChar, $date->format($formatChar));
//      }
//    }
//
//    return $arguments;
//  }
//
//  /**
//   * Method called by <code>dateFormatTestArgumentProvider()</code> to generate
//   * test-worthy combinations of date format string and hour for use as data in
//   * the <code>testGenerateRegex()</code> test.
//   */
//  protected function generateHoursTestArguments()
//  {
//    $formatChars =
//      array
//      (
//        'a',
//        'A',
//        'B',
//        'g',
//        'G',
//        'h',
//        'H',
//
//        /* Common tests */
//        'c',
//        'r',
//        'U'
//      );
//
//    $arguments = array();
//
//    foreach ($formatChars as $formatChar)
//    {
//      $date = new DateTime("2010-01-01 12:00 AM");
//
//      for ($hour = 0; $hour < 24; ++$hour)
//      {
//        $arguments[] = array($formatChar, $date->format($formatChar));
//
//        $date->modify('+1 hour');
//      }
//    }
//
//    return $arguments;
//  }
//
//  /**
//   * Method called by <code>dateFormatTestArgumentProvider()</code> to generate
//   * test-worthy combinations of date format string and minute for use as data
//   * in the <code>testGenerateRegex()</code> test.
//   */
//  protected function generateMinutesTestArguments()
//  {
//    $formatChars =
//      array
//      (
//        'B',
//        'i',
//
//        /* Common tests */
//        'c',
//        'r',
//        'U'
//      );
//
//    $arguments = array();
//
//    foreach ($formatChars as $formatChar)
//    {
//      $date = new DateTime("2010-01-01 12:00 AM");
//
//      for ($minute = 0; $minute < 60; ++$minute)
//      {
//        $arguments[] = array($formatChar, $date->format($formatChar));
//
//        $date->modify('+1 minute');
//      }
//    }
//
//    return $arguments;
//  }
//
//  /**
//   * Method called by <code>dateFormatTestArgumentProvider()</code> to generate
//   * test-worthy combinations of date format string and second for use as data
//   * in the <code>testGenerateRegex()</code> test.
//   */
//  protected function generateSecondsTestArguments()
//  {
//    $formatChars =
//      array
//      (
//        's',
//
//        /* Common tests */
//        'c',
//        'r',
//        'U'
//      );
//
//    $arguments = array();
//
//    foreach ($formatChars as $formatChar)
//    {
//      $date = new DateTime("2010-01-01 12:00 AM");
//
//      for ($second = 0; $second < 120; ++$second)
//      {
//        $arguments[] = array($formatChar, $date->format($formatChar));
//
//        $date->modify('+1 second');
//      }
//    }
//
//    return $arguments;
//  }
//
//  /**
//   * Method called by <code>dateFormatTestArgumentProvider()</code> to generate
//   * test-worthy combinations of date format string and time zone for use as
//   * data in the <code>testGenerateRegex()</code> test.
//   */
//  protected function generateTimeZonesTestArguments()
//  {
//    $formatChars =
//      array
//      (
//        'e',
//        'I',
//        'O',
//        'P',
//        'T',
//        'Z',
//
//        /* Common tests */
//        'c',
//        'r',
//        'U'
//      );
//
//    $arguments = array();
//
//    $timeZoneIds  = DateTimeZone::listIdentifiers();
//
//    // There are certain invalid time zones in the list that we want to skip
//    $unwantedIds  = array('Factory');
//
//    $timeZoneIds  = array_diff($timeZoneIds, $unwantedIds);
//
//    foreach ($formatChars as $formatChar)
//    {
//      $date = new DateTime("2010-01-01 12:00 AM EST");
//
//      foreach ($timeZoneIds as $timeZoneId)
//      {
//        $timeZone = new DateTimeZone($timeZoneId);
//
//        $date->setTimezone($timeZone);
//
//        $arguments[] = array($formatChar, $date->format($formatChar));
//      }
//    }
//
//    return $arguments;
//  }
//
//  /**
//   * Tests how DateFormatRegexGenerator::generateRegex() handles characters in
//   * the time format string that have significance in regular expression syntax.
//   * The documentation for the PHP function <code>preg_quote</code> describes
//   * which characters are considered special.
//   *
//   * The function should escape such special characters so that they do
//   * not impact the matching ability of the resulting regular expression.
//   *
//   * @see http://us3.php.net/manual/en/function.preg-quote.php
//   *
//   * @dataProvider specialCharTestArgumentProvider
//   *
//   * @param string  $formatString
//   *                The time format string for which a regex is being tested.
//   *
//   * @param string  $dateString
//   *                The date / time string that is being used to test the regex.
//   */
//  public function testGenerateRegexForSpecialChars($formatString, $dateString)
//  {
//    $regexGenerator = CapturingDateFormatRegexGenerator::getInstance();
//    $regex          = $regexGenerator->generateRegex($formatString, TRUE);
//
//    preg_match($regex, $dateString, $matches);
//
//    $this->assertTrue(!empty($matches));
//
//    $month = $matches[1];
//    $day   = $matches[2];
//    $year  = $matches[3];
//
//    $this->assertEquals(date('m'), $month);
//    $this->assertEquals(date('d'), $day);
//    $this->assertEquals(date('Y'), $year);
//  }
//
//  /**
//   * Data provider for <code>testGenerateRegexForSpecialChars()</code>.
//   *
//   * This generates all test-worthy combinations of format strings containing
//   * special characters and their corresponding date strings.
//   */
//  public function specialCharTestArgumentProvider()
//  {
//    $specialFormatStrings =
//      array
//      (
//        '\\D\\a\\y: m d, Y',
//        'mdY m\\d\\Y',
//        'm\\\\d\\\\Y',
//        'm/d/Y',
//        '+1 m +d, Y',
//        '*m d, Y*',
//        '1 m d, Y?',
//        '[m d, Y]',
//        '^m d, Y^',
//        '$m $d $Y',
//        'm d (Y)',
//        '{m d, Y}',
//        'm + d = Y',
//        '!m d! !Y!',
//        '<m d> <Y>',
//        'm d | Y',
//        '::m::d::Y::',
//        'm-d-Y'
//      );
//
//    $arguments = array();
//
//    foreach ($specialFormatStrings as $specialFormatString)
//    {
//      $arguments[] =
//        array
//        (
//          $specialFormatString,
//          date($specialFormatString)
//        );
//    }
//
//    return $arguments;
//  }
//}