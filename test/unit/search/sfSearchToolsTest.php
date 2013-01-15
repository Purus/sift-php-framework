<?php

require_once(dirname(__FILE__).'/../../bootstrap/unit.php');

$t = new lime_test(2, new lime_output_color());

$t->diag('sfSearchTools');

$t->is(sfSearchTools::encodeSearchString('zkouška'), 'zkouška', 'Encoding search string works ok');

$encoded = sfSearchTools::encodeSearchString('zkouška ?');
$t->is('zkouška ?', sfSearchTools::decodeSearchString($encoded), 'Encoding and decoding search string returns the same string ');
