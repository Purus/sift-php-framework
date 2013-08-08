<?php

require_once(dirname(__FILE__).'/../../bootstrap/unit.php');

$t = new lime_test(6, new lime_output_color());

$t->isa_ok(sfISO3166::isValidCode('CZ'), 'boolean', 'isValidCode() returns boolean');
$t->is(count(sfISO3166::getCountryCodes()), 249, 'getCountryCodes() returns array with 249 items');

$t->diag('->getEuropeanUnionCountries()');

$t->isa_ok(sfISO3166::getEuropeanUnionCountries(), 'array', '->getEuropeanUnionCountries() returns an array');

$t->diag('->isInEuropeanUnion()');

$t->isa_ok(sfISO3166::isInEuropeanUnion('CZ'), 'boolean', '->isInEuropeanUnion() returns boolean');

$t->is(sfISO3166::isInEuropeanUnion('CZ'), true, '->isInEuropeanUnion() returns correct value');

$t->is(sfISO3166::isInEuropeanUnion('US'), false, '->isInEuropeanUnion() returns correct value');