<?php

require_once(dirname(__FILE__) . '/../../bootstrap/unit.php');

$t = new lime_test(10);

// ->clean()
$t->diag('->clean()');

$v = new sfValidatorCssClassName();
$t->is($v->clean('hidden'), 'hidden', '->clean() checks that the value match the regex');

try
{
  $v->clean('1invalidclass');
  $t->fail('->clean() throws an sfValidatorError if the value does not match the pattern');
  $t->skip('', 1);
}
catch(sfValidatorError $e)
{
  $t->pass('->clean() throws an sfValidatorError if the value does not match the pattern');
  $t->is($e->getCode(), 'invalid', '->clean() throws a sfValidatorError');
}

try
{
  $v->clean('1invalidclass');
  $t->fail('->clean() throws an sfValidatorError if the value does not match the pattern');
  $t->skip('', 1);
}
catch(sfValidatorError $e)
{
  $t->pass('->clean() throws an sfValidatorError if the value does not match the pattern');
  $t->is($e->getCode(), 'invalid', '->clean() throws a sfValidatorError');
}

try
{
  $v->clean('i1validclass');
  $t->pass('->clean() throws an sfValidatorError if the value does not match the pattern');
}
catch(sfValidatorError $e)
{
  $t->fail('->clean() throws an sfValidatorError if the value does not match the pattern');
}

// custom message

$v = new sfValidatorCssClassName(array(), array('invalid' => 'This is invalid'));

try
{
  $v->clean('1invalidclass');
  $t->fail('->clean() throws an sfValidatorError if the value does not match the pattern');
  $t->skip('', 1);
}
catch(sfValidatorError $e)
{
  $t->pass('->clean() throws an sfValidatorError if the value does not match the pattern');
  $t->is($e->getMessage(), 'This is invalid', '->clean() throws a sfValidatorError');
}

$t->diag('multiple_values');

$v = new sfValidatorCssClassName(array(), array('invalid' => 'The class name "%value%" is invalid'));

try
{
  $v->clean('validad-class 1invalidclass');
  $t->fail('->clean() throws an sfValidatorError if the value does not match the pattern');
  $t->skip('', 1);
}
catch(sfValidatorError $e)
{
  $t->pass('->clean() throws an sfValidatorError if the value does not match the pattern');
  $t->is($e->getMessage(), 'The class name "1invalidclass" is invalid', '->clean() throws a sfValidatorError');
}



