<?php

require_once(dirname(__FILE__).'/../../bootstrap/unit.php');

$t = new lime_test(4, new lime_output_color());

$class = 'foo';
$t->diag('->addCssClass');

$t->is(sfHtml::addCssClass('another', $class), 'another foo', 'addCssClass() works ok');
$t->is(sfHtml::addCssClass('another foobar', $class), 'another foobar foo', 'addCssClass() works ok');
$t->is(sfHtml::addCssClass(array('another', 'foobar', 'has-upload'), $class), 'another foobar has-upload foo', 'addCssClass() works ok with array');

$t->diag('->attributesToHtml()');

$t->is(sfHtml::attributesToHtml(array(
  'value' => ''
)), ' value=""', '');
