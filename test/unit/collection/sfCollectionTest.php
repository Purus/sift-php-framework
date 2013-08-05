<?php

require_once(dirname(__FILE__).'/../../bootstrap/unit.php');

$t = new lime_test(8, new lime_output_color());

class myTestCollection extends sfCollection {}

$c = new myTestCollection();

$t->is(count($c), 0, 'count() works ok');
$c->append('1');
$t->is(count($c), 1, 'count() works ok');

$t->diag('->contains');
$t->isa_ok($c->contains('a'), 'boolean', 'contains returns boolean');
$t->is($c->contains('1'), true, 'contains returns correct result');

function foo($a, $b)
{
  if($a == $b)
  {
    return 0;
  }
  return ($a < $b) ? -1 : 1;
}

$c->append('2');

$t->diag('->factory()');

$sorter = sfCollectionSorter::factory('callback', array('foo'));
$t->isa_ok($sorter, 'sfCollectionSorter', 'factory method returns an object of sfCollectionSorter');

try
{
  sfCollectionSorter::factory('invalid', array('foo'));
  $t->fail('factory() throws an exception if the strategy does not exist');
}
catch(Exception $e)
{
  $t->pass('factory() throws an exception if the strategy does not exist');
}

$c->sort(sfCollectionSorter::factory('callback', 'foo'), sfCollectionSorter::DIRECTION_DESC);

$t->is(array_values((array)$c), array('2', '1'), 'sort works ok');

$c->sort(sfCollectionSorter::factory('callback', 'foo'), sfCollectionSorter::DIRECTION_ASC);

$t->is(array_values((array)$c), array('1', '2'), 'sort works ok');
