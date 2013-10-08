<?php

require_once(dirname(__FILE__).'/../../bootstrap/unit.php');

$t = new lime_test(1);

class foo {}

// ::removeObjects()
$t->diag('::removeObjects()');
$objectArray = array('foo', 42, new sfDebug(), array('bar', 23, new foo()));
$cleanedArray = array('foo', 42, 'sfDebug Object()', array('bar', 23, 'foo Object()'));
$t->is_deeply(sfDebug::removeObjects($objectArray), $cleanedArray, '::removeObjects() converts objects to String representations using the class name');
