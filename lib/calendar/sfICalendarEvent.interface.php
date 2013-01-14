<?php
/*
 * This file is part of the Sift PHP framework.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * sfICalendarEvent is an interface for calendar events
 * 
 * @package Sift
 * @subpackage calendar
 */
interface sfICalendarEvent {
  
  public function getStart();
  public function getEnd();
            
}
