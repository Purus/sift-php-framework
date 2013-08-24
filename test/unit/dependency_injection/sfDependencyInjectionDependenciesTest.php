<?php

require_once(dirname(__FILE__) . '/../../bootstrap/unit.php');

$t = new lime_test(1, new lime_output_color());

$containerDependencies = new sfDependencyInjectionDependencies();

$object = new stdClass();
$object->name = 'testName';

$containerDependencies->set('test', $object);

$getObject = $containerDependencies->get('test');

$t->is_deeply($getObject, $object, 'returned object is the same as set');
