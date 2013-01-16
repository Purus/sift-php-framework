<?php

require_once(dirname(__FILE__).'/../../bootstrap/unit.php');

$t = new lime_test(21);

$foo = new sfCliCommandArgument('foo');
$bar = new sfCliCommandArgument('bar');
$foo1 = new sfCliCommandArgument('foo');
$foo2 = new sfCliCommandArgument('foo2', sfCliCommandArgument::REQUIRED);

// __construct()
$t->diag('__construct()');
$argumentSet = new sfCliCommandArgumentSet();
$t->is($argumentSet->getArguments(), array(), '__construct() creates a new sfCliCommandArgumentSet object');

$argumentSet = new sfCliCommandArgumentSet(array($foo, $bar));
$t->is($argumentSet->getArguments(), array('foo' => $foo, 'bar' => $bar), '__construct() takes an array of sfCliCommandArgument objects as its first argument');

// ->setArguments()
$t->diag('->setArguments()');
$argumentSet = new sfCliCommandArgumentSet();
$argumentSet->setArguments(array($foo));
$t->is($argumentSet->getArguments(), array('foo' => $foo), '->setArguments() sets the array of sfCliCommandArgument objects');
$argumentSet->setArguments(array($bar));

$t->is($argumentSet->getArguments(), array('bar' => $bar), '->setArguments() clears all sfCliCommandArgument objects');

// ->addArguments()
$t->diag('->addArguments()');
$argumentSet = new sfCliCommandArgumentSet();
$argumentSet->addArguments(array($foo));
$t->is($argumentSet->getArguments(), array('foo' => $foo), '->addArguments() adds an array of sfCliCommandArgument objects');
$argumentSet->addArguments(array($bar));
$t->is($argumentSet->getArguments(), array('foo' => $foo, 'bar' => $bar), '->addArguments() does not clear existing sfCliCommandArgument objects');

// ->addArgument()
$t->diag('->addArgument()');
$argumentSet = new sfCliCommandArgumentSet();
$argumentSet->addArgument($foo);
$t->is($argumentSet->getArguments(), array('foo' => $foo), '->addArgument() adds a sfCliCommandArgument object');
$argumentSet->addArgument($bar);
$t->is($argumentSet->getArguments(), array('foo' => $foo, 'bar' => $bar), '->addArgument() adds a sfCliCommandArgument object');

// arguments must have different names
try
{
  $argumentSet->addArgument($foo1);
  $t->fail('->addArgument() throws a sfCliCommandException if another argument is already registered with the same name');
}
catch (sfCliCommandException $e)
{
  $t->pass('->addArgument() throws a sfCliCommandException if another argument is already registered with the same name');
}

// cannot add a parameter after an array parameter
$argumentSet->addArgument(new sfCliCommandArgument('fooarray', sfCliCommandArgument::IS_ARRAY));
try
{
  $argumentSet->addArgument(new sfCliCommandArgument('anotherbar'));
  $t->fail('->addArgument() throws a sfCliCommandException if there is an array parameter already registered');
}
catch (sfCliCommandException $e)
{
  $t->pass('->addArgument() throws a sfCliCommandException if there is an array parameter already registered');
}

// cannot add a required argument after an optional one
$argumentSet = new sfCliCommandArgumentSet();
$argumentSet->addArgument($foo);
try
{
  $argumentSet->addArgument($foo2);
  $t->fail('->addArgument() throws an exception if you try to add a required argument after an optional one');
}
catch (sfCliCommandException $e)
{
  $t->pass('->addArgument() throws an exception if you try to add a required argument after an optional one');
}

// ->getArgument()
$t->diag('->getArgument()');
$argumentSet = new sfCliCommandArgumentSet();
$argumentSet->addArguments(array($foo));
$t->is($argumentSet->getArgument('foo'), $foo, '->getArgument() returns a sfCliCommandArgument by its name');
try
{
  $argumentSet->getArgument('bar');
  $t->fail('->getArgument() throws an exception if the Argument name does not exist');
}
catch (sfCliCommandException $e)
{
  $t->pass('->getArgument() throws an exception if the Argument name does not exist');
}

// ->hasArgument()
$t->diag('->hasArgument()');
$argumentSet = new sfCliCommandArgumentSet();
$argumentSet->addArguments(array($foo));
$t->is($argumentSet->hasArgument('foo'), true, '->hasArgument() returns true if a sfCliCommandArgument exists for the given name');
$t->is($argumentSet->hasArgument('bar'), false, '->hasArgument() returns false if a sfCliCommandArgument exists for the given name');

// ->getArgumentRequiredCount()
$t->diag('->getArgumentRequiredCount()');
$argumentSet = new sfCliCommandArgumentSet();
$argumentSet->addArgument($foo2);
$t->is($argumentSet->getArgumentRequiredCount(), 1, '->getArgumentRequiredCount() returns the number of required arguments');
$argumentSet->addArgument($foo);
$t->is($argumentSet->getArgumentRequiredCount(), 1, '->getArgumentRequiredCount() returns the number of required arguments');

// ->getArgumentCount()
$t->diag('->getArgumentCount()');
$argumentSet = new sfCliCommandArgumentSet();
$argumentSet->addArgument($foo2);
$t->is($argumentSet->getArgumentCount(), 1, '->getArgumentCount() returns the number of arguments');
$argumentSet->addArgument($foo);
$t->is($argumentSet->getArgumentCount(), 2, '->getArgumentCount() returns the number of arguments');

// ->getDefaults()
$t->diag('->getDefaults()');
$argumentSet = new sfCliCommandArgumentSet();
$argumentSet->addArguments(array(
  new sfCliCommandArgument('foo1', sfCliCommandArgument::OPTIONAL),
  new sfCliCommandArgument('foo2', sfCliCommandArgument::OPTIONAL, '', 'default'),
  new sfCliCommandArgument('foo3', sfCliCommandArgument::OPTIONAL | sfCliCommandArgument::IS_ARRAY),
//  new sfCliCommandArgument('foo4', sfCliCommandArgument::OPTIONAL | sfCliCommandArgument::IS_ARRAY, '', array(1, 2)),
));
$t->is($argumentSet->getDefaults(), array('foo1' => null, 'foo2' => 'default', 'foo3' => array()), '->getDefaults() return the default values for each argument');

$argumentSet = new sfCliCommandArgumentSet();
$argumentSet->addArguments(array(
  new sfCliCommandArgument('foo4', sfCliCommandArgument::OPTIONAL | sfCliCommandArgument::IS_ARRAY, '', array(1, 2)),
));
$t->is($argumentSet->getDefaults(), array('foo4' => array(1, 2)), '->getDefaults() return the default values for each argument');
