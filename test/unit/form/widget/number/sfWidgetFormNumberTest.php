<?php
require_once(dirname(__FILE__) . '/../../../../bootstrap/unit.php');

$t = new lime_test(14);

sfConfig::set('sf_html5', true);

$w = new sfWidgetFormNumber();

$t->diag('HTML5 enabled');

// ->render()
$t->diag('->render()');
$t->is($w->render('foo'), '<input class="number" type="number" name="foo" id="foo" />', '->render() renders the widget as HTML');
$t->is($w->render('foo', 'bar'), '<input class="number" type="number" name="foo" value="bar" id="foo" />', '->render() can take a value for the input');
$t->is($w->render('foo', '', array('class' => 'foobar')), '<input class="foobar" type="number" name="foo" value="" id="foo" />', '->render() can take HTML attributes as its third argument');

$w = new sfWidgetFormNumber(array(), array('class' => 'foobar'));

$t->is($w->render('foo'), '<input class="foobar" type="number" name="foo" id="foo" />', '__construct() can take default HTML attributes');
$t->is($w->render('foo', null, array('class' => 'barfoo')), '<input class="barfoo" type="number" name="foo" id="foo" />', '->render() can override default attributes');

$w = new sfWidgetFormnumber(array(
  'step' => 1,
  'min' => -10,
  'max' => 100
  ), array('class' => 'foobar'));

$t->is($w->render('foo'), '<input class="foobar" type="number" name="foo" min="-10" max="100" step="1" id="foo" />', '__construct() can take default HTML attributes');
$t->is($w->render('foo', null, array('class' => 'barfoo')), '<input class="barfoo" type="number" name="foo" min="-10" max="100" step="1" id="foo" />', '->render() can override default attributes');

$t->diag('HTML5 disabled');

sfConfig::set('sf_html5', false);

$w = new sfWidgetFormNumber();

// ->render()
$t->diag('->render()');
$t->is($w->render('foo'), '<input class="number" type="text" name="foo" id="foo" />', '->render() renders the widget as HTML');
$t->is($w->render('foo', 'bar'), '<input class="number" type="text" name="foo" value="bar" id="foo" />', '->render() can take a value for the input');
$t->is($w->render('foo', '', array('class' => 'foobar')), '<input class="foobar" type="text" name="foo" value="" id="foo" />', '->render() can take HTML attributes as its third argument');

$w = new sfWidgetFormNumber(array(), array('class' => 'foobar'));

$t->is($w->render('foo'), '<input class="foobar" type="text" name="foo" id="foo" />', '__construct() can take default HTML attributes');
$t->is($w->render('foo', null, array('class' => 'barfoo')), '<input class="barfoo" type="text" name="foo" id="foo" />', '->render() can override default attributes');

$w = new sfWidgetFormNumber(array(
  'step' => 1,
  'min' => -10,
  'max' => 100
  ), array('class' => 'foobar'));

$t->is($w->render('foo'), '<input class="foobar" type="text" name="foo" id="foo" />', '__construct() can take default HTML attributes');
$t->is($w->render('foo', null, array('class' => 'barfoo')), '<input class="barfoo" type="text" name="foo" id="foo" />', '->render() can override default attributes');
