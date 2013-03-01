<?php

require_once(dirname(__FILE__).'/../../bootstrap/unit.php');

$t = new lime_test(9, new lime_output_color());

$scheduler = sfShutdownScheduler::getInstance();

$t->isa_ok($scheduler, 'sfShutdownScheduler', 'getInstance() returns an instance of sfShutdownScheduler');
$t->isa_ok(count($scheduler), 0, 'implements Countable interface');

try
{
  $scheduler->register('nonsense');
  $t->fail('->register() throws InvalidArgumentException if callable is not valid');
}
catch(InvalidArgumentException $e)
{
  $t->pass('->register() throws InvalidArgumentException if callable is not valid');
}

try
{
  $scheduler->register(new sfCallable('nonsense'));
  $t->fail('->register() throws InvalidArgumentException if callable is not valid');
}
catch(InvalidArgumentException $e)
{
  $t->pass('->register() throws InvalidArgumentException if callable is not valid');
}

class myShutdownScheduler extends sfShutdownScheduler {

  public function __construct()
  {
  }
}

$file = dirname(__FILE__).'/shutdown.txt';

function foobar($a, $b)
{
  global $file;
  file_put_contents($file, 'a: ' . $a . ' b: ' . $b);
}


$scheduler = new myShutdownScheduler();
$scheduler->register('foobar', array('first', '1'));
$scheduler->register('foobar', array('second', '2'));

$t->is(count($scheduler), 2, 'returns correct number of events');

// call shutdowns
$scheduler->callRegisteredShutdown();
// check if something happend
$t->is(file_get_contents($file), 'a: second b: 2', 'handlers have been executed');
unlink($file);

// high priority, but registered first
$scheduler->register('foobar', array('first', '1'), sfShutdownScheduler::HIGH_PRIORITY);
$scheduler->register('foobar', array('second', '2'));
$scheduler->callRegisteredShutdown();
// check if something happend
$t->is(file_get_contents($file), 'a: second b: 2', 'handlers have been executed');
unlink($file);

// lower priority, but registered last
$scheduler->register('foobar', array('second', '2'));
$scheduler->register('foobar', array('first', '1'), sfShutdownScheduler::LOW_PRIORITY);
$scheduler->callRegisteredShutdown();
// check if something happend
$t->is(file_get_contents($file), 'a: first b: 1', 'handlers have been executed');
unlink($file);

// with sfCallable
$callable = new sfCallable('foobar');
$scheduler->register($callable, array('callable', 'second_argument'));
$scheduler->callRegisteredShutdown();
// check if something happend
$t->is(file_get_contents($file), 'a: callable b: second_argument', 'handlers have been executed for sfCallable object');
unlink($file);
