<?php

require_once(dirname(__FILE__).'/../../../bootstrap/unit.php');

$t = new lime_test(27);

$v1 = new sfValidatorString(array('max_length' => 3));
$v2 = new sfValidatorString(array('min_length' => 3));

$options = array(
  'cultures' => array('cs_CZ')
);

$v = new sfValidatorI18nAggregate(array($v1, $v2), $options);

// __construct()
$t->diag('__construct()');
$v = new sfValidatorI18nAggregate($v1, $options);
$t->is($v->getValidators(), array($v1), '->__construct() can take a validator as its first argument');
$v = new sfValidatorI18nAggregate(array($v1, $v2), $options);
$t->is($v->getValidators(), array($v1, $v2), '->__construct() can take an array of validators as its first argument');
try
{
  $v = new sfValidatorI18nAggregate('string', $options);
  $t->fail('__construct() throws an exception when passing a non supported first argument');
}
catch (InvalidArgumentException $e)
{
  $t->pass('__construct() throws an exception when passing a non supported first argument');
}

// ->addValidator()
$t->diag('->addValidator()');
$v = new sfValidatorI18nAggregate(null, $options);
$v->addValidator($v1);
$v->addValidator($v2);
$t->is($v->getValidators(), array($v1, $v2), '->addValidator() adds a validator');

// ->clean()
$t->diag('->clean()');
$t->is($v->clean(array('cs_CZ' => 'foo')), array('cs_CZ' => 'foo'), '->clean() returns the string unmodified');

try
{
  $v->setOption('required', true);
  $v->clean(null);
  $t->fail('->clean() throws an sfValidatorError exception if the input value is required');
  $t->skip('', 1);
}
catch (sfValidatorError $e)
{
  $t->pass('->clean() throws an sfValidatorError exception if the input value is required');
  $t->is($e->getCode(), 'required', '->clean() throws a sfValidatorError');
}

$v2->setOption('max_length', 2);
try
{
  $v->clean(array('cs_CZ' => 'foo'));
  $t->fail('->clean() throws an sfValidatorError exception if one of the validators fails');
  $t->skip('', 2);
}
catch (sfValidatorError $e)
{
  $t->pass('->clean() throws an sfValidatorError exception if one of the validators fails');
  $t->is($e[0]->getCode(), 'max_length', '->clean() throws a sfValidatorSchemaError');
  $t->is($e instanceof sfValidatorErrorSchema, 'max_length', '->clean() throws a sfValidatorSchemaError');
}

$v1->setOption('max_length', 2);
try
{
  $v->clean(array('cs_CZ' => 'foo'));
  $t->fail('->clean() throws an sfValidatorError exception if one of the validators fails');
  $t->skip('', 4);
}
catch (sfValidatorError $e)
{
  $t->pass('->clean() throws an sfValidatorError exception if one of the validators fails');
  $t->is(count($e), 2, '->clean() throws an error for every error');
  $t->is($e[0]->getCode(), 'max_length', '->clean() throws a sfValidatorSchemaError');
  $t->is($e[1]->getCode(), 'max_length', '->clean() throws a sfValidatorSchemaError');
  $t->is($e instanceof sfValidatorErrorSchema, 'max_length', '->clean() throws a sfValidatorSchemaError');
}

$v->setOption('halt_on_error', true);
try
{
  $v->clean(array('cs_CZ' => 'foo'));
  $t->fail('->clean() throws an sfValidatorError exception if one of the validators fails');
  $t->skip('', 3);
}
catch (sfValidatorError $e)
{
  $t->pass('->clean() throws an sfValidatorError exception if one of the validators fails');
  $t->is(count($e), 1, '->clean() only returns the first error if halt_on_error option is true');
  $t->is($e[0]->getCode(), 'max_length', '->clean() throws a sfValidatorSchemaError');
  $t->is($e instanceof sfValidatorErrorSchema, 'max_length', '->clean() throws a sfValidatorSchemaError');
}

try
{
  $v->setMessage('invalid', 'This value is invalid.');
  $v->clean(array('cs_CZ' => 'foo'));
  $t->fail('->clean() throws an sfValidatorError exception if one of the validators fails');
  $t->skip('', 2);
}
catch (sfValidatorError $e)
{
  $t->pass('->clean() throws an sfValidatorError exception if one of the validators fails');
  $t->is($e->getCode(), 'invalid', '->clean() throws a sfValidatorError if invalid message is not empty');
  $t->is(!$e instanceof sfValidatorErrorSchema, 'max_length', '->clean() throws a sfValidatorError if invalid message is not empty');
}

// ->asString()
$t->diag('->asString()');
$v1 = new sfValidatorString(array('max_length' => 3));
$v2 = new sfValidatorString(array('min_length' => 3));
$v = new sfValidatorI18nAggregate(array($v1, $v2), $options);
$t->is($v->asString(), "(\n  String({ max_length: 3 })\n  and({ cultures: [cs_CZ] })\n  String({ min_length: 3 })\n)"
, '->asString() returns a string representation of the validator');

$v = new sfValidatorI18nAggregate(array($v1, $v2), $options, array('required' => 'This is required.'));
$t->is($v->asString(), "(\n  String({ max_length: 3 })\n  and({ cultures: [cs_CZ] }, { required: 'This is required.' })\n  String({ min_length: 3 })\n)"
, '->asString() returns a string representation of the validator');


// Additional options

$v1 = new sfValidatorString(array('max_length' => 3));
$v2 = new sfValidatorString(array('min_length' => 3));

$options = array(
  'cultures' => array('cs_CZ', 'en_GB'),
  'all_need_to_pass' => true,
  // break on first error
  'halt_on_error' => true
);

$v = new sfValidatorI18nAggregate(array($v1, $v2), $options);

try
{
  $v->clean(array('cs_CZ' => 'foo', 'en_GB' => 'a'));
  $t->fail('error schema is thrown');
}
catch(sfValidatorErrorSchema $e)
{
  $t->pass('->clean() error schema is thrown when all_need_to_pass attribute is false');

}

$v->setOption('all_need_to_pass', false);

try
{
  $v->clean(array('cs_CZ' => 'foo', 'en_GB' => 'a'));
  $t->pass('->clean() error schema is thrown when all_need_to_pass attribute is false');
}
catch(sfValidatorErrorSchema $e)
{
  $t->fail('error schema is thrown');
}

$v->setOption('all_need_to_pass', true);
$v->setOption('halt_on_error', true);

try
{
  $v->clean(array('cs_CZ' => 'foo', 'en_GB' => 'a'));
  $t->fail('error schema is thrown');
}
catch(sfValidatorErrorSchema $e)
{
  $t->pass('->clean() error schema is thrown when all_need_to_pass attribute is false');

}


