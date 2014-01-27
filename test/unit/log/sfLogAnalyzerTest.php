<?php

require_once(dirname(__FILE__).'/../../bootstrap/unit.php');

$t = new lime_test(27);

try
{
  $a = new sfLogAnalyzer('a');
  $t->fail('constructor throws an exception if the file does not exist');
}
catch(Exception $e)
{
  $t->pass('constructor throws an exception if the file does not existn');
}

$file = dirname(__FILE__) . '/fixtures/sample.log';

// ->log()
$t->diag('->analyze()');

$a = new sfLogAnalyzer($file, array(
  'database' => sys_get_temp_dir() . '/analyzed_log.db'
));

$t->diag('multiple levels');

$t->isa_ok($a->getLogs(array(
  'error', 'critical', 'notice'
)), 'array', 'get() works ok');

$t->diag('magic methods');

$t->isa_ok($a->getEmergencyLogs(), 'array', 'getEmergencyLogs() works ok');
$t->isa_ok($a->getAlertLogs(), 'array', 'getAlertLogs() works ok');
$t->isa_ok($a->getCriticalLogs(), 'array', 'getCriticalLogs() works ok');

// errors
$t->isa_ok($a->getErrorLogs(), 'array', 'getErrors() works ok');
$t->is(count($a->getErrorLogs()), 2, 'getErrors() works ok');

foreach($a->getErrorLogs() as $log)
{
  $t->isa_ok($log['message'], 'sfLogAnalyzerMessage', 'the message is instance of sfLogAnalyzerMessage');
  $t->isa_ok($log['message']->getExtra(), 'array', 'extra parameters are present in the message');
}

$t->isa_ok($a->getWarningLogs(), 'array', 'getWarningLogs() works ok');

$t->is(count($a->getWarningLogs()), 2, 'getWarningLogs() works ok');

$t->isa_ok($a->getNoticeLogs(), 'array', 'getErrors() works ok');
$t->isa_ok($a->getInfoLogs(), 'array', 'getErrors() works ok');

$t->isa_ok($a->getDebugLogs(), 'array', 'getErrors() works ok');
$t->is(count($a->getDebugLogs()), 41, 'getDebugLogs() works ok');

$t->is(count($a), 167, 'Analyzer implements countable interface');

$t->is(count($a->getSkipped()), 1, 'The skipped line is catched.');

$skipped = $a->getSkipped();
$t->is($skipped[0]['line'], 168, 'There are not skipped lines.');

$t->diag('->getStart() ->getEnd()');

$t->is(date('d.m.Y H:i:s', $a->getStart()), '18.10.'.date('Y').' 21:42:10', 'getStart() returns the correct date');
$t->is(date('d.m.Y H:i:s', $a->getEnd()), '18.10.'.date('Y').' 21:42:27', 'getEnd() returns the correct date');

$t->diag('->getLevels()');
$t->isa_ok($a->getLevels(), 'array', 'getLevels() returns an array');
$t->is(count($a->getLevels()), 6, 'getLevels() returns an array with results');

$t->diag('->getTypes()');
$t->isa_ok($a->getTypes(), 'array', 'getTypes() returns an array');
$t->is(count($a->getTypes()), 1, 'getTypes() returns an array with results');

$t->diag('->getDatabaseHandle()');
$t->isa_ok($a->getDatabaseHandle(), 'PDO', 'getDatabaseHandle() returns PDO object');
