<?php

require_once(dirname(__FILE__).'/../../bootstrap/unit.php');

$t = new lime_test(1);

$buffer = fopen('php://memory', 'rw');
$logger = new sfStreamLogger(array('stream' => $buffer));

$logger->log('foo');
rewind($buffer);

$t->is(fix_linebreaks(stream_get_contents($buffer)), "foo\n", 'sfStreamLogger logs messages to a PHP stream');
