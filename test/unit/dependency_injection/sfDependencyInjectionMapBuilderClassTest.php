<?php

require_once(dirname(__FILE__) . '/../../bootstrap/unit.php');
require_once(dirname(__FILE__) . '/stubs/Dummy.php');

$t = new lime_test(9, new lime_output_color());

$builder = new sfDependencyInjectionMapBuilderClass();
$builder->setClass('StubDummy');
$builder->setup();
$builder->buildMethods();

$t->isa_ok($builder->getMap(), 'sfDependencyInjectionMap', 'builder returns map as sfDependencyInjectionMap object');

$t->is(count($builder->getMap()), 2, 'builder returns map with 2 elements');

$items = $builder->getMap()->getItemsFor('constructor');

$t->isa_ok($items, 'array', 'getItemsFor() returns items as an array');
$t->is($items[0]->getDependencyName(), 'Banana', 'getItemsFor() returns items for constructor');

$items = $builder->getMap()->getItemsFor('method');

$t->is($items[0]->getDependencyName(), 'Apple', 'getItemsFor() returns items for method');

$builder->buildProperties();

$items = $builder->getMap()->getItemsFor('property');

$t->is($items[0]->getDependencyName(), 'Pear', 'getItemsFor() returns items for properties');

$builder->setup();
$builder->buildClass();

$t->is($builder->getMap()->count(), 5, 'buildClass() builds a map');

$builder->setup();
$builder->buildClass();

$items = $builder->getMap()->getItemsFor('method');

$t->is($items[1]->getNewClass(), 'StubSomething', 'newClass is populated');

$builder->build();

$t->is($builder->getMap()->count(), 8, 'everything is build');