<?php

require_once(dirname(__FILE__).'/../../bootstrap/unit.php');
require_once(dirname(__FILE__).'/../../../lib/helper/NumberHelper.php');

$t = new lime_test(52, new lime_output_color());

$m1 = new sfMoneyCurrencyValue('100', 'CZK');
$m2 = new sfMoneyCurrencyValue('101', 'CZK');
$m3 = new sfMoneyCurrencyValue('101', 'CZK');
$m4 = new sfMoneyCurrencyValue('101', 'EUR');
$m5 = new sfMoneyCurrencyValue('-100', 'CZK');
$m6 = new sfMoneyCurrencyValue('0', 'CZK');
$m7 = new sfMoneyCurrencyValue('20', 'USD');
$m8 = new sfMoneyCurrencyValue('30', 'USD');
$m9 = new sfMoneyCurrencyValue('10', 'GBP');
$m10 = new sfMoneyCurrencyValue('7900', 'CZK');
$m11 = new sfMoneyCurrencyValue('25.785', 'CZK');
$m12 = new sfMoneyCurrencyValue('308.413', 'EUR');

$t->is((string)$m7->getCurrency(), 'USD', 'getCurrency() works ok');

$t->diag('converter');

try
{
  $convert = new sfMoneyCurrencyConverter('0', 'GBP', 'USD');
  $t->fail('Converter throws an exception if its given invalid conversion rate');
}
catch(Exception $e)
{
  $t->pass('Converter throws an exception if its given invalid conversion rate');
}

try
{
  $convert = new sfMoneyCurrencyConverter('1/2/2', 'GBP', 'USD');
  $t->fail('Converter throws an exception if its given invalid conversion rate');
}
catch(Exception $e)
{
  $t->pass('Converter throws an exception if its given invalid conversion rate');
}

$convertGbpToUsd = new sfMoneyCurrencyConverter(1.5, 'GBP', 'USD');
$convertGbpToUsd->setMoney($m9);

$t->diag('converting between currencies');

$t->is($convertGbpToUsd->getAmount(), '15', 'getAmount() works ok');

$convert = new sfMoneyCurrencyConverter('0.038783', 'CZK', 'EUR');
$convert->setMoney($m11);

$t->is($convert->getAmount(2), '1.00', 'getAmount() returns converted amount in EUR');

$t->diag('createFromIso');

$c = sfMoneyCurrencyConverter::createFromIso('CZK/EUR 1/25.615');
$c->setMoney($m10);

$t->is($c->getAmount(3), '308.413', 'getAmount() returns converted amount in EUR');

$c = sfMoneyCurrencyConverter::createFromIso('CZK/EUR 0.039039625');
$c->setMoney($m10);

$t->is($c->getAmount(3), '308.413', 'getAmount() returns converted amount in EUR');

$c = sfMoneyCurrencyConverter::createFromIso('EUR/CZK 25.615');

$c->setMoney(new sfMoneyCurrencyValue('1', 'EUR'));

$t->is($c->getAmount(3), '25.615', 'getAmount() returns converted amount in EUR');

$c = sfMoneyCurrencyConverter::createFromIso('EUR/CZK 25.615');
$c->setMoney($m12);
$t->is($c->getAmount(2), '7900', 'getAmount() returns converted amount in EUR');

try
{
  $c = sfMoneyCurrencyConverter::createFromIso('EURCZK 25.615');
  $t->fail('createFromIso() throws an exception');
  $t->skip();
}
catch(Exception $e)
{
  $t->pass('createFromIso() throws an exception');
  $t->is($e->getMessage(), 'Error parsing the ISO string "EURCZK 25.615".', 'Exception message makes sense.');
}

$t->diag('isLessThan');

$t->ok($m1->isLessThan($m2) === true, 'isLessThan() works ok');
$t->ok($m2->isLessThan($m1) === false, 'isLessThan() works ok');

$t->diag('isMoreThan');

$t->ok($m1->isMoreThan($m2) === false, 'isMoreThan() works ok');
$t->ok($m2->isMoreThan($m1) === true, 'isMoreThan() works ok');

$t->diag('isEqual');

$t->ok($m1->isEqual($m2) === false, 'isEqual() works ok');
$t->ok($m2->isEqual($m3) === true, 'isEqual() works ok');
$t->ok($m2->isEqual($m4) === false, 'isEqual() works ok when comparing values in different currencies');

