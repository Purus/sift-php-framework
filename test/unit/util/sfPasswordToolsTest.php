<?php

require_once(dirname(__FILE__).'/../../bootstrap/unit.php');

$t = new lime_test(6, new lime_output_color());

$t->isa_ok(sfPasswordTools::generatePassword(), 'string', '->generatePassword() returns string');
$t->is(strlen(sfPasswordTools::generatePassword()), 8, '->generatePassword() returns 8 chars length string');
$t->is(strlen(sfPasswordTools::generatePassword(12)), 12, '->generatePassword() returns 12 chars length string if length is specified');

$t->is(strlen(sfPasswordTools::generatePassword(63, sfPasswordTools::PASSWORD_UNPRONOUNCEABLE)), 63, '->generatePassword() returns 63 chars length string if length is specified for nonpronounceable password');
$t->is(strlen(sfPasswordTools::generatePassword(64, sfPasswordTools::PASSWORD_PRONOUNCEABLE)), 64, '->generatePassword() returns 64 chars length string if length is specified for pronounceable password');
$t->is(strlen(sfPasswordTools::generatePassword(10, 'unpronounceable')), 10, '->generatePassword() returns 64 chars length string if length is specified for pronounceable password');

