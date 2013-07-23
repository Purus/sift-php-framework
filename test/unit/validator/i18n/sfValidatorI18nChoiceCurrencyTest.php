<?php

require_once(dirname(__FILE__).'/../../../bootstrap/unit.php');

$t = new lime_test(4, new lime_output_color());

$v = new sfValidatorI18nChoiceCurrency();

$t->diag('->clean()');

$t->is($v->clean('USD'), 'USD', '->clean() works ok for valid currency code');
$t->is($v->clean('CZK'), 'CZK', '->clean() works ok for valid currency code');

try
{
  $v->clean('FOO');
  $t->fail('->clean fails with wrong county code');
}
catch (sfValidatorError $e)
{
  $t->pass('->clean throws a sfValidatorError if the value is wrong');
}

$v = new sfValidatorI18nChoiceCurrency(array('currencies' => array(
  'CZK'
)));

try
{
  $v->clean('USD');
  $t->fail('->clean fails with code which was not allowed');
}
catch (sfValidatorError $e)
{
  $t->pass('->clean throws a sfValidatorError if the value was not allowed');
}
