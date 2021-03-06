<?php

require_once(dirname(__FILE__).'/../../../bootstrap/unit.php');

$t = new lime_test(24, new lime_output_color());

$v = new sfValidatorPrice(array(
  'strict_mode' => true
));

// ->clean() - no culture
$t->diag('->clean() - standard culture = en');

$v->setOption('culture', 'en');
$t->is($v->clean(12.3), 12.3, '->clean() returns the numbers unmodified');
$t->is($v->clean('12.3'), 12.3, '->clean() converts strings to numbers');

$t->is($v->clean(12.12345678901234), 12.12345678901234, '->clean() returns the numbers unmodified');
$t->is($v->clean('12.12345678901234'), 12.12345678901234, '->clean() converts strings to numbers');

$t->is($v->clean('123,456.78'), 123456.78, '->clean() convert grouped numbers');

try
{
  $v->clean('123,456.789,012');
  $t->fail('->clean fails wrong grouped numbers');
}
catch (sfValidatorError $e)
{
  $t->pass('->clean throws a sfValidatorError if the value is grouped wrong');
}

try
{
  $v->clean('not a float');
  $t->fail('->clean() throws a sfValidatorError if the value is not a number');
}
catch (sfValidatorError $e)
{
  $t->pass('->clean() throws a sfValidatorError if the value is not a number');
}

$t->diag('->clean() - culture = de');
$v->setOption('culture','de');
$t->is($v->clean("12,3"),"12.3",'->clean() return the normalized string');
$t->is($v->clean('12,12345678901234'), 12.12345678901234, '->clean() converts strings to normalized numbers');
$t->is($v->clean('123.456,78'), 123456.78, '->clean() convert grouped numbers');
$t->is($v->clean('100.000'), 100000, '->clean() convert grouped numbers');

try
{
  $v->clean('123.456,789.012');
  $t->fail('->clean fails wrong grouped numbers');
}
catch (sfValidatorError $e)
{
  $t->pass('->clean throws a sfValidatorError if the value is grouped wrong');
}

try
{
  $v->clean("12.3");
  $t->fail('->clean() throws a sfValidatorError if the value is not in localized format');
}
catch  (sfValidatorError $e)
{
  $t->pass('->clean() throws a sfValidatorError if the value is not in localized format');
}

$t->diag('->clean() - culture = cs_CZ');
$v->setOption('culture', 'cs_CZ');
$t->is($v->clean("12,3"),"12.3",'->clean() return the normalized string');
$t->is($v->clean('12,12345678901234'), 12.12345678901234, '->clean() converts strings to normalized numbers');

// this one uses nonbreaking space
$t->is($v->clean('123 456,78'), 123456.78, '->clean() convert grouped numbers');
// single space
$t->is($v->clean('123 456,78'), 123456.78, '->clean() convert grouped numbers');

$t->is($v->clean('100000'), 100000, '->clean() convert grouped numbers');

try
{
  $v->clean('123.456,789.012');
  $t->fail('->clean fails wrong grouped numbers');
}
catch (sfValidatorError $e)
{
  $t->pass('->clean throws a sfValidatorError if the value is grouped wrong');
}

try
{
  $v->clean("12.3");
  $t->fail('->clean() throws a sfValidatorError if the value is not in localized format');
}
catch  (sfValidatorError $e)
{
  $t->pass('->clean() throws a sfValidatorError if the value is not in localized format');
}

$t->diag('required');

$v = new sfValidatorPrice(array(
  'required' => true
));

// ->clean() - no culture
$t->diag('->clean() - standard culture = en');

$v->setOption('culture', 'en');

try
{
  $v->clean(" ");
  $t->fail('->clean() throws a sfValidatorError if the value is empty');
}
catch  (sfValidatorError $e)
{
  $t->pass('->clean() throws a sfValidatorError if the value is empty');
}

try
{
  $value = $v->clean('0.0');
  $t->pass('->clean() throws a sfValidatorError if the value is empty');
  $t->is_deeply($value, 0.0, 'Value is ok');
}
catch(sfValidatorError $e)
{
  $t->fail('->clean() throws a sfValidatorError if the value is empty');
  $t->skip('', 1);
}

// #64
$v->setOption('culture', 'cs_CZ');

try
{
  $value = $v->clean('1635.5');
  $t->is_deeply($value, 1635.5, 'Value is ok');
}
catch(sfValidatorError $e)
{
  $t->fail('->clean() throws a sfValidatorError if the value is empty');
  $t->skip('', 1);
}
