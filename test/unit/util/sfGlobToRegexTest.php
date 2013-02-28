<?php

require_once(dirname(__FILE__).'/../../bootstrap/unit.php');

$t = new lime_test(11, new lime_output_color());

$t->isa_ok(sfGlobToRegex::toRegex('match'), 'string', '->toRegex() returns string');

// returns correct string
$t->is(sfGlobToRegex::toRegex('foo.*'), '#^(?=[^\\.])foo\\.[^/]*$#', '->toRegex() returns string');

$t->is(preg_match(sfGlobToRegex::toRegex('foo.*'), 'foo.bar'), true, 'preg_match() works ok with converted glob pattern');
$t->is(sfGlobToRegex::match('foo.*', 'foo.bar'), true, '->match() works ok with converted glob pattern');

sfGlobToRegex::match('foo.*', 'foo.bar', $matches);
$t->isa_ok($matches, 'array', '->match() works ok with converted glob pattern');
$t->is_deeply($matches, array('foo.bar'), '->match() works ok with converted glob pattern');

$t->is(sfGlobToRegex::match('/path/*{foo,bar}.dat', '/path/foo.dat'), true, 'brace expressions works ok');
$t->is(sfGlobToRegex::match('.*', '.git', $matches), true, 'brace expressions works ok');
$t->is_deeply($matches, array('.git'), '->matchAll() works ok with converted glob pattern');

sfGlobToRegex::matchAll('foo.*', 'foo.bar', $matches);
$t->isa_ok($matches, 'array', '->matchAll() works ok with converted glob pattern');
$t->is_deeply($matches, array( 0 => array('foo.bar')), '->matchAll() works ok with converted glob pattern');
