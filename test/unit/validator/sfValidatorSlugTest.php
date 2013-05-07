<?php

require_once(dirname(__FILE__) . '/../../bootstrap/unit.php');

$t = new lime_test(3);

// ->clean()
$t->diag('->clean()');

$v = new sfValidatorSlug();

try
{
  $v->clean('this is an invalid slugaaa');
  $t->fail('->clean() checks that the value is valid slug');
}
catch(sfValidatorError $e)
{
  $t->pass('->clean() checks that the value is valid slug');
}

try
{
  $v->clean('this-is-a-valid-slugaaa');
  $t->pass('->clean() checks that the value is valid slug');
}
catch(sfValidatorError $e)
{
  $t->fail('->clean() checks that the value is valid slug');
}

try
{
  $v->clean('slugaaa12');
  $t->pass('->clean() checks that the value is valid slug');
}
catch(sfValidatorError $e)
{
  $t->fail('->clean() checks that the value is valid slug');
}

