<?php

require_once(dirname(__FILE__) . '/../../bootstrap/unit.php');
require_once($_test_dir.'/unit/sfContextMock.class.php');

$t = new lime_test(2, new lime_output_color());

$t->diag('sfCalendar');

$calendar = new sfCalendar();
$t->isa_ok($calendar->render(), 'string', '->render() returns string');

$event = new sfCalendarEvent(time(), time() + 150);
$calendar->addEvent($event);

$t->isa_ok($calendar->getEvents(), 'array', 'getEvents() returns array of events');