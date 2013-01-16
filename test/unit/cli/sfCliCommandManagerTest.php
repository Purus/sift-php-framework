<?php

require_once(dirname(__FILE__).'/../../bootstrap/unit.php');

$t = new lime_test(41);

// __construct()
$t->diag('__construct()');
$argumentSet = new sfCliCommandArgumentSet();
$optionSet = new sfCliCommandOptionSet();

$manager = new sfCliCommandManager();
$t->isa_ok($manager->getArgumentSet(), 'sfCliCommandArgumentSet', '__construct() creates a new sfCliCommandArgumentsSet if none given');
$t->isa_ok($manager->getOptionSet(), 'sfCliCommandOptionSet', '__construct() creates a new sfCliCommandOptionSet if none given');

$manager = new sfCliCommandManager($argumentSet);
$t->is($manager->getArgumentSet(), $argumentSet, '__construct() takes a sfCliCommandArgumentSet as its first argument');
$t->isa_ok($manager->getOptionSet(), 'sfCliCommandOptionSet', '__construct() takes a sfCliCommandArgumentSet as its first argument');

$manager = new sfCliCommandManager($argumentSet, $optionSet);
$t->is($manager->getOptionSet(), $optionSet, '__construct() can take a sfCliCommandOptionSet as its second argument');

// ->setArgumentSet() ->getArgumentSet()
$t->diag('->setArgumentSet() ->getArgumentSet()');
$manager = new sfCliCommandManager(new sfCliCommandArgumentSet());
$argumentSet = new sfCliCommandArgumentSet();
$manager->setArgumentSet($argumentSet);
$t->is($manager->getArgumentSet(), $argumentSet, '->setArgumentSet() sets the manager argument set');

// ->setOptionSet() ->getOptionSet()
$t->diag('->setOptionSet() ->getOptionSet()');
$manager = new sfCliCommandManager(new sfCliCommandArgumentSet());
$optionSet = new sfCliCommandOptionSet();
$manager->setOptionSet($optionSet);
$t->is($manager->getOptionSet(), $optionSet, '->setOptionSet() sets the manager option set');

// ->process()
$t->diag('->process()');
$argumentSet = new sfCliCommandArgumentSet(array(
  new sfCliCommandArgument('foo1', sfCliCommandArgument::REQUIRED),
  new sfCliCommandArgument('foo2', sfCliCommandArgument::OPTIONAL | sfCliCommandArgument::IS_ARRAY),
));
$optionSet = new sfCliCommandOptionSet(array(
  new sfCliCommandOption('foo1', null, sfCliCommandOption::PARAMETER_NONE),
  new sfCliCommandOption('foo2', 'f', sfCliCommandOption::PARAMETER_NONE),
  new sfCliCommandOption('foo3', null, sfCliCommandOption::PARAMETER_OPTIONAL, '', 'default3'),
  new sfCliCommandOption('foo4', null, sfCliCommandOption::PARAMETER_OPTIONAL, '', 'default4'),
  new sfCliCommandOption('foo5', null, sfCliCommandOption::PARAMETER_OPTIONAL, '', 'default5'),
  new sfCliCommandOption('foo6', 'r', sfCliCommandOption::PARAMETER_REQUIRED, '', 'default5'),
  new sfCliCommandOption('foo7', 't', sfCliCommandOption::PARAMETER_REQUIRED, '', 'default7'),
  new sfCliCommandOption('foo8', null, sfCliCommandOption::PARAMETER_REQUIRED | sfCliCommandOption::IS_ARRAY),
  new sfCliCommandOption('foo9', 's', sfCliCommandOption::PARAMETER_OPTIONAL, '', 'default9'),
  new sfCliCommandOption('foo10', 'u', sfCliCommandOption::PARAMETER_OPTIONAL, '', 'default10'),
  new sfCliCommandOption('foo11', 'v', sfCliCommandOption::PARAMETER_OPTIONAL, '', 'default11'),
));
$manager = new sfCliCommandManager($argumentSet, $optionSet);
$manager->process('--foo1 -f --foo3 --foo4="foo4" --foo5=foo5 -r"foo6 foo6" -t foo7 --foo8="foo" --foo8=bar -s -u foo10 -vfoo11 foo1 foo2 foo3 foo4');
$options = array(
  'foo1' => true,
  'foo2' => true,
  'foo3' => 'default3',
  'foo4' => 'foo4',
  'foo5' => 'foo5',
  'foo6' => 'foo6 foo6',
  'foo7' => 'foo7',
  'foo8' => array('foo', 'bar'),
  'foo9' => 'default9',
  'foo10' => 'foo10',
  'foo11' => 'foo11',
);
$arguments = array(
  'foo1' => 'foo1',
  'foo2' => array('foo2', 'foo3', 'foo4')
);
$t->ok($manager->isValid(), '->process() processes CLI options');
$t->is($manager->getOptionValues(), $options, '->process() processes CLI options');
$t->is($manager->getArgumentValues(), $arguments, '->process() processes CLI options');

