<?php

require_once(dirname(__FILE__).'/../../bootstrap/unit.php');

$t = new lime_test(87, new lime_output_color());

// __construct()
$t->diag('__construct()');
try
{
  $c = new sfI18nNumberFormat();
  $t->fail('__construct() takes a mandatory ICU array as its first argument');
}
catch (sfException $e)
{
  $t->pass('__construct() takes a mandatory ICU array as its first argument');
}

// ::getInstance()
$t->diag('::getInstance()');
$t->isa_ok(sfI18nNumberFormat::getInstance(), 'sfI18nNumberFormat', '::getInstance() returns an sfI18nNumberFormat instance');
$c = new sfCulture();
$t->is(sfI18nNumberFormat::getInstance($c), $c->getNumberFormat(), '::getInstance() can take a sfCulture instance as its first argument');
$t->isa_ok(sfI18nNumberFormat::getInstance('fr'), 'sfI18nNumberFormat', '::getInstance() can take a culture as its first argument');
$n = sfI18nNumberFormat::getInstance();
$n->setPattern(sfI18nNumberFormat::PERCENTAGE);
$t->is(sfI18nNumberFormat::getInstance(null, sfI18nNumberFormat::PERCENTAGE)->getPattern(), $n->getPattern(), '::getInstance() can take a formatting type as its second argument');

// ->getPattern() ->setPattern()
$t->diag('->getPattern() ->setPattern()');
$n = sfI18nNumberFormat::getInstance();
$n1 = sfI18nNumberFormat::getInstance();
$n->setPattern(sfI18nNumberFormat::CURRENCY);
$n1->setPattern(sfI18nNumberFormat::PERCENTAGE);
$t->isnt($n->getPattern(), $n1->getPattern(), '->getPattern() ->setPattern() changes the current pattern');

$n = sfI18nNumberFormat::getInstance();
$n1 = sfI18nNumberFormat::getInstance();
$n->Pattern = sfI18nNumberFormat::CURRENCY;
$n1->setPattern(sfI18nNumberFormat::CURRENCY);
$t->is($n->getPattern(), $n1->getPattern(), '->setPattern() is equivalent to ->Pattern = ');
$t->is($n->getPattern(), $n->Pattern, '->getPattern() is equivalent to ->Pattern');

// ::getCurrencyInstance()
$t->diag('::getCurrencyInstance()');
$t->is(sfI18nNumberFormat::getCurrencyInstance()->getPattern(), sfI18nNumberFormat::getInstance(null, sfI18nNumberFormat::CURRENCY)->getPattern(), '::getCurrencyInstance() is a shortcut for ::getInstance() and type sfI18nNumberFormat::CURRENCY');

// ::getPercentageInstance()
$t->diag('::getPercentageInstance()');
$t->is(sfI18nNumberFormat::getPercentageInstance()->getPattern(), sfI18nNumberFormat::getInstance(null, sfI18nNumberFormat::PERCENTAGE)->getPattern(), '::getPercentageInstance() is a shortcut for ::getInstance() and type sfI18nNumberFormat::PERCENTAGE');

// ::getScientificInstance()
$t->diag('::getScientificInstance()');
$t->is(sfI18nNumberFormat::getScientificInstance()->getPattern(), sfI18nNumberFormat::getInstance(null, sfI18nNumberFormat::SCIENTIFIC)->getPattern(), '::getScientificInstance() is a shortcut for ::getInstance() and type sfI18nNumberFormat::SCIENTIFIC');

// setters/getters
foreach (array(
  'DecimalDigits', 'DecimalSeparator', 'GroupSeparator',
  'CurrencySymbol', 'NegativeInfinitySymbol', 'PositiveInfinitySymbol',
  'NegativeSign', 'PositiveSign', 'NaNSymbol', 'PercentSymbol', 'PerMilleSymbol',
) as $method)
{
  $t->diag(sprintf('->get%s() ->set%s()', $method, $method));
  $n = sfI18nNumberFormat::getInstance();
  $setter = 'set'.$method;
  $getter = 'get'.$method;
  $n->$setter('foo');
  $t->is($n->$getter(), 'foo', sprintf('->%s() sets the current decimal digits', $setter));
  $t->is($n->$method, $n->$getter(), sprintf('->%s() is equivalent to ->%s', $getter, $method));
  $n->$method = 'bar';
  $t->is($n->$getter(), 'bar', sprintf('->%s() is equivalent to ->%s = ', $setter, $method));
}

