<?php

require_once(dirname(__FILE__).'/../../bootstrap/unit.php');
require_once(dirname(__FILE__).'/../../../lib/util/sfCallable.class.php');

$t = new lime_test(5);

// call()
$t->diag('call()');
$c = new sfCallable('trim');
$t->is($c->call('  foo  '), 'foo', '->call() calls the callable with the given arguments');

class TrimTest
{
  static public function trimStatic($text)
  {
    return trim($text);
  }

  public function trim($text)
  {
    return trim($text);
  }
}

$c = new sfCallable(array('TrimTest', 'trimStatic'));
$t->is($c->call('  foo  '), 'foo', '->call() calls the callable with the given arguments');

$c = new sfCallable(array(new TrimTest(), 'trim'));
$t->is($c->call('  foo  '), 'foo', '->call() calls the callable with the given arguments');

$c = new sfCallable('nonexistantcallable');
try
{
  $c->call();
  $t->fail('->call() throws an sfException if the callable is not valid');
}
catch (sfException $e)
{
  $t->pass('->call() throws an sfException if the callable is not valid');
}

// ->getCallable()
$t->diag('->getCallable()');
$c = new sfCallable('trim');
$t->is($c->getCallable(), 'trim', '->getCallable() returns the current callable');
