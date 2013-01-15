<?php

require_once(dirname(__FILE__).'/../../bootstrap/unit.php');

$t = new lime_test(6, new lime_output_color());

$integer = 16;

$t->diag('sfIntegerEncoder()');

$t->isa_ok(sfIntegerEncoder::encode($integer), 'string', '->encode() returns string');
$t->is(sfIntegerEncoder::encode($integer), 'i', '->encode() returns encoded integer');

$integer = 123456;
$t->is(sfIntegerEncoder::encode($integer), 'DHz', '->encode() returns encoded integer');
$t->isa_ok(sfIntegerEncoder::decode('DHz'), 'integer', '->decode() returns integer');
$t->is(sfIntegerEncoder::encode(999999), '79ho', '->encode() returns string for long integer');
$t->is(sfIntegerEncoder::decode('DHz'), 123456, '->decode() returns decoded integer');