$t->diag('isLessThanOrEqual');

$t->ok($m1->isLessThanOrEqual($m2) === true, 'isLessThanOrEqual() works ok');
$t->ok($m1->isLessThanOrEqual($m3) === true, 'isLessThanOrEqual() works ok');
$t->ok($m1->isLessThanOrEqual($m6) === false, 'isLessThanOrEqual() works ok');

$t->diag('isMoreThanOrEqual');

$t->ok($m1->isMoreThanOrEqual($m2) === false, 'isMoreThanOrEqual() works ok');
$t->ok($m1->isMoreThanOrEqual($m3) === false, 'isMoreThanOrEqual() works ok');
$t->ok($m2->isMoreThanOrEqual($m3) === true, 'isMoreThanOrEqual() works ok');

$t->diag('->isPositive() ->isNegative()');

$t->isa_ok($m4->isPositive(), 'boolean', 'isPositive() returns boolean value');
$t->isa_ok($m4->isNegative(), 'boolean', 'isNegative() returns boolean value');

$t->is_deeply($m4->isPositive(), true, 'isPositive() returns true for positive amount');
$t->is_deeply($m4->isNegative(), false, 'isNegative() returns false for positive amount');


$t->is_deeply($m5->isPositive(), false, 'isPositive() returns false for negative amount');
$t->is_deeply($m5->isNegative(), true, 'isNegative() returns true for negative amount');

$t->diag('isZero()');

$t->isa_ok($m4->isZero(), 'boolean', 'isZero() returns boolean value');
$t->is($m4->isZero(), false, 'isZero() returns false for non zero value');
$t->is($m5->isZero(), false, 'isZero() returns false for non zero value');
$t->is($m6->isZero(), true, 'isZero() returns true for zero value');

$t->diag('->add()');

$t->is_deeply($m2->add($m1)->getAmount(), '201', '->add() works ok');
$t->is_deeply($m2->getAmount(), '101', '->add() does not touch the original value');

try
{
  $m1->add($m9); // fails
  $t->fail('add() with different currency throws an exception');
}
catch(Exception $e)
{
  $t->pass('add() with different currency throws an exception');
}

$t->diag('->substract()');

$t->is_deeply($m2->subtract($m1)->getAmount(), '1', '->substract() works ok');
$t->is_deeply($m2->getAmount(), '101', '->substract() does not touch the original value');

$t->diag('->multiply()');

$t->is_deeply($m2->multiply(10)->getAmount(), '1010', '->multiply() works ok');
$t->is_deeply($m2->getAmount(), '101', '->multiply() does not touch the original value');

$t->diag('->divide()');

$t->is_deeply($m2->divide(8)->getAmount(), '12.625', '->divide() works ok');
$t->is_deeply($m2->getAmount(), '101', '->divide() does not touch the original value');

$t->diag('->power()');

$t->is_deeply($m2->power(2)->getAmount(), '10201', '->power() works ok');
$t->is_deeply($m2->getAmount(), '101', '->power() does not touch the original value');

$t->diag('math chainability');

$t->is_deeply($m2->power(2)->subtract($m1)->multiply(3.5)->getAmount(), '35353.5', 'math operations can be chained');
$t->is_deeply($m2->getAmount(), '101', 'math operations does not touch the original value');

$t->diag('formatting manually');

$formatter = new sfI18nNumberFormatter('cs_CZ');
$t->is($formatter->format($m4->getAmount(), 'c', (string)$m4->getCurrency()), '101,00 €', 'formatting works for czech locale');

$formatter = new sfI18nNumberFormatter('en_GB');
$t->is($formatter->format($m4->getAmount(), 'c', (string)$m4->getCurrency()), '€101.00', 'formatting works for english locale');

$formatter = new sfI18nNumberFormatter('de_DE');
$t->is($formatter->format($m4->getAmount(), 'c', (string)$m4->getCurrency()), '101,00 €', 'formatting works for german locale');

$t->diag('formatting the currency object');

$t->is($m4->format('#.00 ¤', 'cs_CZ'), '101,00 €', 'format() formats the value using custom format and czech locale');

$t->is($m4->format('c', 'en_GB'), '€101.00', 'format() formats the value for english locale');
