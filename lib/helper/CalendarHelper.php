<?php
/*
 * This file is part of the Sift PHP framework.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
 
/**
 * Returns stylesheets for the calendar
 * 
 * @package Sift
 * @subpackage helper_calendar
 * @param sfCalendar $calendar
 * @return array
 */
function get_stylesheets_for_calendar(sfCalendar $calendar)
{  
  return $calendar->getStylesheets();
}

/**
 * Returns an array of javascripts for the calendar
 *
 * @package Sift
 * @subpackage helper_calendar
 * @param sfCalendar $calendar
 * @return array
 */
function get_javascripts_for_calendar(sfCalendar $calendar)
{  
  return $calendar->getJavascripts();
}