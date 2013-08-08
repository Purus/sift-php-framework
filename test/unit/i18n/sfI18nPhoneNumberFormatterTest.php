<?php

require_once(dirname(__FILE__).'/../../bootstrap/unit.php');

$t = new lime_test(12, new lime_output_color());

$c = new sfI18nPhoneNumberFormatter();

$t->diag('->format()');

$tests = array(
  // CZ
  '+420386123456' => '+420 386123456',
  // SK
  '+421245257673' => '+421 245257673',
  // US
  '+18002221222' => '+1 8002221222',
  // FR
  '+33140998109' => '+33 140998109',
  // GB
  '+442031799555' => '+44 2031799555',
  // DE
  '+4922166992750' => '+49 22166992750'
);

foreach($tests as $test => $expected)
{
  $t->is($c->format($test), $expected, sprintf('->format() works as expected for "%s"', $test));
}

$t->diag('->format() with culture specifics');

$tests = array(
  // CZ
  '+420386123456' => '+420 386 123 456',
  // SK
  '+421245257673' => '+421 245 257 673',
  // US
  '+18002221222' => '+1 80 02 22 12 22',
  // FR
  '+33140998109' => '+33 140 998 109',
  // GB
  '+442031799555' => '+44 20 31 79 95 55',
  // DE
  '+4922166992750' => '+49 22 166 992 750'
);

foreach($tests as $test => $expected)
{
  $t->is($c->format($test, 'cs_CZ'), $expected, sprintf('->format() works as expected for "%s"', $test));
}