<?php

require_once dirname(__FILE__).'/../../bootstrap/unit.php';

$t = new lime_test(3);

$bt = new sfDebugBacktrace(debug_backtrace(), array(
  'skip' => 1
));

$t->isa_ok($bt->get(), 'array', '->get() returns an array');
$t->isa_ok(serialize($bt), 'string', 'serialize() works ok');
$t->ok(unserialize(serialize($bt)) == $bt, 'unserialize() works ok');
