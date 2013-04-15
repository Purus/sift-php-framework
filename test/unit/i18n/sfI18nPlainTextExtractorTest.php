<?php

require_once(dirname(__FILE__).'/../../bootstrap/unit.php');

$t = new lime_test(2, new lime_output_color());

$tests = array(
  'test.js' => array(
    'Hello world, testing jsgettext',
    'string 2: double quotes',
    '/* comment in string */',
    'regexp in string: /[a-z]+/',
    'string 2: "escaped double quotes"',
    'Test string',
    'string 1: single quotes',
    'string 2: \'escaped single quotes\'',
    'Jesus is Lord!',
  ),
  'arguments.js' => array(
    'Please enter %number% more characters.',
    'Please enter %number% more characters with arguments.',
  )
);

$extractor = new sfI18nJavascriptExtractor(array(

));

foreach($tests as $file => $expected)
{
  $messages = $extractor->extract(file_get_contents(dirname(__FILE__) . '/fixtures/'.$file));
  $t->is($messages, $expected, sprintf('->extract() returns messages for "%s"', $file));
}

