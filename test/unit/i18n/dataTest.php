<?php

require_once(dirname(__FILE__).'/../../bootstrap/unit.php');

$t = new lime_test(88, new lime_output_color());

$t->diag('i18n data');
$en = unserialize(file_get_contents(dirname(__FILE__).'/../../../data/i18n/cldr/en.dat'));
$root = unserialize(file_get_contents(dirname(__FILE__).'/../../../data/i18n/cldr/root.dat'));
// merge with root
$en   = sfToolkit::arrayDeepMerge($root, $en);


// check main keys
foreach(array('countries', 'currencies', 'keys', 'languages', 
              'numberSystem', 'numberPatterns', 'scripts', 'types',
              'version', 'calendar', 'timeZones') as $entry)
{
  $t->ok(isset($en[$entry]), sprintf('i18n data files may contain a "%s" entry', $entry));
}

// countries
$t->diag('countries');
$t->is($en['countries']['GB'], 'United Kingdom', '"countries" contains country names');
$t->is($en['countries']['FR'], 'France', '"countries" contains country names');

// Currencies
$t->diag('currencies');

$eur = $en['currencies']['EUR'][0] ? array(
                                      $en['currencies']['EUR'][0],
                                      $en['currencies']['EUR'][1])
                                   : array(
                                      $root['currencies']['EUR'][0],
                                      $en['currencies']['EUR'][1]);

$t->is($eur, array(0 => '€', 1 => 'Euro'), '"Currencies" contains currency name and symbol');

$usd = $en['currencies']['USD'][0] ? array(
                                      $en['currencies']['USD'][0],
                                      $en['currencies']['USD'][1])
                                   : array(
                                      $root['currencies']['USD'][0],
                                      $en['currencies']['USD'][1]);

$t->is($usd, array(0 => '$', 1 => 'US Dollar'), '"Currencies" contains currency names and symbols');

// Languages
$t->diag('languages');
$t->is($en['languages']['fr'], 'French', '"languages" contains language names');
$t->is($en['languages']['en'], 'English', '"languages" contains language names');

// NumberPatterns
$t->diag('numberPatterns');
$t->is($en['numberPatterns'][0], '#,##0.###', '"numberPatterns" contains patterns to format numbers');
$t->is($en['numberPatterns'][1], '¤#,##0.00;(¤#,##0.00)', '"numberPatterns" contains patterns to format numbers');
$t->is($en['numberPatterns'][2], '#,##0%', '"numberPatterns" contains patterns to format numbers');
$t->is($en['numberPatterns'][3], '#E0', '"numberPatterns" contains patterns to format numbers');

// calendar
$t->diag('calendar');
$c = $en['calendar']['gregorian'];

$t->diag('calendar/timeFormats');
$t->is($c['timeFormats']['full'], 'h:mm:ss a zzzz', '"calendar" contains time formats');
$t->is($c['timeFormats']['long'], 'h:mm:ss a z', '"calendar" contains time formats');
$t->is($c['timeFormats']['medium'], 'h:mm:ss a', '"calendar" contains time formats');
$t->is($c['timeFormats']['short'], 'h:mm a', '"calendar" contains time formats');

$t->diag('calendar/dateFormats');
$t->is($c['dateFormats']['full'], 'EEEE, MMMM d, y', '"calendar" contains time formats');
$t->is($c['dateFormats']['long'], 'MMMM d, y', '"calendar" contains time formats');
$t->is($c['dateFormats']['medium'], 'MMM d, y', '"calendar" contains time formats');
$t->is($c['dateFormats']['short'], 'M/d/yy', '"calendar" contains time formats');


$t->diag('calendar/dayNames');
$a = $c['dayNames']['format']['abbreviated'];
foreach (array(0 => 'Sun', 1 => 'Mon', 2 => 'Tue', 3 => 'Wed', 4 => 'Thu', 5 => 'Fri', 6 => 'Sat') as $key => $name)
{
  $t->is($a[$key], $name, '"calendar" contains abrreviated day names');
}

$a = $c['dayNames']['stand-alone']['narrow'];
foreach (array(0 => 'S', 1 => 'M', 2 => 'T', 3 => 'W', 4 => 'T', 5 => 'F', 6 => 'S') as $key => $name)
{
  $t->is($a[$key], $name, '"calendar" contains narrow day names');
}

$a = $c['dayNames']['format']['wide'];
foreach (array(0 => 'Sunday', 1 => 'Monday', 2 => 'Tuesday', 3 => 'Wednesday', 4 => 'Thursday', 5 => 'Friday', 6 => 'Saturday') as $key => $name)
{
  $t->is($a[$key], $name, '"calendar" contains day names');
}

$t->diag('calendar/eras');
$t->is($c['eras'], array(0 => 'BC', 1 => 'AD'), '"calendar" contains era names');

$t->diag('calendar/monthNames');
$a = $c['monthNames']['format']['abbreviated'];
foreach (array(0 => 'Jan', 1 => 'Feb', 2 => 'Mar', 3 => 'Apr', 4 => 'May', 5 => 'Jun', 6 => 'Jul', 7 => 'Aug', 8 => 'Sep', 9 => 'Oct', 10 => 'Nov', 11 => 'Dec') as $key => $name)
{
  $t->is($a[$key], $name, '"calendar" contains abrreviated month names');
}

$a = $c['monthNames']['stand-alone']['narrow'];
foreach (array(0 => 'J', 1 => 'F', 2 => 'M', 3 => 'A', 4 => 'M', 5 => 'J', 6 => 'J', 7 => 'A', 8 => 'S', 9 => 'O', 10 => 'N', 11 => 'D') as $key => $name)
{
  $t->is($a[$key], $name, '"calendar" contains narrow month names');
}

$a = $c['monthNames']['format']['wide'];
foreach (array(0 => 'January', 1 => 'February', 2 => 'March', 3 => 'April', 4 => 'May', 5 => 'June', 6 => 'July', 7 => 'August', 8 => 'September', 9 => 'October', 10 => 'November', 11 => 'December') as $key => $name)
{
  $t->is($a[$key], $name, '"calendar" contains month names');
}


// zoneStrings
$t->diag('timeZones');
$t->is(count($en['timeZones']) > 0, true, '"timeZones" contains time zone names');


// Types

// LocaleScript

// Scripts

// Keys

// Variants

// Version
