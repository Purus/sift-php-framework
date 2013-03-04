<?php

require_once dirname(__FILE__).'/../../bootstrap/unit.php';

$t = new lime_test(12);

class myGenerator implements sfIGenerator {

  public function generate()
  {
  }

  public function setModuleName($moduleName)
  {
  }

  public function getModuleName()
  {
  }

}

class myGeneratorColumn extends sfGeneratorColumn {}

// ->isPartial() ->isComponent() ->isLink()
$t->diag('->isPartial() ->isComponent() ->isLink()');

$generator = new myGenerator();

$field = new myGeneratorColumn($generator, 'my_field', array());
$t->is($field->isPartial(), false, '->isPartial() defaults to false');
$t->is($field->isComponent(), false, '->isComponent() defaults to false');
$t->is($field->isLink(), false, '->isLink() defaults to false');

$field = new myGeneratorColumn($generator, 'my_field', array(), array('_'));
$t->is($field->isPartial(), true, '->isPartial() returns true if flag is "_"');
$t->is($field->isComponent(), false, '->isComponent() defaults to false');
$t->is($field->isLink(), false, '->isLink() defaults to false');

$field = new myGeneratorColumn($generator, 'my_field', array(), array('~'));
$t->is($field->isPartial(), false, '->isPartial() defaults to false');
$t->is($field->isComponent(), true, '->isComponent() returns true if flag is "~"');
$t->is($field->isLink(), false, '->isLink() defaults to false');

$field = new myGeneratorColumn($generator, 'my_field', array(), array('='));

$t->is($field->isPartial(), false, '->isPartial() defaults to false');
$t->is($field->isComponent(), false, '->isComponent() defaults to false');
$t->is($field->isLink(), true, '->isLink() returns true if flag is "="');
