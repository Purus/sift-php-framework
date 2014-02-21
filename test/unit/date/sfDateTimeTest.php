<?php

require_once(dirname(__FILE__) . '/../../bootstrap/unit.php');

$t = new lime_test(14, new lime_output_color());

date_default_timezone_set('Europe/Prague');

// FIXME: make more tests!

$date = sfDate::getInstance('2012-12-12 00:00:00');

$t->isa_ok($date, 'sfDate', 'getInstance() returns sfDate object');

// 1 is monday
$fd = $date->firstDayOfWeek(1);

$t->is($fd->dump(), '2012-12-10 00:00:00', 'firstDayOfWeek() works ok.');

// without specification of first day
$t->is(sfDate::getInstance('2012-12-12 00:00:00')->firstDayOfWeek()
        ->dump(), '2012-12-09 00:00:00', 'firstDayOfWeek() works ok.');

// without specification of first day
$t->is(sfDate::getInstance('2012-12-12 00:00:00')->firstDayOfWeek('cs_CZ')
        ->dump(), '2012-12-10 00:00:00', 'firstDayOfWeek() works ok.');

// without specification of first day
$t->is(sfDate::getInstance('2012-12-12 00:00:00')->firstDayOfWeek('en_GB')
        ->dump(), '2012-12-09 00:00:00', 'firstDayOfWeek() works ok.');

$date->addMonth(1)->firstDayOfMonth();

$t->is($fd->dump(), '2013-01-01 00:00:00', 'firstDayOfMonth() works ok.');

$t->diag('sfDateTimeToolkit');

$timestamp = $fd->getTs();

$t->is(sfDateTimeToolkit::getTS($timestamp), date('U', strtotime('2013-01-01 00:00:00')), 'getTS works ok for timestamp');

$datetime = new DateTime('2012-12-10 00:00:00');

$t->is(sfDateTimeToolkit::getTS($datetime), $datetime->format('U'), 'getTS works ok for DateTime objects');

// formatting the values
$t->diag('format');

$t->is(sfDate::getInstance('2012-12-12 00:00:00 +0100')->format(DATE_RFC2822), 'Wed, 12 Dec 2012 00:00:00 +0100', 'format() works ok for RFC2822 date format.');

$t->is(sfDate::getInstance('2012-12-12 00:00:00 +0100')->format(DATE_W3C), '2012-12-12T00:00:00+01:00', 'format() works ok for W3C date format.');

$t->diag('__toString()');

$t->is((string)sfDate::getInstance('2012-12-12 00:00:00'), '2012-12-12T00:00:00+0100', '_-toString() works ok.');

// serialize

$date = sfDate::getInstance('2012-12-12 00:00:00');
$serialized = serialize($date);
$t->isa_ok($serialized, 'string', 'serializing of the sfDate object works ok');

$unserialized = unserialize($serialized);
$t->isa_ok($unserialized, 'sfDate', 'unserializing of the serialized object returns sfDate object');

$t->is($unserialized->format('d.m.Y H:i:s'), '12.12.2012 00:00:00', 'serializing of the sfDate object works ok');
