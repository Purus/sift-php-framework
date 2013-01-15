<?php

require_once(dirname(__FILE__).'/../../bootstrap/unit.php');

$t = new lime_test(34, new lime_output_color());

$windows1250 = file_get_contents(dirname(__FILE__) . '/fixtures/windows_1250.txt');
$utf8        = file_get_contents(dirname(__FILE__) . '/fixtures/utf8.txt');
$iso88592    = file_get_contents(dirname(__FILE__) . '/fixtures/iso88592.txt');
$iso88591    = file_get_contents(dirname(__FILE__) . '/fixtures/iso88591.txt');

$t->diag('->ascii()');

$input  = array(' á ', 'čeněk');
$output = array(' a ', 'cenek');
foreach($input as $k => $i)
{
  $t->is(sfUtf8::ascii($i), $output[$k], sprintf('sfUtf8::ascii() works ok for "%s"', $i));
}

$t->diag('->isUtf8()');

$input  = array($windows1250, $utf8, $iso88592);
$output = array(false, true, false);

$t->isa_ok(sfUtf8::isUtf8($input[0]), 'boolean', 'sfUtf8::isUtf8() returns boolean');

foreach($input as $k => $i)
{
  $t->is(sfUtf8::isUtf8($i), $output[$k], sprintf('sfUtf8::isUtf8() works ok for "%s"', $i));
}

$t->diag('->chr()');

// http://www.utf8-chartable.de/
$input  = array('U+003A', 'U+003F');
$output = array(':', '?');

foreach($input as $k => $i)
{
  $t->is(sfUtf8::chr($i), $output[$k], sprintf('sfUtf8::chr() works ok for "%s"', $i));
}

$t->diag('->clean()');

$invalidUtf8 = file_get_contents(dirname(__FILE__) . '/fixtures/invalid.txt');
$invalidCleaned = file_get_contents(dirname(__FILE__) . '/fixtures/invalid_cleaned.txt');

$input  = array($invalidUtf8);
$output = array($invalidCleaned);

foreach($input as $k => $i)
{
  $t->is(sfUtf8::clean($i), $output[$k], sprintf('sfUtf8::clean() works ok for "%s"', $i));
}

$t->diag('->upper()');

$input  = array('A', 'čeněk');
$output = array('A', 'ČENĚK');
foreach($input as $k => $i)
{
  $t->is(sfUtf8::upper($i), $output[$k], sprintf('sfUtf8::upper() works ok for "%s"', $i));
}

$t->diag('->lower()');

$input = array('A', 'ČENĚK', 'ŽížAla');
$output  = array('a', 'čeněk', 'žížala');

foreach($input as $k => $i)
{
  $t->is(sfUtf8::lower($i), $output[$k], sprintf('sfUtf8::lower() works ok for "%s"', $i));
}

$t->diag('->trim()');

$input  = array(' a ', ' čeněk');
$output = array('a', 'čeněk');
foreach($input as $k => $i)
{
  $t->is(sfUtf8::trim($i), $output[$k], sprintf('sfUtf8::trim() works ok for "%s"', $i));
}

$t->diag('->ucwords()');

$input  = array('Čeněk řÍha', "Single prime \"apostrophes\" aren't an išjů, and 'single prime' quotes work");
$output = array('Čeněk ŘÍha', "Single Prime \"Apostrophes\" Aren't An Išjů, And 'Single Prime' Quotes Work");
foreach($input as $k => $i)
{
  $t->is(sfUtf8::ucwords($i), $output[$k], sprintf('sfUtf8::ucwords() works ok for "%s"', $i));
}

$t->diag('->ucwords()');

$input  = array('Čeněk řÍha');
$output = array('Čeněk ŘÍha');
foreach($input as $k => $i)
{
  $t->is(sfUtf8::ucwords($i), $output[$k], sprintf('sfUtf8::ucwords() works ok for "%s"', $i));
}

$t->diag('->len()');

$input  = array('Čeněk řÍha');
$output = array(10);
foreach($input as $k => $i)
{
  $t->is(sfUtf8::len($i), $output[$k], sprintf('sfUtf8::len() works ok for "%s"', $i));
}

$t->diag('->convertToUtf8()');

$t->is(sfUtf8::convertToUtf8($windows1250), $utf8, 'sfUtf8::convertToUtf8() works ok');
$t->is(sfUtf8::convertToUtf8($iso88592), $utf8, 'sfUtf8::convertToUtf8() works ok');

$t->diag('->detectCharset');

$results = array(
  'UTF-8',
  'WINDOWS-1250',
  'ISO-8859-1',
  'ISO-8859-2'
);

foreach(array($utf8, $windows1250, $iso88591, $iso88592) as $k => $string)
{
  $t->is(sfUtf8::detectCharset($string), $results[$k], sprintf('sfUtf8::detechCharset() works ok for "%s"', $results[$k]));
}

// subReplace
$t->is(sfUtf8::subReplace('ABCDEFGH:/MNRPQR/', 'bob', 0), 'bob', 'subReplace() works ok');
$t->is(sfUtf8::subReplace('ABCDEFGH:/MNRPQR/', 'bob', 0, 17), 'bob', 'subReplace() works ok');
$t->is(sfUtf8::subReplace('ABCDEFGH:/MNRPQR/', 'bob', 0, 0), 'bobABCDEFGH:/MNRPQR/', 'subReplace() works ok');
$t->is(sfUtf8::subReplace('ABCDEFGH:/MNRPQR/', 'bob', 10, -1), 'ABCDEFGH:/bob/', 'subReplace() works ok');
$t->is(sfUtf8::subReplace('žížala', 'bob', 0, 0), 'bobžížala', 'subReplace() works ok');
$t->is(sfUtf8::subReplace('žížala:/MNRPQR/', 'bob', -7, -1), 'žížala:/bob/', 'subReplace() works ok');

$t->is(sfUtf8::subReplace(array('žížala', 'žoužala'), 'bob', 0, 0), array('bobžížala', 'bobžoužala'), 'subReplace() works ok with arrays');

$t->is(sfUtf8::subReplace(array('žížala', 'žoužala'), 'bob', 0, array(0, 1)), array('bobžížala', 'boboužala'), 'subReplace() works ok with arrays with array of lengths');