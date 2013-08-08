<?php

require_once(dirname(__FILE__) . '/../../../bootstrap/unit.php');

$t = new lime_test(51);

// ->configure()
$t->diag('->configure()');

try
{
  new sfValidatorPhoneNumber(array('countries' => array('EN')));
  $t->fail('->configure() throws an InvalidArgumentException if a country does not exist');
}
catch(InvalidArgumentException $e)
{
  $t->pass('->configure() throws an InvalidArgumentException if a country does not exist');
}

$v = new sfValidatorPhoneNumber(array('countries' => array('FR', 'GB')));

// ->clean()
$t->diag('->clean()');

$validTests = array(
  'CZ' => array('386 123 456', '905 951 234', '602123456', '774 123 456', '420774123456', '+420774123456', '+420.606.123.456'),
  'SK' => array('+421 2 4525 7673', '00421 2 5710 1800'),
  'US' => array('1-800-222-1222', '+1 650-253-0000', '1-800-222-1222', '1-800-GOT-MILK'),
  'FR' => array('+33(0)1 40 99 81 09'),
  'GB' => array('+44 20 3179 9555'),
  'DE' => array('+49 (0)221 - 66 99 27 50')
);

$expectedCleanedValues = array(
  'CZ' => array('+420386123456', '+420905951234', '+420602123456', '+420774123456', '+420774123456', '+420774123456', '+420606123456'),
  'SK' => array('+421245257673', '+421257101800'),
  'US' => array('+18002221222', '+16502530000', '+18002221222', '+18004686455'),
  'FR' => array('+33140998109'),
  'GB' => array('+442031799555'),
  'DE' => array('+4922166992750')
);

foreach($validTests as $country => $numbers)
{
  foreach($numbers as $i => $number)
  {
    try
    {
      $v = new sfValidatorPhoneNumber(array('countries' => array($country)));
      $cleaned = $v->clean($number);
      $t->pass(sprintf('clean() validates the phone number %s successfully for %s', $number, $country));
      $t->is($cleaned, $expectedCleanedValues[$country][$i], 'Cleaned value is as expected');
    }
    catch(sfValidatorError $e)
    {
      $t->fail(sprintf('clean() validates the phone number %s successfully for %s', $number, $country));
      $t->skip();
    }
  }
}

// all countries validation
$v = new sfValidatorPhoneNumber();

foreach($validTests as $country => $numbers)
{
  foreach($numbers as $number)
  {
    try
    {
      $v->clean($number);
      $t->pass(sprintf('clean() validates the phone number %s successfully', $number));
    }
    catch(sfValidatorError $e)
    {
      $t->fail(sprintf('clean() validates the phone number %s successfully', $number));
    }
  }
}

$t->diag('->getJavascriptValidationRules()');

$rules = $v->getJavascriptValidationRules();

$t->isa_ok($rules, 'array', 'getJavascriptValidationRules() returns array');

$t->like($rules['customCallback']['callback'], '/return result;/', 'getJavascriptValidationRules() returns some javascript rules');