// ->getOptionValue()
$t->diag('->getOptionValue()');
foreach ($options as $name => $value)
{
  $t->is($manager->getOptionValue($name), $value, '->getOptionValue() returns the value for the given option name');
}

try
{
  $manager->getOptionValue('nonexistant');
  $t->fail('->getOptionValue() throws a sfCliCommandException if the option name does not exist');
}
catch (sfCliCommandException $e)
{
  $t->pass('->getOptionValue() throws a sfCliCommandException if the option name does not exist');
}

// ->getArgumentValue()
$t->diag('->getArgumentValue()');
foreach ($arguments as $name => $value)
{
  $t->is($manager->getArgumentValue($name), $value, '->getArgumentValue() returns the value for the given argument name');
}

try
{
  $manager->getArgumentValue('nonexistant');
  $t->fail('->getArgumentValue() throws a sfCliCommandException if the argument name does not exist');
}
catch (sfCliCommandException $e)
{
  $t->pass('->getArgumentValue() throws a sfCliCommandException if the argument name does not exist');
}

// ->isValid() ->getErrors()
$t->diag('->isValid() ->getErrors()');
$argumentSet = new sfCliCommandArgumentSet();
$manager = new sfCliCommandManager($argumentSet);
$manager->process('foo');
$t->ok(!$manager->isValid(), '->isValid() returns false if the options are not valid');
$t->is(count($manager->getErrors()), 1, '->getErrors() returns an array of errors');

$argumentSet = new sfCliCommandArgumentSet(array(new sfCliCommandArgument('foo', sfCliCommandArgument::REQUIRED)));
$manager = new sfCliCommandManager($argumentSet);
$manager->process('');
$t->ok(!$manager->isValid(), '->isValid() returns false if the options are not valid');
$t->is(count($manager->getErrors()), 1, '->getErrors() returns an array of errors');

$optionSet = new sfCliCommandOptionSet(array(new sfCliCommandOption('foo', null, sfCliCommandOption::PARAMETER_REQUIRED)));
$manager = new sfCliCommandManager(null, $optionSet);
$manager->process('--foo');
$t->ok(!$manager->isValid(), '->isValid() returns false if the options are not valid');
$t->is(count($manager->getErrors()), 1, '->getErrors() returns an array of errors');

$optionSet = new sfCliCommandOptionSet(array(new sfCliCommandOption('foo', 'f', sfCliCommandOption::PARAMETER_REQUIRED)));
$manager = new sfCliCommandManager(null, $optionSet);
$manager->process('-f');
$t->ok(!$manager->isValid(), '->isValid() returns false if the options are not valid');
$t->is(count($manager->getErrors()), 1, '->getErrors() returns an array of errors');

$optionSet = new sfCliCommandOptionSet(array(new sfCliCommandOption('foo', null, sfCliCommandOption::PARAMETER_NONE)));
$manager = new sfCliCommandManager(null, $optionSet);
$manager->process('--foo="bar"');
$t->ok(!$manager->isValid(), '->isValid() returns false if the options are not valid');
$t->is(count($manager->getErrors()), 1, '->getErrors() returns an array of errors');

$manager = new sfCliCommandManager();
$manager->process('--bar');
$t->ok(!$manager->isValid(), '->isValid() returns false if the options are not valid');
$t->is(count($manager->getErrors()), 1, '->getErrors() returns an array of errors');

$manager = new sfCliCommandManager();
$manager->process('-b');
$t->ok(!$manager->isValid(), '->isValid() returns false if the options are not valid');
$t->is(count($manager->getErrors()), 1, '->getErrors() returns an array of errors');

$manager = new sfCliCommandManager();
$manager->process('--bar="foo"');
$t->ok(!$manager->isValid(), '->isValid() returns false if the options are not valid');
$t->is(count($manager->getErrors()), 1, '->getErrors() returns an array of errors');
