<?php

require_once(dirname(__FILE__) . '/../../bootstrap/unit.php');

$t = new lime_test(9, new lime_output_color());

$item = new sfBrowseHistoryItem(1, 'Jesus is Lord');

$t->isa_ok($item->getId(), 'integer', '->getId() returns integer');
$t->isa_ok($item->getName(), 'string', '->getName() returns string');

$t->is($item->getId(), 1, '->getId() returns assigned id');
$t->is($item->getName(), 'Jesus is Lord', '->getName() returns assigned name');

// set parameter
$item->setParameter('foo', 'bar');
$t->is($item->getFoo(), 'bar', 'magic method ->getFoo() works ok');
$t->is($item->getfoo(), 'bar', 'magic method ->getfoo() works ok');

$t->is($item->hasParameter('foo'), true, '->hasParameter() works ok');
$t->is($item->hasFoo(), true, '->hasFoo() works ok');


try {

  $item->foobar();
  $t->fail('Invalid method throws BadMethodCallException exception');
}
catch(BadMethodCallException $e)
{
  $t->pass('Invalid method throws BadMethodCallException exception');
}
