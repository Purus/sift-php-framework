<?php

require_once(dirname(__FILE__) . '/../../bootstrap/unit.php');

$t = new lime_test(11);

$v = new sfValidatorEmail();

// ->clean()
$t->diag('->clean()');
foreach(array(
'fabien.potencier@symfony-project.com',
 'example@example.co.uk',
 'fabien_potencier@example.fr',
) as $url)
{
  $t->is($v->clean($url), $url, '->clean() checks that the value is a valid email');
}

foreach(array(
'example',
 'example@',
 'example@localhost',
 'example@example.com@example.com',
) as $nonUrl)
{
  try
  {
    $v->clean($nonUrl);
    $t->fail('->clean() throws an sfValidatorError if the value is not a valid email');
    $t->skip('', 1);
  }
  catch(sfValidatorError $e)
  {
    $t->pass('->clean() throws an sfValidatorError if the value is not a valid email');
    $t->is($e->getCode(), 'invalid', '->clean() throws a sfValidatorError');
  }
}
