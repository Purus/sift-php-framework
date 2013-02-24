<?php

require_once(dirname(__FILE__).'/../../bootstrap/unit.php');
require_once(dirname(__FILE__).'/../../../lib/util/sfToolkit.class.php');
require_once(dirname(__FILE__).'/../../../lib/util/sfArray.class.php');

$t = new lime_test(12, new lime_output_color());

$array = array(
    "name" => "Jesus Christ",
    "age" => "n/a",					
    "location" => array(
      "city" => "New Jerusalem",            
      "country" => "New Earth"
    )
);

$t->diag('->get()');

$t->is(sfArray::get($array, 'name'), 'Jesus Christ', 'get() works ok for simple key');
$t->is(sfArray::get($array, 'location.country'), 'New Earth', 'get() works ok for dot notation key');
$t->is(sfArray::get($array, 'location.coordinates', 'unknown'), 'unknown', 'get() works ok for dot notation key and default value');
$t->is(sfArray::get($array, array('location.coordinates', 'name'), 'unknown'), 
    array(
      'location.coordinates' => 'unknown', 
      'name' => 'Jesus Christ'), 'get() works ok for array dot notation key and default value');

$t->diag('->set()');

sfArray::set($array, 'name', 'Savior Jesus Christ');
$t->is(sfArray::get($array, 'name'), 'Savior Jesus Christ', 'set() works ok for simple key');

sfArray::set($array, 'location.city');
$t->is(sfArray::get($array, 'location.city'), null, 'set() works ok for simple key');

class Coordinates {}
$coordinates = new Coordinates();
$coordinates->lat = 49.123;
$coordinates->lon = 14.123;

sfArray::set($array, 'location.coordinates', $coordinates);
$t->isa_ok(sfArray::get($array, 'location.coordinates'), 'Coordinates', 'set() works ok for dot notation key and object');

$t->diag('->keyExists()');

$t->is(sfArray::keyExists($array, 'name'), true, 'keyExists() works ok for simple key');
$t->is(sfArray::keyExists($array, 'location.city'), true, 'keyExists() works ok for dot notation key');
$t->is(sfArray::keyExists($array, 'location.coordinates'), true, 'keyExists() works ok for object');
$t->is(sfArray::keyExists($array, 'location.unknown'), false, 'keyExists() works ok for invalid key');

$t->is(sfArray::keyExists(array('bar' => null), 'bar'), true, 'keyExists() works ok for null value');
