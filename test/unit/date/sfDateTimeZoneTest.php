<?php

require_once(dirname(__FILE__) . '/../../bootstrap/unit.php');

$t = new lime_test(9, new lime_output_color());

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
  $t->is_deeply(sfDateTimeZone::getNameFromOffset($offset), $timezone, 'getNameFromOffset() works ok');
}
