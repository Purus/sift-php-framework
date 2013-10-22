<?php

require_once(dirname(__FILE__).'/../../bootstrap/unit.php');

$t = new lime_test(2);

// make it active
sfConfig::set('sf_debug', true);

$t->isa_ok(sfDebugDumper::dump(1, array(), false), 'string', 'dump() works ok');

$t->isa_ok(sfDebugDumper::dump(array(
    'd' => 'foobar',
    'c' => new stdClass()
), array(), false), 'string', 'dump() of array works ok');
