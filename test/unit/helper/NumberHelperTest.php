<?php

require_once(dirname(__FILE__).'/../../bootstrap/unit.php');

sfLoader::loadHelpers(array('Helper', 'Tag', 'Number'));

$t = new lime_test(9, new lime_output_color());

// format_number()
$t->diag('format_number()');
$t->is(format_number(10012.1, 'en'), '10,012.1', 'format_number() takes a number as its first argument');

// format_currency()
$t->is(format_currency(1200000.00, 'USD', 'en'), '$1,200,000.00', 'format_currency() takes a number as its first argument');
$t->is(format_currency(1200000.1, 'USD', 'en'), '$1,200,000.10', 'format_currency() takes a number as its first argument');
$t->is(format_currency(1200000.10, 'USD', 'en'), '$1,200,000.10', 'format_currency() takes a number as its first argument');
$t->is(format_currency(1200000.101, 'USD', 'en'), '$1,200,000.10', 'format_currency() takes a number as its first argument');
$t->is(format_currency('1200000', 'USD', 'en'), '$1,200,000.00', 'format_currency() takes a number as its first argument');

// czech
$t->is(format_currency('1200000', 'USD', 'cs'), '1 200 000,00 US$', 'format_currency() takes a number as its first argument');
$t->is(format_currency('1200000', 'CZK', 'cs_CZ'), '1 200 000,00 Kč', 'format_currency() takes a number as its first argument');

$t->is(format_currency('1200000', 'CZK', 'sk'), '1 200 000,00 CZK', 'format_currency() takes a number as its first argument');
