<?php

require_once(dirname(__FILE__) . '/../../bootstrap/unit.php');

$t = new lime_test(6);

$v = new sfValidatorIpAddress();

// ->clean()
$t->diag('->clean()');
$t->is($v->clean('127.0.0.1'), true, '->clean() returns valid result');

try {
  $v->clean('127.0.0');
  $t->fail('->clean() throws exception if ip address is not valid');
}
catch(sfValidatorError $e)
{
  $t->pass('->clean() throws exception if ip address is not valid');
}

try {
  $result = $v->clean('192.168.37.252');
  $t->pass('->clean() does not throw exception if ip address is valid');
  $t->is($result, true);
}
catch(sfValidatorError $e)
{
  $t->fail('->clean() throws exception if ip address is not valid');
  $t->skip('', 1);
}

// validate IPv6
$t->diag('IPV6');

$v = new sfValidatorIpAddress(array(
    'v6' => true
));

try {
  $v->clean('127.0.0.1');
  $t->fail('->clean() throws exception if ip address is not valid');
}
catch(sfValidatorError $e)
{
  $t->pass('->clean() throws exception if ip address is not valid');
}

try
{
  $v->clean('2001:0db8:85a3:0000:0000:8a2e:0370:7334');
  $t->pass('->clean() throws exception if ip address is not valid');
}
catch(sfValidatorError $e)
{
  $t->fail('->clean() throws exception if ip address is not valid');
}
