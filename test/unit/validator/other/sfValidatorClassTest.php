<?php

require_once(dirname(__FILE__) . '/../../../bootstrap/unit.php');

$t = new lime_test(9);

$v = new sfValidatorClass();

class foo {}
class bar {}
class foobar extends bar {}
interface loader {}
class baseLoader implements loader {}


// ->clean()
$t->diag('->clean()');
$t->is($v->clean('foo'), 'foo', '->clean() returns the string unmodified if the class exists');

$v->setOption('required', false);
$t->is($v->clean(''), '', '->clean() returns the string unmodified');

$v->setOption('extend', 'bar');

try
{
  $v->clean('foo');
  $t->fail('->clean() throws exception for invalid classname');
}
catch(sfValidatorError $e)
{
  $t->pass('->clean() throws exception for invalid classname');
}

try
{
  $value = $v->clean('foobar');
  $t->pass('->clean() throws does not throw exception for valid classname');
  $t->is($value, 'foobar', '->clean() returns the string unmodified');
}
catch(sfValidatorError $e)
{
  $t->fail('->clean() throws does not throw exception for valid classname');
  $t->skip('', 1);
}

$v->setOption('extend', 'loader');

try
{
  $value = $v->clean('baseLoader');
  $t->pass('->clean() throws does not throw exception for valid classname');
  $t->is($value, 'baseLoader', '->clean() returns the string unmodified');
}
catch(sfValidatorError $e)
{
  $t->fail('->clean() throws does not throw exception for valid classname');
  $t->skip('', 1);
}

$v = new sfValidatorClass(array('extend' => 'bar'));

try
{
  $value = $v->clean('foobar');
  $t->pass('->clean() throws does not throw exception for valid classname');
  $t->is($value, 'foobar', '->clean() returns the string unmodified');
}
catch(sfValidatorError $e)
{
  $t->fail('->clean() throws does not throw exception for valid classname');
  $t->skip('', 1);
}