<?php

require_once(dirname(__FILE__).'/../../bootstrap/unit.php');

$t = new lime_test(10, new lime_output_color());

$t->is_deeply(sfRounding::round(10), '10', 'method is called.');
$t->is_deeply(sfRounding::round('10.25', 1, sfRounding::NONE), '10.25', 'method NONE is called.');
$t->is_deeply(sfRounding::round('10.25', 1, sfRounding::UP), '10.3', 'method UP is called.');
$t->is_deeply(sfRounding::round('10.25', 1, sfRounding::DOWN), '10.2', 'method DOWN is called.');
$t->is_deeply(sfRounding::round('10.25', 1, sfRounding::HALF_DOWN), '10.2', 'method HALF_DOWN is called.');
$t->is_deeply(sfRounding::round('10.25', 1, sfRounding::HALF_UP), '10.3', 'method HALF_UP is called.');
$t->is_deeply(sfRounding::round('10.25', 1, sfRounding::HALF_EVEN), '10.2', 'method HALF_EVEN is called.');
$t->is_deeply(sfRounding::round('10.25', 1, sfRounding::HALF_ODD), '10.3', 'method HALF_ODD is called.');
$t->is_deeply(sfRounding::round('10.25', 1, sfRounding::NEAREST, '10'), '10.3', 'method HALF_ODD is called.');

try
{
  sfRounding::round('10.21', 1, 'Invalid mode');
  $t->fail('Using invalid rounding mode throws an exception');
}
catch(Exception $e)
{
  $t->pass('Using invalid rounding mode throws an exception');
}

$t->is_deeply(sfRounding::roundToNearest('10.21', '10', 2, sfRounding::UP), '10.3', 'roundToNearest() is called.');