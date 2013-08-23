<?php

require_once(dirname(__FILE__) . '/../../bootstrap/unit.php');

$t = new lime_test(5, new lime_output_color());

$item = new sfDependencyInjectionMapItem();
$item->setDependencyName('My Dependency');
$item->setInjectAs('mySomething');
$item->setInjectWith('method');
$item->setForce(true);
$item->setNewClass('New_Class');

$t->diag('->getDependencyName() ->getInjectAs() ->getInjectWith() ->getForce() ->getNewClass()');
$t->is($item->getDependencyName(), 'My Dependency');

$t->is($item->getInjectAs(), 'mySomething');
$t->is($item->getInjectWith(), 'method');
$t->is($item->getForce(), true);
$t->is($item->getNewClass(), 'New_Class');
