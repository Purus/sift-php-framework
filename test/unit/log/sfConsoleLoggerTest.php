<?php


require_once(dirname(__FILE__).'/../../bootstrap/unit.php');

$t = new lime_test(1);

$logger = new sfConsoleLogger();
$logger->setStream($buffer = fopen('php://memory', 'rw'));

$logger->log('foo');
rewind($buffer);
$t->is(fix_linebreaks(stream_get_contents($buffer)), "foo\n", 'sfConsoleLogger logs messages to the console');
