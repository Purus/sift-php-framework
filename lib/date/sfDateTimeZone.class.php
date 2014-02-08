<?php
/*
 * This file is part of the Sift PHP framework.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * sfDateTimeZone class.
 *
 * A class for representing a timezone
 *
 * @package Sift
 * @subpackage date
 */
class sfDateTimeZone extends DateTimeZone {

  /**
   * Validates the timezone name
   *
   * @param string $timezone
   * @return boolean
   */
  public static function isValid($timezone)
  {
    try
    {
      new DateTimeZone($timezone);
    }
    catch(Exception $e)
    {
      return false;
    }
    return true;
  }

  /**
   * Converts a timezone hourly offset to its timezone's name.
   * @example $offset = -5, $isDst = 0 <=> return value = 'America/New_York'
   *
   * @param float $offset The timezone's offset in hours.
   *                      Lowest value: -12 (Pacific/Kwajalein)
   *                      Highest value: 14 (Pacific/Kiritimati)
   * @param boolean $dayLightSavings Is the offset for the timezone when it's in daylight savings time?
   *
   * @return string|false The name of the timezone: 'Asia/Tokyo', 'Europe/Paris', of false when the offset is invalid
   */
  public static function getNameFromOffset($offset, $dayLightSavings = null)
  {
    if($dayLightSavings === null)
    {
      $dayLightSavings = date('I');
    }

    if(!is_numeric($offset))
    {
      return false;
    }

    $dayLightSavings = (boolean)$dayLightSavings;

    $offset *= 3600;
    $zone = timezone_name_from_abbr('', $offset, $dayLightSavings);

    // fallback if the zone failed
    if($zone === false)
    {
      foreach(timezone_abbreviations_list() as $abbr)
      {
        foreach($abbr as $city)
        {
          if((boolean) $city['dst'] === $dayLightSavings &&
              strlen($city['timezone_id']) > 0 &&
              $city['offset'] == $offset)
          {
            $zone = $city['timezone_id'];
            break 2;
          }
        }
      }
    }
    return $zone;
  }

}