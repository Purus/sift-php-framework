<?php
/*
 * This file is part of the Sift PHP framework.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * Calendar is a holder for events in time (day, month, year)
 *
 * @package Sift
 * @subpackage calendar
 */
class sfCalendar
{
  /**
   * Calendar version
   */
  const VERSION = '2.0.0';

  protected $month;
  protected $year;
  protected $renderer;
  protected $events = array();
  protected $timezone;

  /**
   * Constructs the calendar
   *
   * @param integer $month
   * @param integer $year
   * @param string|DateTimeZone $timezone
   */
  public function __construct($month = null, $year = null, $timezone = null)
  {
    $this->month = $month ? $month : date('n');
    $this->year = $year ? $year : date('Y');

    if (is_null($timezone)) {
      $this->setTimeZone(sfConfig::get('sf_default_timezone', 'Europe/Prague'));
    }
  }

  /**
   * Returns calendar timezone
   *
   * @return DateTimeZone
   */
  public function getTimeZone()
  {
    return $this->timezone;
  }

  /**
   * Set timezone
   *
   * @param string $timezone
   * @return sfCalendar
   */
  public function setTimeZone($timezone)
  {
    if (!$timezone instanceof DateTimeZone) {
      $timezone = new DateTimeZone($timezone);
    }
    $this->timezone = $timezone;

    return $this;
  }

  /**
   * Sets renderer to the calendar
   *
   * @param sfICalendarRenderer $renderer
   */
  public function setRenderer(sfICalendarRenderer $renderer)
  {
    $this->renderer = $renderer;
  }

  /**
   * Returns renderer instance
   *
   * @return sfICalendarRenderer
   */
  public function getRenderer()
  {
    if (!$this->renderer) {
      $this->renderer = new sfCalendarRendererHtml();
    }

    return $this->renderer;
  }

  /**
   * Renders the calendar using renderer instance
   *
   * @param array $options
   * @return mixed
   */
  public function render($options = array())
  {
    $renderer = $this->getRenderer();

    return $renderer->render($this, $options);
  }

  /**
   * Returns an array of javascripts
   *
   * @return array
   */
  public function getJavascripts()
  {
    $renderer = $this->getRenderer();
    if (method_exists($renderer, 'getJavascripts')) {
      return $renderer->getJavascripts();
    }

    return array();
  }

  /**
   * Returns an array of stylesheets
   *
   * @return array
   */
  public function getStylesheets()
  {
    $renderer = $this->getRenderer();
    if (method_exists($renderer, 'getStylesheets')) {
      return $renderer->getStylesheets();
    }

    return array();
  }

  /**
   * __toString() magic method.
   *
   * @return string
   */
  public function __toString()
  {
    return $this->render();
  }

  /**
   * Add single event
   *
   * @param array|sfCalendarEvent $event
   * @return sfCalendar
   */
  public function addEvent($event)
  {
    // create an instance of event
    if (!$event instanceof sfICalendarEvent) {
      $event = sfCalendarEvent::fromArray($event);
    }

    $this->events[] = $event;

    return $this;
  }

  /**
   * Add an array of events
   *
   * @param array $events Array of events
   * @return sfCalendar
   */
  public function addEvents($events)
  {
    foreach ($events as $event) {
      $this->addEvent($event);
    }

    return $this;
  }

  /**
   * Returns events
   *
   * @param integer $month
   * @param integer $day
   * @param integer $year
   * @return array
   */
  public function getEvents($month = null, $day = null, $year = null)
  {
    if ($month) {
      $events = array();
      foreach ($this->events as $event) {
        if($event->takesPlace($month ? $month : $this->getMonth(),
                $day, $year ? $year : $this->getYear()))
        {
          $events[] = $event;
        }
      }
    } else {
      $events = $this->events;
    }

    $this->sortEvents($events);

    return $events;
  }

  /**
   * Returns current month
   *
   * @return integer
   */
  public function getMonth()
  {
    return $this->month;
  }

  /**
   * Returns current year
   *
   * @return integer
   */
  public function getYear()
  {
    return $this->year;
  }

  /**
   * Sort events by start date
   *
   * @param array $events
   */
  protected function sortEvents(&$events)
  {
    uasort($events, array($this, '_sortEvents'));
  }

  /**
   * Internal sorting method
   *
   * @param sfCalendarEvent $a
   * @param sfCalendarEvent $b
   * @return int
   * @see sortEvents()
   */
  protected function _sortEvents($a, $b)
  {
    if ($a->getStart() < $b->getStart()) {
      return -1;
    } elseif ($a->getStart() > $b->getStart()) {
      return 1;
    }

    return 0;
  }

}
