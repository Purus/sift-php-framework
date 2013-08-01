<?php

require_once(dirname(__FILE__).'/../../bootstrap/unit.php');

$t = new lime_test(6, new lime_output_color());

$t->isa_ok(sfISO4217::isValidCode('USD'), 'boolean', 'isValidCode() returns boolean');
$t->is(sfISO4217::isValidCode('USD'), true, 'isValidCode() returns true for USD');
$t->is(sfISO4217::isValidCode('CZK'), true, 'isValidCode() returns true for CZK');
$t->is(sfISO4217::isValidCode('ABC'), false, 'isValidCode() returns true for invalid currency');

$t->isa_ok(sfISO4217::getCurrencyCodes(), 'array', 'getCurrencyCodes() returns an array');

$t->is(count(sfISO4217::getCurrencyCodes()), 182, 'getCurrencyCodes() returns array with 182 items');
