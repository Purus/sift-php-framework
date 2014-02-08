<?php
/*
 * This file is part of the Sift PHP framework.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * sfCalendarRendererIcal renders sfCalendar to ICAL format
 *
 * @package Sift
 * @subpackage calendar
 */
class sfCalendarRendererICal extends sfCalendarRenderer
{
  /**
   * Line esding
   */
  const LINE_ENDING = "\r\n";

  /**
   * Maximum length of the line
   *
   */
  const FOLD_LENGTH = 75;

  /**
   * Calendar name
   *
   * @var string
   */
  protected $name = 'calendar';

  /**
   * Sets calendar name. Used by some of the renderers
   *
   * @param string $name
   * @return sfCalendar
   */
  public function setName($name)
  {
    $this->name = $name;

    return $this;
  }

  /**
   * Returns name
   *
   * @return string
   */
  public function getName()
  {
    return $this->name;
  }

  /**
   * Renders $calendar in iCalendar format
   *
   * @param sfCalendar $calendar
   * @param array $options
   * @return string
   */
  public function render(sfCalendar $calendar, $options = array())
  {
    $events = $calendar->getEvents();

    if (isset($options['name'])) {
      $this->setName($options['name']);
    }

    $calendarName = $this->getName();

    // build ics
    $ics = array();
    $ics[] = 'BEGIN:VCALENDAR';
    $ics[] = 'VERSION:2.0';
    $ics[] = 'METHOD:PUBLISH';
    $ics[] = sprintf('X-WR-CALNAME:%s', $this->renderValue($calendarName));
    $ics[] = 'X-WR-TIMEZONE:UTC';
    $ics[] = sprintf('PRODID:-//sfCalendar v.%s//EN', sfCalendar::VERSION);

    $tz = $calendar->getTimeZone();
    $utc =  new DateTimeZone('UTC');

    $now = new DateTime('@'.time(), $utc);
    $now->setTimezone($tz);

    // add events
    foreach ($events as $event) {
      $eventId = sprintf('%s@%s', $event->getId() ? $event->getId() : md5(serialize($event)),
                          $this->renderValue($calendarName));

      // convert all dates to UTC
      $start = new DateTime('@'.$event->getStart(), $utc);
      $start->setTimezone($tz);

      $end = new DateTime('@'.$event->getStart(), $utc);
      $end->setTimezone($tz);

      $ics[] = 'BEGIN:VEVENT';
      $ics[] = sprintf('UID:%s', $this->renderValue($eventId));
      $ics[] = sprintf('DTSTART:%sT%sZ', $start->format('Ymd'), $start->format('His'));
      $ics[] = sprintf('DTEND:%sT%sZ', $end->format('Ymd'), $end->format('His'));
      $ics[] = sprintf('DTSTAMP:%sT%sZ', $now->format('Ymd'), $now->format('His'));
      $ics[] = sprintf('SUMMARY:%s', $this->renderValue($event->getName()));
      $ics[] = sprintf('DESCRIPTION:%s', $this->renderValue($event->getDescription()));

      if ($url = $event->getUrl()) {
        $ics[] = sprintf('URL;VALUE=URI:%s', $this->renderValue($url));
      }

      if ($location = $event->getLocation()) {
        $ics[] = sprintf('LOCATION:%s', $this->renderValue($location));
      }

      if ($coordinate = $event->getCoordinate()) {
        if ($coordinate instanceof sfGeoCoordinate) {
          $ics[] = sprintf('GEO:%s;%s', $coordinate->getLat(), $coordinate->getLon());
        } else {
          $ics[] = sprintf('GEO:%s;%s', $coordinate[0], $coordinate[1]);
        }
      }

      $ics[] = 'END:VEVENT';
    }

    $ics[] = 'END:VCALENDAR';

    foreach ($ics as &$line) {
      $line = $this->fold($line);
    }

    return join(self::LINE_ENDING, $ics);
  }

  /**
   * Renders a value
   *
   * @param mixed $value
   * @param string $type
   * @return mixed
   */
  protected function renderValue($value, $type = 'text')
  {
    switch (strtolower($type)) {
      case 'text':
        $value = str_replace(",", "\,", $value);
      break;
    }

    return $value;
  }

  /**
   * Text cannot exceed 75 octets. This method will "fold" long lines in accordance with RFC 2445
   *
   * @param string $line
   * @return string
   */
  protected function fold($line)
  {
    $length = sfUtf8::len($line);

    if ($length < (self::FOLD_LENGTH + 1)) {
      return $line;
    }

    $apart = array();
    for ($i = 0; $i < $length; $i += self::FOLD_LENGTH) {
      $apart[] = sfUtf8::sub($line, $i, self::FOLD_LENGTH);
    }

    return implode(self::LINE_ENDING . ' ', $apart);
  }

}
