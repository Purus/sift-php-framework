<?php

/*
 * This file is part of the symfony package.
 * (c) 2004-2006 Fabien Potencier <fabien.potencier@symfony-project.com>
 * 
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

require_once(dirname(__FILE__).'/../../bootstrap/unit.php');

$t = new lime_test(20);

$foo = new sfCliCommandOption('foo', 'f');
$bar = new sfCliCommandOption('bar', 'b');
$foo1 = new sfCliCommandOption('fooBis', 'f');
$foo2 = new sfCliCommandOption('foo', 'p');

// __construct()
$t->diag('__construct()');
$optionSet = new sfCliCommandOptionSet();
$t->is($optionSet->getOptions(), array(), '__construct() creates a new sfCliCommandOptionSet object');

$optionSet = new sfCliCommandOptionSet(array($foo, $bar));
$t->is($optionSet->getOptions(), array('foo' => $foo, 'bar' => $bar), '__construct() takes an array of sfCliCommandOption objects as its first argument');

// ->setOptions()
$t->diag('->setOptions()');
$optionSet = new sfCliCommandOptionSet();
$optionSet->setOptions(array($foo));
$t->is($optionSet->getOptions(), array('foo' => $foo), '->setOptions() sets the array of sfCliCommandOption objects');
$optionSet->setOptions(array($bar));
$t->is($optionSet->getOptions(), array('bar' => $bar), '->setOptions() clears all sfCliCommandOption objects');
try
{
  $optionSet->getOptionForShortcut('f');
  $t->fail('->setOptions() clears all sfCliCommandOption objects');
}
catch (sfCliCommandException $e)
{
  $t->pass('->setOptions() clears all sfCliCommandOption objects');
}

// ->addOptions()
$t->diag('->addOptions()');
$optionSet = new sfCliCommandOptionSet();
$optionSet->addOptions(array($foo));
$t->is($optionSet->getOptions(), array('foo' => $foo), '->addOptions() adds an array of sfCliCommandOption objects');
$optionSet->addOptions(array($bar));
$t->is($optionSet->getOptions(), array('foo' => $foo, 'bar' => $bar), '->addOptions() does not clear existing sfCliCommandOption objects');

// ->addOption()
$t->diag('->addOption()');
$optionSet = new sfCliCommandOptionSet();
$optionSet->addOption($foo);
$t->is($optionSet->getOptions(), array('foo' => $foo), '->addOption() adds a sfCliCommandOption object');
$optionSet->addOption($bar);
$t->is($optionSet->getOptions(), array('foo' => $foo, 'bar' => $bar), '->addOption() adds a sfCliCommandOption object');
try
{
  $optionSet->addOption($foo2);
  $t->fail('->addOption() throws a sfCliCommandException if the another option is already registered with the same name');
}
catch (sfCliCommandException $e)
{
  $t->pass('->addOption() throws a sfCliCommandException if the another option is already registered with the same name');
}
try
{
  $optionSet->addOption($foo1);
  $t->fail('->addOption() throws a sfCliCommandException if the another option is already registered with the same shortcut');
}
catch (sfCliCommandException $e)
{
  $t->pass('->addOption() throws a sfCliCommandException if the another option is already registered with the same shortcut');
}

// ->getOption()
$t->diag('->getOption()');
$optionSet = new sfCliCommandOptionSet();
$optionSet->addOptions(array($foo));
$t->is($optionSet->getOption('foo'), $foo, '->getOption() returns a sfCliCommandOption by its name');
try
{
  $optionSet->getOption('bar');
  $t->fail('->getOption() throws an exception if the option name does not exist');
}
catch (sfCliCommandException $e)
{
  $t->pass('->getOption() throws an exception if the option name does not exist');
}

// ->hasOption()
$t->diag('->hasOption()');
$optionSet = new sfCliCommandOptionSet();
$optionSet->addOptions(array($foo));
$t->is($optionSet->hasOption('foo'), true, '->hasOption() returns true if a sfCliCommandOption exists for the given name');
$t->is($optionSet->hasOption('bar'), false, '->hasOption() returns false if a sfCliCommandOption exists for the given name');

// ->hasShortcut()
$t->diag('->hasShortcut()');
$optionSet = new sfCliCommandOptionSet();
$optionSet->addOptions(array($foo));
$t->is($optionSet->hasShortcut('f'), true, '->hasShortcut() returns true if a sfCliCommandOption exists for the given shortcut');
$t->is($optionSet->hasShortcut('b'), false, '->hasShortcut() returns false if a sfCliCommandOption exists for the given shortcut');

// ->getOptionForShortcut()
$t->diag('->getOptionForShortcut()');
$optionSet = new sfCliCommandOptionSet();
$optionSet->addOptions(array($foo));
$t->is($optionSet->getOptionForShortcut('f'), $foo, '->getOptionForShortcut() returns a sfCliCommandOption by its shortcut');
try
{
  $optionSet->getOptionForShortcut('l');
  $t->fail('->getOption() throws an exception if the shortcut does not exist');
}
catch (sfCliCommandException $e)
{
  $t->pass('->getOption() throws an exception if the shortcut does not exist');
}

// ->getDefaults()
$t->diag('->getDefaults()');
$optionSet = new sfCliCommandOptionSet();
$optionSet->addOptions(array(
  new sfCliCommandOption('foo1', null, sfCliCommandOption::PARAMETER_NONE),
  new sfCliCommandOption('foo2', null, sfCliCommandOption::PARAMETER_REQUIRED),
  new sfCliCommandOption('foo3', null, sfCliCommandOption::PARAMETER_REQUIRED, '', 'default'),
  new sfCliCommandOption('foo4', null, sfCliCommandOption::PARAMETER_OPTIONAL),
  new sfCliCommandOption('foo5', null, sfCliCommandOption::PARAMETER_OPTIONAL, '', 'default'),
  new sfCliCommandOption('foo6', null, sfCliCommandOption::PARAMETER_OPTIONAL | sfCliCommandOption::IS_ARRAY),
  new sfCliCommandOption('foo7', null, sfCliCommandOption::PARAMETER_OPTIONAL | sfCliCommandOption::IS_ARRAY, '', array(1, 2)),
));
$defaults = array(
  'foo1' => null,
  'foo2' => null,
  'foo3' => 'default',
  'foo4' => null,
  'foo5' => 'default',
  'foo6' => array(),
  'foo7' => array(1, 2),
);
$t->is($optionSet->getDefaults(), $defaults, '->getDefaults() returns the default values for all options');
