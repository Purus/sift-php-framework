<?php
/*
 * This file is part of the Sift PHP framework.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * sfCalendarEvent represent an event in time
 *
 * @package    Sift
 * @subpackage calendar
 */
class sfCalendarEvent implements sfICalendarEvent, ArrayAccess
{
    protected $start, $end;
    protected $data = array();

    /**
     * Constructs the event
     *
     * @param integer|string $start Unix timestamp of start, string (will be converted to timestamp)
     * @param integer|string $end   Unix timestamp of end, string (will be converted to timestamp)
     * @param array          $data  Array of event data (title, location, geo, ...)
     *
     * @throws InvalidArgumentException
     */
    public function __construct($start, $end, $data = array())
    {
        $this->start = sfDateTimeToolkit::getTS($start);
        $this->end = sfDateTimeToolkit::getTS($end);

        if (!$this->start) {
            throw new InvalidArgumentException('Start is invalid');
        }

        $this->data = $data;
    }

    /**
     * Returns event start
     *
     * @return integer
     */
    public function getStart()
    {
        return $this->start;
    }

    /**
     * Returns event end
     *
     * @return integer
     */
    public function getEnd()
    {
        return $this->end;
    }

    /**
     * Takes this event in given time?
     *
     * @param integer $month
     * @param integer $day
     * @param integer $year
     *
     * @return boolean
     */
    public function takesPlace($month, $day, $year)
    {
        $timestamp = mktime(0, 0, 0, $month, 1, $year);

        // we are looking for month
        if (!$day) {
            // number of days in the month
            $day = date('t', $timestamp);
            $start = mktime(0, 0, 0, $month, 1, $year);
            $end = mktime(23, 59, 59, $month, $day, $year);
        } else {
            $start = mktime(0, 0, 0, $month, $day, $year);
            $end = mktime(23, 59, 59, $month, $day, $year);
        }

        if ($this->getStart() >= $start && $this->getStart() <= $end) {
            return true;
        } elseif ($start > $this->getStart() && $start < $this->getEnd()) {
            return true;
        }

        return false;
    }

    /**
     * Returns event duration in seconds
     *
     * @return integer
     */
    public function getDuration()
    {
        return $this->end - $this->start;
    }

    public function __call($methodName, $args)
    {
        if (preg_match('~^(set|get)([A-Z])(.*)$~', $methodName, $matches)) {
            $property = strtolower($matches[2]) . $matches[3];

            switch ($matches[1]) {
                case 'set':
                    $this->data[$property] = $args[0];

                    return $this;
                    break;

                case 'get':
                    return isset($this->data[$property]) ? $this->data[$property] : null;
                    break;

                case 'default':
                    // FIXME: possible usage of sfEvent system!
                    throw new sfException(sprintf('Method "%s" does not exist', $methodName));
                    break;
            }
        }
    }

    public function toArray()
    {
        return $this->data;
    }

    public function __toString()
    {
        $return = array();
        if (isset($this->data['name'])) {
            $return[] = $this->data['name'];
        }

        return join(' ', $return);
    }

    public static function fromArray($array)
    {
        if (isset($array['start'])) {
            $start = $array['start'];
            unset($array['start']);
        }

        if (isset($array['end'])) {
            $end = $array['end'];
            unset($array['end']);
        }

        if (!$start || !$end) {
            throw new sfCalendarException('Calendar event is missing either start or end information.');
        }

        return new self($start, $end, $array);
    }

    public function current()
    {
        return current($this->_data);
    }

    public function next()
    {
        return next($this->_data);
    }

    public function key()
    {
        return key($this->_data);
    }

    public function valid()
    {
        return $this->current() !== false;
    }

    public function rewind()
    {
        return reset($this->_data);
    }

    public function offsetExists($name)
    {
        return isset($this->_data[$name]);
    }

    public function offsetGet($name)
    {
        return $this->_data[$name];
    }

    public function offsetSet($name, $value)
    {
        return $this->_data[$name] = $value;
    }

    public function offsetUnset($name)
    {
        unset($this->_data[$name]);
    }

}
