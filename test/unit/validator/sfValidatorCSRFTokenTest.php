<?php

require_once(dirname(__FILE__) . '/../../bootstrap/unit.php');

$t = new lime_test(5);

// __construct()
$t->diag('__construct()');
try
{
  new sfValidatorCSRFToken();
  $t->fail('__construct() throws an RuntimeException if you don\'t pass a token option');
}
catch(RuntimeException $e)
{
  $t->pass('__construct() throws an RuntimeException if you don\'t pass a token option');
}

$v = new sfValidatorCSRFToken(array('token' => 'symfony'));

// ->clean()
$t->diag('->clean()');
$t->is($v->clean('symfony'), 'symfony', '->clean() checks that the token is valid');

try
{
  $v->clean('another');
  $t->fail('->clean() throws an sfValidatorError if the token is not valid');
  $t->skip('', 1);
}
catch(sfValidatorError $e)
{
  $t->pass('->clean() throws an sfValidatorError if the token is not valid');
  $t->is($e->getCode(), 'csrf_attack', '->clean() throws a sfValidatorError');
}

// ->asString()
$t->diag('->asString()');
$t->is($v->asString(), 'CSRFToken({ token: symfony })', '->asString() returns a string representation of the validator');
