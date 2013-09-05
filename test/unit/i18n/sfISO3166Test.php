<?php

require_once(dirname(__FILE__).'/../../bootstrap/unit.php');

$t = new lime_test(16, new lime_output_color());

$t->isa_ok(sfISO3166::isValidCode('CZ'), 'boolean', 'isValidCode() returns boolean');
$t->is(count(sfISO3166::getCountryCodes()), 246, 'getCountryCodes() returns array with 249 items');

$t->diag('->getEuropeanUnionCountries()');

$t->isa_ok(sfISO3166::getEuropeanUnionCountries(), 'array', '->getEuropeanUnionCountries() returns an array');

$t->diag('->isInEuropeanUnion()');

$t->isa_ok(sfISO3166::isInEuropeanUnion('CZ'), 'boolean', '->isInEuropeanUnion() returns boolean');

$t->is(sfISO3166::isInEuropeanUnion('CZ'), true, '->isInEuropeanUnion() returns correct value');

$t->is(sfISO3166::isInEuropeanUnion('US'), false, '->isInEuropeanUnion() returns correct value');

$t->diag('alpha3');

$t->isa_ok(sfISO3166::isValidCode('CZE', sfISO3166::ALPHA3), 'boolean', 'isValidCode() returns boolean');
$t->is(count(sfISO3166::getCountryCodes(sfISO3166::ALPHA3)), 246, 'getCountryCodes() returns array with 249 items');

$t->isa_ok(sfISO3166::isInEuropeanUnion('CZE', sfISO3166::ALPHA3), 'boolean', '->isInEuropeanUnion() returns boolean');
$t->is(sfISO3166::isInEuropeanUnion('CZE', sfISO3166::ALPHA3), true, '->isInEuropeanUnion() returns correct value');

$t->diag('->code2ToCode3()');

$t->isa_ok(sfISO3166::code2ToCode3('CZ'), 'string', '->code2ToCode3() returns string');
$t->is_deeply(sfISO3166::code2ToCode3('INVALID'), false, '->code2ToCode3() returns false for invalid code');

$t->is(sfISO3166::code2ToCode3('CZ'), 'CZE', '->code2ToCode3() returns string');

$t->diag('->code3ToCode2()');

$t->isa_ok(sfISO3166::code3ToCode2('CZE'), 'string', '->code2ToCode3() returns string');
$t->is_deeply(sfISO3166::code3ToCode2('INVALID'), false, '->code2ToCode3() returns false for invalid code');

$t->is(sfISO3166::code3ToCode2('CZE'), 'CZ', '->code2ToCode3() returns string');
