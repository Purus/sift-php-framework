<?php

require_once(dirname(__FILE__).'/../../bootstrap/unit.php');

$t = new lime_test(6, new lime_output_color());

$t->isa_ok(sfISO4217::isValidCode('USD'), 'boolean', 'isValidCurrency() returns boolean');
$t->is(sfISO4217::isValidCode('USD'), true, 'isValidCurrency() returns true for USD');
$t->is(sfISO4217::isValidCode('CZK'), true, 'isValidCurrency() returns true for CZK');
$t->is(sfISO4217::isValidCode('ABC'), false, 'isValidCurrency() returns true for invalid currency');

$t->isa_ok(sfISO4217::getCurrencyCodes(), 'array', 'getCurrencyCodes() returns an array');

$t->ok(count(sfISO4217::getCurrencyCodes()) == 100, 'getCurrencyCodes() returns array with 100 items');
