<?php

require_once(dirname(__FILE__).'/../../bootstrap/unit.php');

$t = new lime_test(12, new lime_output_color());

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

$array = array(
    'valid' => true,
    'callback' => 'function (a, b) { alert("this is my message"); $(\'#foobar\').show(\'fast\'); }'
);

$t->is(sfJson::encode($array, true), '{"valid":true,"callback":function (a, b) { alert("this is my message"); $(\'#foobar\').show(\'fast\'); }}', 'encode() encodes array with javascript expressions correctly');


class FooBar implements sfIJsonSerializable {

  public function jsonSerialize()
  {
    return array('bar');
  }

}

class Dummy implements sfIJsonSerializable {

  public function jsonSerialize()
  {
    return array('dummy' => 'yes');
  }
}

class DummyExpression implements sfIJsonSerializable {

  public function jsonSerialize()
  {
    return new sfJsonExpression('function() { alert("Jesus is Lord"); }');
  }
}

$foobar = new FooBar();
$dummy = new Dummy();

$t->is(sfJson::encode($foobar), '["bar"]', 'encode() can serialize objects which implement sfIJsonSerializable interface');
$t->is(sfJson::encode(array($foobar, $dummy)), '[["bar"],{"dummy":"yes"}]', 'encode() can serialize more elements which implement sfIJsonSerializable');

class myCollection extends sfCollection {}

$obj = new myCollection();
$obj->append($foobar);
$obj->append($dummy);

$t->is(sfJson::encode($obj), '{"0":["bar"],"1":{"dummy":"yes"}}', 'encode() can serialize a collection with more elements which implement sfIJsonSerializable');

$obj = new myCollection();
$obj->append(new DummyExpression());
$obj->append($dummy);

$t->is(sfJson::encode($obj), '{"0":function() { alert("Jesus is Lord"); },"1":{"dummy":"yes"}}', 'encode() can serialize a collection with more elements which implement sfIJsonSerializable and returns sfJsonExpression');

$t->diag('decode()');

$json = '{"Organization": "PHP Documentation Team"}';
$t->isa_ok(sfJson::decode($json), 'stdClass', 'encode() decodes object correctly');

$t->diag('sfJsonExpression');
$e = new sfJsonExpression('function() {}');
$t->is($e->__toString(), 'function() {}', '__toString() method returns expression string');
