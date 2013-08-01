<?php

require_once(dirname(__FILE__).'/../../bootstrap/unit.php');

$t = new lime_test(2, new lime_output_color());

$t->isa_ok(sfISO3166::isValidCode('CZ'), 'boolean', 'isValidCode() returns boolean');
$t->is(count(sfISO3166::getCountryCodes()), 249, 'getCountryCodes() returns array with 249 items');
