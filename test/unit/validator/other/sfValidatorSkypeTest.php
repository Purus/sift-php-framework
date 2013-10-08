<?php

require_once(dirname(__FILE__) . '/../../../bootstrap/unit.php');

$t = new lime_test(4);

$v = new sfValidatorSkype(array(), array('invalid' => 'SKYPE is invalid'));

// ->clean()
$t->diag('->clean()');
$t->is($v->clean('foobar'), 'foobar', '->clean() returns the string unmodified');

$v->setOption('required', false);
$t->is($v->clean(''), '', '->clean() returns the string unmodified');

try
{
  $v->clean('_foobar');
  $t->fail('->clean() throws exception for invalid classname');
  $t->skip();
}
catch(sfValidatorError $e)
{
  $t->pass('->clean() throws exception for invalid classname');
  $t->is($e->getMessage(), 'SKYPE is invalid', 'invalid message can be customized');
}


