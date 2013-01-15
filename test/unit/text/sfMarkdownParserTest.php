<?php

require_once(dirname(__FILE__).'/../../bootstrap/unit.php');

$t = new lime_test(3, new lime_output_color());

$fixturesDir = dirname(__FILE__) . '/fixtures';
$markdown    = file_get_contents($fixturesDir . '/text.markdown');
$result      = file_get_contents($fixturesDir . '/text_result.html');

file_put_contents($fixturesDir . '/text_result.html', $result);

$parser = new sfMarkdownParser();
$html = $parser->transform($markdown);

$t->isa_ok($html, 'string', '->transform() returns string');
$t->is($result, $html, '->transform() works ok');

$code = file_get_contents($fixturesDir . '/code.markdown');
$codeHtml = file_get_contents($fixturesDir . '/code.html');

$result = $parser->transform($code);

$t->is($result, $codeHtml, '->transform() works ok');
