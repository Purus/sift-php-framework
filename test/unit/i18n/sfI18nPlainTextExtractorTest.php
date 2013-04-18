<?php

require_once(dirname(__FILE__).'/../../bootstrap/unit.php');

$t = new lime_test(1, new lime_output_color());

$tests = array(
  'test.txt' => array(
    'Hello world',
    'Jesus is my Lord',
    'Create new "%%title%%"',
    'Hello',
    'This is a hash #1',
  ),
);

$extractor = new sfI18nPlainTextExtractor();

foreach($tests as $file => $expected)
{
  $messages = $extractor->extract(file_get_contents(dirname(__FILE__) . '/fixtures/'.$file));
  $t->is($messages, $expected, sprintf('->extract() returns messages for "%s"', $file));
}

