<?php

require_once(dirname(__FILE__) . '/../../bootstrap/unit.php');

$t = new lime_test(7, new lime_output_color());

$map = new sfDependencyInjectionMap();
$item = new sfDependencyInjectionMapItem();
$item->setDependencyName('depend');
$item->setInjectAs('myDepend');
$item->setInjectWith('test');

$map->append($item);

$t->is(count($map), 1, 'Map implements countable');

$t->diag('->append()');

$item2 = new sfDependencyInjectionMapItem();
$map->append($item2);

$t->is($map->count(), 2, 'add() added item to the map');

$items = $map->getItemsFor('test');

$t->isa_ok($items, 'array', 'getItemsFor() returns an array');
$t->is_deeply($items, array($item), 'getItemsFor() works ok');

$t->isa_ok($map->has('test'), 'boolean', 'has() returns boolean');
$t->is_deeply($map->has('test'), true, 'has() returns true for existing item');
$t->isa_ok($map->has('testaaaa'), 'boolean', 'has() returns boolean for nonexistant value');