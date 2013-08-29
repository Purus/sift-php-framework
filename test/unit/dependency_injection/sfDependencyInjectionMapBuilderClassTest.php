<?php

require_once(dirname(__FILE__) . '/../../bootstrap/unit.php');
require_once(dirname(__FILE__) . '/stubs/Dummy.php');
require_once(dirname(__FILE__) . '/stubs/DummyExtended.php');

$t = new lime_test(13, new lime_output_color());

class myDependencyInjectionMapBuilderClass extends sfDependencyInjectionMapBuilderClass {

  public function buildMethods()
  {
    return parent::buildMethods();
  }

  public function buildProperties()
  {
    return parent::buildProperties();
  }

  public function buildClass()
  {
    return parent::buildClass();
  }

}

$builder = new myDependencyInjectionMapBuilderClass('Dummy');
$builder->buildMethods();

$t->isa_ok($builder->getMap(), 'sfDependencyInjectionMap', 'builder returns map as sfDependencyInjectionMap object');
$t->is(count($builder->getMap()), 2, 'builder returns map with 2 elements');

$items = $builder->getMap()->getItemsFor('constructor');

$t->isa_ok($items, 'array', 'getItemsFor() returns items as an array');
$t->is($items[0]->getDependencyName(), 'banana', 'getItemsFor() returns items for constructor');

$items = $builder->getMap()->getItemsFor('method');

$t->is($items[0]->getDependencyName(), 'apple', 'getItemsFor() returns items for method');

$builder = new myDependencyInjectionMapBuilderClass('Dummy');
$builder->buildProperties();

$items = $builder->getMap()->getItemsFor('property');

$t->is($items[0]->getDependencyName(), 'pear', 'getItemsFor() returns items for properties');

$builder = new myDependencyInjectionMapBuilderClass('Dummy');
$builder->buildClass();

$t->is($builder->getMap()->count(), 2, 'buildClass() builds a map');

$builder = new myDependencyInjectionMapBuilderClass('Dummy');
$builder->buildClass();

$items = $builder->getMap()->getItemsFor('method');

$t->is($items[0]->getNewClass(), 'Something', 'newClass is populated');

$builder = new myDependencyInjectionMapBuilderClass('Dummy');

$builder->build();

$t->is($builder->getMap()->count(), 5, 'everything is build');

$t->diag('extensioned classes');

$builder = new myDependencyInjectionMapBuilderClass('DummyExtended');
$builder->buildMethods();

$t->isa_ok($builder->getMap(), 'sfDependencyInjectionMap', 'builder returns map as sfDependencyInjectionMap object');
$t->is(count($builder->getMap()), 2, 'builder returns map with 2 elements');

$items = $builder->getMap()->getItemsFor('constructor');

$t->isa_ok($items, 'array', 'getItemsFor() returns items as an array');
$t->is($items[0]->getDependencyName(), 'banana', 'getItemsFor() returns items for constructor');
