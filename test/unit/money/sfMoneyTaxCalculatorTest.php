<?php

require_once(dirname(__FILE__).'/../../bootstrap/unit.php');

$t = new lime_test(7, new lime_output_color());

$c = sfMoneyTaxCalculator::getInstance();

$t->is(get_class($c), 'sfMoneyTaxCalculator', 'calculator is sfMoneyTaxCalculator object');

$t->is_deeply((string)$c->getTaxAmount(new sfMoneyCurrencyValue(7900, 'CZK'), 21), '1371.07', 'getTaxAmount() works for generic calculator and tax 21%');
$t->is_deeply((string)$c->getTaxAmount(new sfMoneyCurrencyValue(7900, 'CZK'), 15), '1030.43', 'getTaxAmount() works for generic calculator and tax 15%');

$t->is_deeply((string)$c->getPriceWithTax(new sfMoneyCurrencyValue('2801.652893', 'CZK'), 21), '3390', 'getPriceWithTax() works for generic calculator and tax 21%');

$c = sfMoneyTaxCalculator::getInstance('CsCoefficient');

$t->is(get_class($c), 'sfMoneyTaxCalculatorDriverCsCoefficient', 'calculator is sfMoneyTaxCalculatorCsCoefficient object');

$t->is_deeply((string)$c->getTaxAmount(new sfMoneyCurrencyValue(7900, 'CZK'), 21), '1371.44', 'getTaxAmount() works for CS calculator and tax 21%');
$t->is_deeply((string)$c->getTaxAmount(new sfMoneyCurrencyValue(7900, 'CZK'), 15), '1030.16', 'getTaxAmount() works for CS calculator and tax 15%');
