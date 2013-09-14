<?php

require_once(dirname(__FILE__).'/../../bootstrap/unit.php');

$t = new lime_test(2, new lime_output_color());

// FIXME: test.php does not work yet!

$tests = array(
  'test2.php' => array(
    '___UNKNOWN_DOMAIN___' =>
      array (
        'If you have an account here, please %%login%%',
        'login',
        'If you do not have an account here, please %%register%%',
        'register',
        'Please %%call%%',
        'call',
      ),
  ),
);

class myExtractor extends sfI18nPhpExtractor {

  public function findFunctionCalls($function_names, $code)
  {
    return parent::findFunctionCalls($function_names, $code);
  }
}

$extractor = new myExtractor();
$functionCalls = $extractor->findFunctionCalls(array('__'), file_get_contents(dirname(__FILE__) . '/fixtures/test2.php'));

$t->is($functionCalls, array (
  0 =>
  array (
    'name' => '__',
    'args' =>
    array (
      0 => 'If you have an account here, please %%login%%',
    ),
    'line' => 1,
  ),
  1 =>
  array (
    'name' => '__',
    'args' =>
    array (
      0 => 'login',
    ),
    'line' => 2,
  ),
  2 =>
  array (
    'name' => '__',
    'args' =>
    array (
      0 => 'If you do not have an account here, please %%register%%',
    ),
    'line' => 7,
  ),
  3 =>
  array (
    'name' => '__',
    'args' =>
    array (
      0 => 'register',
    ),
    'line' => 8,
  ),
  4 =>
  array (
    'name' => '__',
    'args' =>
    array (
      0 => 'Please %%call%%',
    ),
    'line' => 11,
  ),
  5 =>
  array (
    'name' => '__',
    'args' =>
    array (
      0 => 'call',
    ),
    'line' => 12,
  ),
), '->findFunctionCalls() works ok');

foreach($tests as $file => $expected)
{
  $messages = $extractor->extract(file_get_contents(dirname(__FILE__) . '/fixtures/'.$file));
  $t->is($messages, $expected, sprintf('->extract() returns messages for "%s"', $file));
}
