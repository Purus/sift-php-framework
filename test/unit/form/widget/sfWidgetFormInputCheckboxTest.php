<?php

require_once(dirname(__FILE__).'/../../../bootstrap/unit.php');

$t = new lime_test(14);

$w = new sfWidgetFormInputCheckbox();

sfWidgetForm::setAria(false);

$t->diag('Aria disabled');

// ->render()
$t->diag('->render()');
$t->is($w->render('foo', 1), '<input type="checkbox" name="foo" checked="checked" id="foo" />', '->render() renders the widget as HTML');
$t->is($w->render('foo', null), '<input type="checkbox" name="foo" id="foo" />', '->render() renders the widget as HTML');
$t->is($w->render('foo', false), '<input type="checkbox" name="foo" id="foo" />', '->render() renders the widget as HTML');
$t->is($w->render('foo', 0, array('value' => '0')), '<input type="checkbox" name="foo" value="0" checked="checked" id="foo" />', '->render() renders the widget as HTML');

$w = new sfWidgetFormInputCheckbox(array(), array('value' => 'bar'));
$t->is($w->render('foo', null), '<input value="bar" type="checkbox" name="foo" id="foo" />', '->render() renders the widget as HTML');
$t->is($w->render('foo', null, array('value' => 'baz')), '<input value="baz" type="checkbox" name="foo" id="foo" />', '->render() renders the widget as HTML');
$t->is($w->render('foo', 'bar'), '<input value="bar" type="checkbox" name="foo" checked="checked" id="foo" />', '->render() renders the widget as HTML');

$t->diag('Aria enabled');

sfWidgetForm::setAria(true);

$w = new sfWidgetFormInputCheckbox();

$t->diag('->render()');
$t->is($w->render('foo', 1), '<input type="checkbox" name="foo" checked="checked" aria-checked="true" id="foo" />', '->render() renders the widget as HTML');
$t->is($w->render('foo', null), '<input type="checkbox" name="foo" id="foo" />', '->render() renders the widget as HTML');
$t->is($w->render('foo', false), '<input type="checkbox" name="foo" id="foo" />', '->render() renders the widget as HTML');
$t->is($w->render('foo', 0, array('value' => '0')), '<input type="checkbox" name="foo" value="0" checked="checked" aria-checked="true" id="foo" />', '->render() renders the widget as HTML');

$w = new sfWidgetFormInputCheckbox(array(), array('value' => 'bar'));
$t->is($w->render('foo', null), '<input value="bar" type="checkbox" name="foo" id="foo" />', '->render() renders the widget as HTML');
$t->is($w->render('foo', null, array('value' => 'baz')), '<input value="baz" type="checkbox" name="foo" id="foo" />', '->render() renders the widget as HTML');
$t->is($w->render('foo', 'bar'), '<input value="bar" type="checkbox" name="foo" checked="checked" aria-checked="true" id="foo" />', '->render() renders the widget as HTML');