foreach (array('GroupSizes', 'NegativePattern', 'PositivePattern') as $method)
{
  $t->diag(sprintf('->get%s() ->set%s()', $method, $method));
  $n = sfI18nNumberFormat::getInstance();
  $setter = 'set'.$method;
  $getter = 'get'.$method;
  $n->$setter(array('foo', 'foo'));
  $t->is($n->$getter(), array('foo', 'foo'), sprintf('->%s() sets the current decimal digits', $setter));
  $t->is($n->$method, $n->$getter(), sprintf('->%s() is equivalent to ->%s', $getter, $method));
  $n->$method = array('bar', 'bar');
  $t->is($n->$getter(), array('bar', 'bar'), sprintf('->%s() is equivalent to ->%s = ', $setter, $method));
}

$tests = array(
  'fr' => array(
    'DecimalDigits'          => -1,
    'DecimalSeparator'       => ',',
    'GroupSeparator'         => ' ',
    'CurrencySymbol'         => '$US',
    'NegativeInfinitySymbol' => '-∞',
    'PositiveInfinitySymbol' => '+∞',
    'NegativeSign'           => '-',
    'PositiveSign'           => '+',
    'NaNSymbol'              => 'NaN',
    'PercentSymbol'          => '%',
    'PerMilleSymbol'         => '‰',
  ),
  'en' => array(
    'DecimalDigits'          => -1,
    'DecimalSeparator'       => '.',
    'GroupSeparator'         => ',',
    'CurrencySymbol'         => '$',
    'NegativeInfinitySymbol' => '-∞',
    'PositiveInfinitySymbol' => '+∞',
    'NegativeSign'           => '-',
    'PositiveSign'           => '+',
    'NaNSymbol'              => 'NaN',
    'PercentSymbol'          => '%',
    'PerMilleSymbol'         => '‰',
  ),
);

foreach ($tests as $culture => $fixtures)
{
  $n = sfI18nNumberFormat::getInstance($culture);

  foreach ($fixtures as $method => $result)
  {
    $getter = 'get'.$method;
    $t->is($n->$getter(), $result, sprintf('->%s() returns "%s" for culture "%s"', $getter, $result, $culture));
  }
}


$t->diag('->getNumber()');
$t->is(sfI18nNumberFormat::getNumber('1,5', 'cs_CZ'), '1.5', 'getNumber() works for czech locale');

$t->is(sfI18nNumberFormat::getNumber('1 000,5', 'cs_CZ'), '1000.5', 'getNumber() works for czech locale');
$t->is(sfI18nNumberFormat::getNumber('1000,5', 'cs_CZ'), '1000.5', 'getNumber() works for czech locale');
$t->is(sfI18nNumberFormat::getNumber('0,0', 'cs_CZ'), '0.0', 'getNumber() works for czech locale');

try
{
  sfI18nNumberFormat::getNumber('1000.5', 'cs_CZ');
  $t->fail('When trying invalid value for given culture the exception is thrown');
}
catch(Exception $e)
{
  $t->pass('When trying invalid value for given culture the exception is thrown');
}

$t->is(sfI18nNumberFormat::getNumber('0,0', 'sk_SK'), '0.0', 'getNumber() works for slovak locale');
$t->is(sfI18nNumberFormat::getNumber('1000,5', 'sk_SK'), '1000.5', 'getNumber() works for slovak locale');

try
{
  sfI18nNumberFormat::getNumber('1000.5', 'sk_SK');
  $t->fail('When trying invalid value for given culture the exception is thrown');
}
catch(Exception $e)
{
  $t->pass('When trying invalid value for given culture the exception is thrown');
}

$t->is(sfI18nNumberFormat::getNumber('1000.5', 'en'), '1000.5', 'getNumber() works for english locale');
$t->is(sfI18nNumberFormat::getNumber('0', 'en'), '0', 'getNumber() works for english locale');
$t->is(sfI18nNumberFormat::getNumber('0.0', 'en'), '0.0', 'getNumber() works for english locale');

try
{
  sfI18nNumberFormat::getNumber('1000,5', 'en');
  $t->fail('When trying invalid value for given culture the exception is thrown');
}
catch(Exception $e)
{
  $t->pass('When trying invalid value for given culture the exception is thrown');
}
