<?php

require_once(dirname(__FILE__).'/../../bootstrap/unit.php');
require_once(dirname(__FILE__).'/../../../lib/json/sfJson.class.php');
require_once(dirname(__FILE__).'/../../../lib/json/sfJsonExpression.class.php');


$t = new lime_test(7, new lime_output_color());

$string = 'Toto je string';
$encodedString = '"Toto je string"';

$t->diag('encode()');

$t->is(sfJson::encode($string), $encodedString, 'encode() encodes string correctly');

$stringUtf8 = 'Toto je koňský salám';
$encodedStringUtf8 = '"Toto je ko\\u0148sk\\u00fd sal\\u00e1m"';

$t->is(sfJson::encode($stringUtf8), $encodedStringUtf8, 'encode() encodes UTF8 string correctly');

$stringUtf8 = 'Toto je koňský salám';
$encodedStringUtf8 = '"Toto je ko\\u0148sk\\u00fd sal\\u00e1m"';

$foo = new stdClass();
$foo->bar = 'yes';
$t->is(sfJson::encode($foo), '{"bar":"yes"}', 'encode() encodes object correctly');

$array = array(
    'valid' => true,
    'callback' => 'function() { alert("this is my message"); $(\'#foobar\').show(\'fast\'); }'
);

$t->is(sfJson::encode($array, true), '{"valid":true,"callback":function() { alert("this is my message"); $(\'#foobar\').show(\'fast\'); }}', 'encode() encodes array with javascript expressions correctly');

$array = array(
  'valid' => true,
  'callback' => new sfJsonExpression('function() { alert("this is my message"); $(\'#foobar\').show(\'fast\'); }')
);

$t->is(sfJson::encode($array, true), '{"valid":true,"callback":function() { alert("this is my message"); $(\'#foobar\').show(\'fast\'); }}', 'encode() encodes array with javascript expressions objects correctly');

$t->diag('decode()');

$json = '{"Organization": "PHP Documentation Team"}';
$t->isa_ok(sfJson::decode($json), 'stdClass', 'encode() decodes object correctly');


$t->diag('sfJsonExpression');

$e = new sfJsonExpression('function() {}');
$t->is($e->__toString(), 'function() {}', '__toString() method returns expression string');