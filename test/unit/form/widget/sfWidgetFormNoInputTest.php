<?php

require_once(dirname(__FILE__).'/../../../bootstrap/unit.php');

$t = new lime_test(7);

$w = new sfWidgetFormNoInput();

// ->render()
$t->diag('->render()');
$t->is($w->render('foo'), '<span class="form-no-input"></span>', '->render() renders the widget as HTML');
$t->is($w->render('foo', 'bar'), '<span class="form-no-input">bar</span>', '->render() can take a value for the input');
$t->is($w->render('foo', '', array('class' => 'foobar')), '<span class="foobar form-no-input"></span>', '->render() can take HTML attributes as its third argument');

$w = new sfWidgetFormNoInput(array(), array('class' => 'foobar'));
$t->is($w->render('foo'), '<span class="form-no-input"></span>', '__construct() can take default HTML attributes');
$t->is($w->render('foo', null, array('class' => 'barfoo')), '<span class="barfoo form-no-input"></span>', '->render() can override default attributes');

$w = new sfWidgetFormNoInput(array('tag' => 'div'), array('class' => 'foobar'));
$t->is($w->render('foo'), '<div class="form-no-input"></div>', '__construct() can take default HTML attributes');
$t->is($w->render('foo', null, array('class' => 'barfoo')), '<div class="barfoo form-no-input"></div>', '->render() can override default attributes');
