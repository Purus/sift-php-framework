<?php

require_once(dirname(__FILE__).'/../../bootstrap/unit.php');

$t = new lime_test(9);

$buffer = fopen('php://memory', 'rw');
$logger = new sfVarLogger();

$logger->log('foo');
$logger->log('{sfFoo} bar', sfLogger::ERR);

$logs = $logger->getLogs();
$t->is(count($logs), 2, 'sfVarLogger logs all messages into its instance');

$t->is($logs[0]['message'], 'foo', 'sfVarLogger returns an array with the message');
$t->is($logs[0]['level'], 6, 'sfVarLogger returns an array with the level');
$t->is($logs[0]['level_name'], 'info', 'sfVarLogger returns an array with the level name');
$t->is($logs[0]['type'], 'sfOther', 'sfVarLogger returns an array with the type');

$t->is($logs[1]['message'], 'bar', 'sfVarLogger returns an array with the message');
$t->is($logs[1]['level'], 3, 'sfVarLogger returns an array with the level');
$t->is($logs[1]['level_name'], 'error', 'sfVarLogger returns an array with the level name');
$t->is($logs[1]['type'], 'sfFoo', 'sfVarLogger returns an array with the type');
