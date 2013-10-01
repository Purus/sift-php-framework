<?php

require_once(dirname(__FILE__).'/../../bootstrap/unit.php');

$t = new lime_test(2, new lime_output_color());

$t->isa_ok(sfUuid::generate(), 'string', '->sfUuid::generate() returns string');

$uuid  = sfUuid::generate();
$valid = preg_match('/^\{?[0-9a-f]{8}\-?[0-9a-f]{4}\-?[0-9a-f]{4}\-?'.'[0-9a-f]{4}\-?[0-9a-f]{12}\}?$/i', $uuid) === 1;
$t->is($valid, true, '->sfUuid::generate() returns valid uuid string');
