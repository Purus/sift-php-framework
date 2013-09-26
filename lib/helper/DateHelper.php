<?php

/*
 * This file is part of the Sift PHP framework.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * Date helpers
 *
 * @package Sift
 * @subpackage helper_date
 *
 */

/**
 * Formats the date range
 *
 * @param string $startDate The start date
 * @param string $endDate The end date
 * @param string $format Format
 * @param string $fullText The string for sprintf() Should contain 2 (%s) placeholdes for $startDate and $endDate
 * @param string $startText The string for sprintf(). Should contain 1 placeholder
 * @param string $endText The string for sprintf(). Should contain 1 placeholder
 * @param string $culture The culture
 * @param string $charset The charset
 * @return string
 */
function format_daterange($startDate, $endDate, $format = 'd',
    $fullText = '%s - %s', $startText = '%s', $endText = '%s',
    $culture = null, $charset = null)
{
  if($startDate && $endDate)
  {
    return sprintf($fullText, format_date($startDate, $format, $culture, $charset),
                              format_date($endDate, $format, $culture, $charset));
  }
  else if($startDate)
  {
    return sprintf($startText, format_date($startDate, $format, $culture, $charset));
  }
  else if($endDate)
  {
    return sprintf($endText, format_date($endDate, $format, $culture, $charset));
  }
}

/**
 * Formats the date
 *
 * @staticvar array $dateFormats
 * @param string $date The date to format
 * @param string $format The format
 * @param string $culture The culture
 * @param string $charset The charset
 * @return string
 */
function format_date($date, $format = 'd', $culture = null, $charset = null)
{
  static $dateFormats = array();

  if(is_null($date))
  {
    return null;
  }

  if(is_null($culture))
  {
    $culture = sfContext::getInstance()->getUser()->getCulture();
  }

  if(!$charset)
  {
    $charset = sfConfig::get('sf_charset');
  }

  if(!isset($dateFormats[$culture]))
  {
    $dateFormats[$culture] = new sfI18nDateFormatter($culture);
  }

  return $dateFormats[$culture]->format($date, $format, null, $charset);
}

/**
 * Formats the date time
 *
 * @param string $date The date to format
 * @param string $format The format
 * @param string $culture The culture
 * @param string $charset The charser
 * @return string
 */
function format_datetime($date, $format = 'F', $culture = null, $charset = null)
{
  return format_date($date, $format, $culture, $charset);
}

/**
 * Returns the distance of time in human readable format
 *
 * @param integer $from_time
 * @param integer $to_time
 * @param boolean $include_seconds
 * @return string
 */
function distance_of_time_in_words($from_time, $to_time = null, $include_seconds = false)
{
  $to_time = $to_time ? $to_time : time();

  $distance_in_minutes = floor(abs($to_time - $from_time) / 60);
  $distance_in_seconds = floor(abs($to_time - $from_time));

  $string = '';
  $parameters = array();

  if($distance_in_minutes <= 1)
  {
    if(!$include_seconds)
    {
      $string = $distance_in_minutes == 0 ? 'less than a minute' : '1 minute';
    }
    else
    {
      if($distance_in_seconds <= 5)
      {
        $string = 'less than 5 seconds';
      }
      else if($distance_in_seconds >= 6 && $distance_in_seconds <= 10)
      {
        $string = 'less than 10 seconds';
      }
      else if($distance_in_seconds >= 11 && $distance_in_seconds <= 20)
      {
        $string = 'less than 20 seconds';
      }
      else if($distance_in_seconds >= 21 && $distance_in_seconds <= 40)
      {
        $string = 'half a minute';
      }
      else if($distance_in_seconds >= 41 && $distance_in_seconds <= 59)
      {
        $string = 'less than a minute';
      }
      else
      {
        $string = '1 minute';
      }
    }
  }
  else if($distance_in_minutes >= 2 && $distance_in_minutes <= 44)
  {
    $string = '%minutes% minutes';
    $parameters['%minutes%'] = $distance_in_minutes;
  }
  else if($distance_in_minutes >= 45 && $distance_in_minutes <= 89)
  {
    $string = 'about 1 hour';
  }
  else if($distance_in_minutes >= 90 && $distance_in_minutes <= 1439)
  {
    $string = 'about %hours% hours';
    $parameters['%hours%'] = round($distance_in_minutes / 60);
  }
  else if($distance_in_minutes >= 1440 && $distance_in_minutes <= 2879)
  {
    $string = '1 day';
  }
  else if($distance_in_minutes >= 2880 && $distance_in_minutes <= 43199)
  {
    $string = '%days% days';
    $parameters['%days%'] = round($distance_in_minutes / 1440);
  }
  else if($distance_in_minutes >= 43200 && $distance_in_minutes <= 86399)
  {
    $string = 'about 1 month';
  }
  else if($distance_in_minutes >= 86400 && $distance_in_minutes <= 525959)
  {
    $string = '%months% months';
    $parameters['%months%'] = round($distance_in_minutes / 43200);
  }
  else if($distance_in_minutes >= 525960 && $distance_in_minutes <= 1051919)
  {
    $string = 'about 1 year';
  }
  else
  {
    $string = 'over %years% years';
    $parameters['%years%'] = floor($distance_in_minutes / 525960);
  }

  return __($string, $parameters, '%SF_SIFT_DATA_DIR%/i18n/catalogues/time_distance');
}

/**
 * Like distance_of_time_in_words, but where to_time is fixed to time()
 *
 * @param integer $from_time The time
 * @param boolean $include_seconds
 * @return string
 */
function time_ago_in_words($from_time, $include_seconds = false)
{
  return distance_of_time_in_words($from_time, time(), $include_seconds);
}
