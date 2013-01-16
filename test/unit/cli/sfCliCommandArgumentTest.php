<?php

require_once(dirname(__FILE__).'/../../bootstrap/unit.php');

$t = new lime_test(16);

// __construct()
$t->diag('__construct()');
$argument = new sfCliCommandArgument('foo');
$t->is($argument->getName(), 'foo', '__construct() takes a name as its first argument');

// mode argument
$argument = new sfCliCommandArgument('foo');
$t->is($argument->isRequired(), false, '__construct() gives a "sfCliCommandArgument::OPTIONAL" mode by default');

$argument = new sfCliCommandArgument('foo', null);
$t->is($argument->isRequired(), false, '__construct() can take "sfCliCommandArgument::OPTIONAL" as its mode');

$argument = new sfCliCommandArgument('foo', sfCliCommandArgument::OPTIONAL);
$t->is($argument->isRequired(), false, '__construct() can take "sfCliCommandArgument::PARAMETER_OPTIONAL" as its mode');

$argument = new sfCliCommandArgument('foo', sfCliCommandArgument::REQUIRED);
$t->is($argument->isRequired(), true, '__construct() can take "sfCliCommandArgument::PARAMETER_REQUIRED" as its mode');

try
{
  $argument = new sfCliCommandArgument('foo', 'ANOTHER_ONE');
  $t->fail('__construct() throws an sfCliCommandException if the mode is not valid');
}
catch (sfCliCommandException $e)
{
  $t->pass('__construct() throws an sfCliCommandException if the mode is not valid');
}

// ->isArray()
$t->diag('->isArray()');
$argument = new sfCliCommandArgument('foo', sfCliCommandArgument::IS_ARRAY);
$t->ok($argument->isArray(), '->isArray() returns true if the argument can be an array');
$argument = new sfCliCommandArgument('foo', sfCliCommandArgument::OPTIONAL | sfCliCommandArgument::IS_ARRAY);
$t->ok($argument->isArray(), '->isArray() returns true if the argument can be an array');
$argument = new sfCliCommandArgument('foo', sfCliCommandArgument::OPTIONAL);
$t->ok(!$argument->isArray(), '->isArray() returns false if the argument can not be an array');

// ->getHelp()
$t->diag('->getHelp()');
$argument = new sfCliCommandArgument('foo', null, 'Some help');
$t->is($argument->getHelp(), 'Some help', '->getHelp() return the message help');

// ->getDefault()
$t->diag('->getDefault()');
$argument = new sfCliCommandArgument('foo', sfCliCommandArgument::OPTIONAL, '', 'default');
$t->is($argument->getDefault(), 'default', '->getDefault() return the default value');

// ->setDefault()
$t->diag('->setDefault()');
$argument = new sfCliCommandArgument('foo', sfCliCommandArgument::OPTIONAL, '', 'default');
$argument->setDefault(null);
$t->ok(is_null($argument->getDefault()), '->setDefault() can reset the default value by passing null');
$argument->setDefault('another');
$t->is($argument->getDefault(), 'another', '->setDefault() changes the default value');

$argument = new sfCliCommandArgument('foo', sfCliCommandArgument::OPTIONAL | sfCliCommandArgument::IS_ARRAY);
$argument->setDefault(array(1, 2));
$t->is($argument->getDefault(), array(1, 2), '->setDefault() changes the default value');

try
{
  $argument = new sfCliCommandArgument('foo', sfCliCommandArgument::REQUIRED);
  $argument->setDefault('default');
  $t->fail('->setDefault() throws an sfCliCommandException if you give a default value for a required argument');
}
catch (sfCliCommandException $e)
{
  $t->pass('->setDefault() throws an sfCliCommandException if you give a default value for a required argument');
}

try
{
  $argument = new sfCliCommandArgument('foo', sfCliCommandArgument::IS_ARRAY);
  $argument->setDefault('default');
  $t->fail('->setDefault() throws an sfCliCommandException if you give a default value which is not an array for a IS_ARRAY option');
}
catch (sfCliCommandException $e)
{
  $t->pass('->setDefault() throws an sfCliCommandException if you give a default value which is not an array for a IS_ARRAY option');
}
