<?php

require_once(dirname(__FILE__) . '/../../bootstrap/unit.php');

$t = new lime_test(11, new lime_output_color());

$valid = array(
  'Europe/Prague',
  'Asia/Jerusalem',
  'UTC'
);

$invalid = array(
  'EuropePrague',
  'my domain',
  ''
);

$t->diag('->isValid()');

foreach($valid as $timezone)
{
  $t->is_deeply(sfDateTimeZone::isValid($timezone), true, 'isValid() works ok');
}

foreach($invalid as $timezone)
{
  $t->is_deeply(sfDateTimeZone::isValid($timezone), false, 'isValid() works ok');
}

$t->diag('getNameFromOffset()');


$offsets = array(
  2 => 'Europe/Paris',
  3 => 'Europe/Helsinki',
  'my invalid' => false
);

foreach($offsets as $offset => $timezone)
{
  // daylight savings in action
  $t->is_deeply(sfDateTimeZone::getNameFromOffset($offset, true), $timezone, 'getNameFromOffset() works ok');
}

$offsets = array(
  2 => 'Europe/Helsinki',
  3 => 'Europe/Moscow',
);

foreach($offsets as $offset => $timezone)
{
  // disabled daylight
  $t->is_deeply(sfDateTimeZone::getNameFromOffset($offset, false), $timezone, 'getNameFromOffset() works ok');
}
