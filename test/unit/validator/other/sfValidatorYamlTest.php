<?php

require_once(dirname(__FILE__) . '/../../../bootstrap/unit.php');

$t = new lime_test(5);

$v = new sfValidatorYaml();

// ->clean()
$t->diag('->clean()');
$t->is($v->clean('foo'), 'foo', '->clean() returns the string unmodified');

try
{
  // malformed yaml
  $v->clean("encryption_options: {[]}");
  $t->fail('->clean() throws exception for invalid yaml string');
}
catch(sfValidatorError $e)
{
  $t->pass('->clean() throws exception for invalid yaml string');
}

$v->setOption('type', 'array');

try
{
  // malformed yaml
  $v->clean("encryption_options");

  $t->fail('->clean() throws exception for invalid yaml type');
}
catch(sfValidatorError $e)
{
  $t->pass('->clean() throws exception for invalid yaml type');
}

$v = new sfValidatorYaml(array('type' => 'array'), array(
    'invalid_type' => 'Parameters are invalid. Should be array of options.'
));

try
{
  //
  $v->clean('foo');
  $t->fail('->clean() throws exception for invalid yaml type');
  $t->skip('', 1);
}
catch(sfValidatorError $e)
{
  $t->pass('->clean() throws exception for invalid yaml type');
  $t->is($e->getMessage(), 'Parameters are invalid. Should be array of options.', 'Custom message is used.');
}
