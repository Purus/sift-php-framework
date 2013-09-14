<?php
require_once(dirname(__FILE__) . '/../../bootstrap/unit.php');

$t = new lime_test(15);

$v = new sfValidatorString();

// ->clean()
$t->diag('->clean()');
$t->is($v->clean('foo'), 'foo', '->clean() returns the string unmodified');

$v->setOption('required', false);
$t->ok($v->clean(null) === '', '->clean() converts the value to a string');
$t->ok($v->clean(1) === '1', '->clean() converts the value to a string');

$v->setOption('max_length', 2);
$t->is($v->clean('fo'), 'fo', '->clean() checks the maximum length allowed');
try
{
  $v->clean('foo');
  $t->fail('"max_length" option set the maximum length of the string');
  $t->skip('', 1);
}
catch(sfValidatorError $e)
{
  $t->pass('"max_length" option set the maximum length of the string');
  $t->is($e->getCode(), 'max_length', '->clean() throws a sfValidatorError');
}

$v->setMessage('max_length', 'Too long');
try
{
  $v->clean('foo');
  $t->fail('"max_length" error message customization');
}
catch(sfValidatorError $e)
{
  $t->is($e->getMessage(), 'Too long', '"max_length" error message customization');
}

$v->setOption('max_length', null);

$v->setOption('min_length', 3);
$t->is($v->clean('foo'), 'foo', '->clean() checks the minimum length allowed');
try
{
  $v->clean('fo');
  $t->fail('"min_length" option set the minimum length of the string');
  $t->skip('', 1);
}
catch(sfValidatorError $e)
{
  $t->pass('"min_length" option set the minimum length of the string');
  $t->is($e->getCode(), 'min_length', '->clean() throws a sfValidatorError');
}

$v->setMessage('min_length', 'Too short');
try
{
  $v->clean('fo');
  $t->fail('"min_length" error message customization');
}
catch(sfValidatorError $e)
{
  $t->is($e->getMessage(), 'Too short', '"min_length" error message customization');
}

$v->setOption('min_length', null);

$t->diag('UTF-8 support');
if(!function_exists('mb_strlen'))
{
  $t->skip('UTF-8 support needs mb_strlen');
}
else
{
  $v->setOption('max_length', 4);
  $t->is($v->clean('été'), 'été', '"sfValidatorString" supports UTF-8');
}


$v = new sfValidatorString(array(), array('required' => 'My required message'));

$messages = $v->getJavascriptValidationMessages();

$t->is($messages, array(
    'required' => 'My required message'
), 'getJavascriptValidationMessages() works ok');

$t->is($v->getActiveMessages(), array(
  'My required message'
), 'getActiveMessages() returns active messages');

$v = new sfValidatorString(array(), array('required' => 'My required message'));

$rules = $v->getJavascriptValidationRules();

$t->is($messages, array(
    'required' => 'My required message'
), 'getJavascriptValidationRules() works ok');