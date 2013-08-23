<?php

require_once(dirname(__FILE__) . '/../../bootstrap/unit.php');
require_once(dirname(__FILE__) . '/stubs/Dummy.php');

$t = new lime_test(4, new lime_output_color());

$builder = new sfDependencyInjectionMapBuilderArray();

$builder->add(array(
  'dependencyName' => 'database',
  'injectWith' => 'method',
  'injectAs' => 'setDatabase',
));

$builder->add(array(
  'dependencyName' => 'apple',
  'injectWith' => 'constructor',
  'injectAs' => 1
));

$builder->add(array(
  'injectWith' => 'property',
  'injectAs' => 'theService',
  'force' => true,
  'newClass' => 'Service_Class',
));

$builder->add(array(
  'dependencyName' => 'someDep',
  'injectWith' => 'property',
  'injectAs' => 'someDep',
));

$builder->build();

$t->isa_ok($builder->getMap(), 'sfDependencyInjectionMap', 'getMap() returns the map as sfDependencyInjectionMap object');

$t->is($builder->getMap()->count(), 4, 'build() builds the map');

$map = $builder->getMap();
$item = $map->getItemsFor('constructor');

$t->isa_ok($item, 'array', 'getItemsFor() returned array');
$t->is($item[0]->getDependencyName(), 'apple', 'getItemsFor() returned correct result');