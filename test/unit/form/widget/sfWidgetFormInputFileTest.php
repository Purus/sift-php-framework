<?php

require_once(dirname(__FILE__).'/../../../bootstrap/unit.php');

$t = new lime_test(2);

$w = new sfWidgetFormInputFile();

// ->render()
$t->diag('->render()');
$t->is($w->render('foo'), '<input type="file" name="foo" id="foo" />', '->render() renders the widget as HTML');

sfWidget::setXhtml(false);

$w = new sfWidgetFormInputFile(array('multiple' => true));

// ->render()
$t->diag('->render()');
$t->is($w->render('foo'), '<input type="file" name="foo[]" multiple="multiple" id="foo">', '->render() renders the widget as HTML with multiple attribute');
