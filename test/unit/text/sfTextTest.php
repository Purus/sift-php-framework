<?php

require_once(dirname(__FILE__).'/../../bootstrap/unit.php');

$t = new lime_test(3, new lime_output_color());

$t->diag('->isHtml()');

$string = 'This is a plain text';
$t->is_deeply(sfText::isHtml($string), false, '->isHtml() works for plain text');

$string = 'This is a plain text, but I think that 2 < 1';
$t->is_deeply(sfText::isHtml($string), false, '->isHtml() works for plain text');

$string = 'This is a rich <strong>text</strong>';
$t->is_deeply(sfText::isHtml($string), true, '->isHtml() works for plain text');
