<?php

require_once(dirname(__FILE__).'/../../bootstrap/unit.php');

$t = new lime_test(5, new lime_output_color());

$t->diag('->encodeSearchString()');

$t->is(sfSearchTools::encodeSearchString('zkouška'), 'zkouška', 'Encoding search string works ok');

$encoded = sfSearchTools::encodeSearchString('zkouška ?');
$t->is('zkouška ?', sfSearchTools::decodeSearchString($encoded), 'Encoding and decoding search string returns the same string ');

$t->diag('->highlight();');

$text = 'This is a text';
$expression = new sfSearchQueryExpression();
$expression->addPhrase('text');

$t->is(sfSearchTools::highlight($text, $expression), 'This is a <span class="search-highlighted">text</span>', 'highlighting the plain text works ok');

$t->todo('make the highlighter to work with html');
$t->skip();

// $text = 'This is a <a href="/text/1/">text</a>';
// $t->is(sfSearchTools::highlight($text, $expression), 'This is a <a href="/text/1/"><span class="search-highlighted">text</span></a>', 'highlighting the html works ok');