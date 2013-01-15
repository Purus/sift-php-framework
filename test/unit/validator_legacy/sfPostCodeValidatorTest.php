<?php

require_once(dirname(__FILE__).'/../../bootstrap/unit.php');
require_once($_test_dir.'/unit/sfContextMock.class.php');

$t = new lime_test(13, new lime_output_color());

$context = new sfContext();
$v = new sfPostCodeValidator();

// ->initialize()
$t->diag('->initialize()');

try
{
  $v->initialize($context);
  $t->fail('->initialize() takes a mandatory "pattern" parameter or array of countries parameter');
}
catch (sfValidatorException $e)
{
  $t->pass('->initialize() takes a mandatory "pattern" parameter or array of countries parameter');
}

$t->ok($v->initialize($context, array('pattern' => '/\d+/')), '->initialize() takes a "pattern" as a parameter');
$t->ok($v->initialize($context, array('countries' => array('cs'))), '->initialize() takes a "countries" as a parameter');

$v->initialize($context, array('pattern' => '/\d+/', 'post_code_error' => 'my error message'));
$value = 'a string';
$error = null;
$v->execute($value, $error);
$t->is($error, 'my error message', '->initialize() changes "$error" with a custom message if it returns false');

// ->execute()
$t->diag('->execute()');
$v->initialize($context, array('pattern' => '/\d+/'));

$value = 12;
$error = null;
$t->ok($v->execute($value, $error), '->execute() returns true if value match the pattern');
$t->is($error, null, '->execute() doesn\'t change "$error" if it returns true');


$value = 'a string';
$error = null;
$t->ok(!$v->execute($value, $error), '->execute() returns false if value does not match the pattern');
$t->isnt($error, null, '->execute() changes "$error" with a default message if it returns false');

// match parameter
$v->initialize($context, array('countries' => array('cz')));

$value = '370 01';
$error = null;
$t->is($v->execute($value, $error), true, '->execute() returns true for valid post code in czech republic');

$value = '37001';
$error = null;
$t->is($v->execute($value, $error), true, '->execute() returns true for valid post code in czech republic');

// match parameter
$v->initialize($context, array('countries' => array('sk')));

$value = '974 01';
$error = null;
$t->is($v->execute($value, $error), true, '->execute() returns true for valid post code in slovakia');

$v->initialize($context, array('countries' => 'all'));

// Belleplaine Housing Area (WALKERS)
// http://en.wikipedia.org/wiki/List_of_postal_codes_in_Barbados
$value = 'BB25029';
$error = null;
$t->is($v->execute($value, $error), true, '->execute() returns true for valid post code in Barbados');

// Canada
$value = 'A0A 1A0';
$error = null;
$t->is($v->execute($value, $error), true, '->execute() returns true for valid post code in Canada');
