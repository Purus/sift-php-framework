<?php
/*
 * This file is part of the Sift PHP framework.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * sfCalendarRendererHtml renders sfCalendar to HTML code
 *
 * @package    Sift
 * @subpackage calendar
 */
class sfCalendarRendererHtml extends sfCalendarRenderer
{
    /**
     * Options holder
     *
     * @var array
     */
    protected $options
        = array(
            'date_format'       => 'f',
            'culture'           => 'en_GB',
            'day_event_route'   => '',
            'month_event_route' => '',
            // table id
            'id'                => '',
            // bootstrap styling
            'class'             => 'calendar table table-striped table-bordered',
        );

    /**
     * Renders the calendar
     *
     * @param sfCalendar $calendar
     * @param array      $options
     *
     * @return string
     */
    public function render(sfCalendar $calendar, $options = array())
    {
        $this->setOptions($options);

        $culture = $this->getOption('culture');
        $format = $this->getOption('date_format');

        // load culture specific data
        $dateFormatInfo = sfI18nDateTimeFormat::getInstance($culture);
        $dateFormat = new sfI18nDateFormatter($dateFormatInfo);
        // month names
        $monthNames = $dateFormatInfo->getStandAloneMonthNames();
        // day names (first is allways sunday)
        $dayNames = $dateFormatInfo->getAbbreviatedDayNames();
        // first day of week
        $firstDayOfWeek = $dateFormatInfo->getFirstDayOfWeek();

        $month = $calendar->getMonth();
        $year = $calendar->getYear();
        $day = 1;
        // current timestamp
        $timeStamp = mktime(0, 0, 0, $month, $day, $year);
        // number of days in the current month
        $daysInMonth = date('t', $timeStamp);
        // start day
        $startDay = date('N', $timeStamp) - $firstDayOfWeek;
        $today = mktime(0, 0, 0, date('n'), date('j'), date('Y'));

        $calendarId = $this->getOption('id');
        $calendarClass = $this->getOption('class');

        // generate output
        $html = array();
        $html[] = sprintf(
            "<table%s%s>\n  <thead>",
            ($calendarId ? sprintf(' id="%s"', $calendarId) : ''),
            ($calendarClass ? sprintf(' class="%s"', $calendarClass) : '')
        );

        // month name
        $monthName = $monthNames[$month - 1];
        $monthEventRoute = $this->getOption('month_event_route');
        if ($monthEventRoute) {
            $previous = sfTime::subtract($timeStamp, 1, sfTime::MONTH);
            $previousMonth = date('n', $previous);
            $previousYear = date('Y', $previous);

            $html[] = sprintf(
                '<tr><th colspan="2" class="center"><a href="%s" class="btn btn-small"><i class="icon-chevron-left"></i> <span class="hide">%s</span></a></th>',
                $this->generateUrl(
                    strtr($monthEventRoute, array('%month%' => $previousMonth, '%year%' => $previousYear))
                ),
                $this->__('previous month')
            );

            $html[] = sprintf('<th colspan="3">%s %s</th>', $monthName, $year);

            $next = sfTime::add($timeStamp, 1, sfTime::MONTH);
            $nextMonth = date('n', $next);
            $nextYear = date('Y', $next);

            $html[] = sprintf(
                '<th colspan="2" class="center"><a href="%s" class="btn btn-small"><i class="icon-chevron-right"></i> <span class="hide">%s</span></a></th></tr>',
                $this->generateUrl(
                    strtr($monthEventRoute, array('%month%' => $nextMonth, '%year%' => $nextYear))
                ),
                $this->__('next month')
            );
        } else {
            $html[] = sprintf('<tr><th colspan="7">%s %s</th></tr>', $monthName, $year);
        }

        $html[] = '<tr>';

        // day names
        foreach ($this->reformatDayNames($dayNames, $firstDayOfWeek) as $k => $dayName) {
            $html[] = sprintf('    <th>%s</th>', $dayName);
        }

        $html[] = "  </tr>\n</thead>";
        $html[] = "<tbody>\n";

        // we are not starting teh current month in the first column
        if ($startDay != 0) {
            $html[] = '<tr>';
        }

        // previous months days
        for ($e = 0; $e <= $startDay; $e++) {
            $diff = $startDay - $e;
            if ($diff > 0) {
                $previous = sfTime::subtract($timeStamp, $diff, sfTime::DAY);
                $html[] = sprintf('<td class="muted">%s</td>', date('j', $previous));
            }
            if ($e > 0 && $e % 7 == 0) {
                $html[] = '</tr>';
            }
        }

        for ($i = 1; $i <= $daysInMonth; $i++) {
            $day = $i;
            $timeStamp = mktime(0, 0, 0, $month, $day, $year);

            $class = array();

            if ($timeStamp == $today) {
                $class[] = 'today';
            }

            if (($startDay + $day) % 7 == 0) {
                $class[] = 'last-day-of-week';
            }

            $events = $calendar->getEvents($month, $day, $year);
            $numberEvents = count($events);
            $hasEvents = $numberEvents > 0;

            if ($hasEvents) {
                $class[] = 'has-events';
            }

            if ($hasEvents) {
                $dayEventRoute = $this->getOption('day_event_route');
                $link = $dayEventRoute ?
                    $this->generateUrl(
                        strtr(
                            $dayEventRoute,
                            array('%day%' => $day, '%month%' => $month, '%year%' => $year)
                        )
                    ) : '#';

                if ($numberEvents > 1) {
                    $title = $this->__('Total %total%', array('%total%' => count($events)));
                } else {
                    $title = $events[0]->__toString();
                }

                $html[] = sprintf(
                    '    <td%s><a title="%s" href="%s">%s</a>',
                    (count($class) ?
                        sprintf(' class="%s"', join(' ', $class)) : ''),
                    $title,
                    $link,
                    $day
                );

//        $html[] = sprintf('    <td%s><div class="dropdown"><a title="%s" class="dropdown-toggle" data-toggle="dropdown" data-target="#" href="%s">%s</a>', (count($class) ?
//                sprintf(' class="%s"', join(' ', $class)) : ''), $title, $link, $day);

//        $html[] = '<ul class="dropdown-menu" role="menu">';
//
//        foreach($events as $event)
//        {
//          $url = $event->getUrl();
//          if(!$url && ($route = $event->getRoute()))
//          {
//            $url = $this->generateUrl($route);
//          }
//          if($url)
//          {
//            $html[] = sfHtml::contentTag('li', sfHtml::contentTag('a', $event->__toString(),
//                      array(
//                        'href' => $url,
//                        'title' => $this->__('Event starts at %start_date%', array('%start_date%' => $dateFormat->format($event->getStart(), $format)))
//                      )), array(
//                        'role' => 'menuitem'
//                      ));
//          }
//          else
//          {
//            $html[] = sprintf('<li role="menuitem"><a class="event">%s <br /><span class="muted">%s</span></a></li>',
//                    $event->__toString(),
//                    $this->__('Event starts at %start_date%', array('%start_date%' => $dateFormat->format($event->getStart(), $format))));
//          }
//        }
//
//        $html[] = '</ul></div>';
                $html[] = '</td>';
            } else {
                $html[] = sprintf(
                    '    <td%s>%s</td>',
                    (count($class) ?
                        sprintf(' class="%s"', join(' ', $class)) : ''),
                    $day
                );
            }

            if (($startDay + $day) % 7 == 0 && $day != $daysInMonth) {
                $html[] = '</tr><tr>';
            }
        }

        // next months days
        for ($e2 = 1; $e2 < (7 - (($startDay + $daysInMonth - 1) % 7)); $e2++) {
            $next = sfTime::add($timeStamp, $e2, sfTime::DAY);
            $html[] = sprintf('<td class="muted">%s</td>', date('j', $next));
        }

        $html[] = '</tr></tbody></table>';

        return join("\n", $html);
    }

    /**
     * Reformats day names based on the first day of week
     *
     * @return void
     */
    protected function reformatDayNames($dayNames, $firstDayOfWeek)
    {
        for ($i = 0; $i < $firstDayOfWeek; $i++) {
            array_push($dayNames, array_shift($dayNames));
        }

        return $dayNames;
    }

}
