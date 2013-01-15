<?php

require_once(dirname(__FILE__).'/../../bootstrap/unit.php');
require_once($_test_dir.'/unit/sfContextMock.class.php');

$t = new lime_test(9, new lime_output_color());

$context = new sfContext();
$v = new sfPhoneNumberValidator();

// ->initialize()
$t->diag('->initialize()');


$t->ok($v->initialize($context, array('pattern' => '/\d+/')), '->initialize() takes a "pattern" as a parameter');
$t->ok($v->initialize($context, array('countries' => array('cs'))), '->initialize() takes a "countries" as a parameter');


$v->initialize($context, array('pattern' => '/\d+/', 'phone_error' => 'my error message'));
$value = 'a string';
$error = null;
$v->execute($value, $error);
$t->is($error, 'my error message', '->initialize() changes "$error" with a custom message if it returns false');

// ->execute()
$t->diag('->execute()');
$v->initialize($context, array('regex' => '/\d+/'));

$value = 12;
$error = null;
$t->ok($v->execute($value, $error), '->execute() returns true if value match the pattern');
$t->is($error, null, '->execute() doesn\'t change "$error" if it returns true');


$value = 'a string';
$error = null;
$t->ok(!$v->execute($value, $error), '->execute() returns false if value does not match the pattern');
$t->isnt($error, null, '->execute() changes "$error" with a default message if it returns false');

// match parameter
$v->initialize($context, array('country' => array('cs')));

$value = '774 868 002';
$error = null;
$t->is($v->execute($value, $error), true, '->execute() returns true for valid phone number in Czech republic');

$value = '+421 (0)2 4525 7673';
$error = null;
$t->is($v->execute($value, $error), true, '->execute() returns true for valid phone number is Slovakia');
