<?php

require_once(dirname(__FILE__).'/../../bootstrap/unit.php');

$t = new lime_test(2);

$form = new sfForm();

$widget = new sfWidgetFormInputText();
$form->setWidget('field1', $widget);

$group = new sfFormFieldGroup($form, 'Group', array('field1'));
$fields = $group->getFields();
$t->is($fields, array('field1' => $form['field1']), '->getFields() returns fields');

$form->setWidget('field2', $widget);

$group = new sfFormFieldGroup($form, 'Group', array('field1', 'field2'));

$t->is(count($group), 2, 'count() works on the group object');