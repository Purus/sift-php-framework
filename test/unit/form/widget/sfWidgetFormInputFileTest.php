<?php

require_once(dirname(__FILE__).'/../../../bootstrap/unit.php');

$t = new lime_test(1);

$w = new sfWidgetFormInputFile();

// ->render()
$t->diag('->render()');
$t->is($w->render('foo'), '<input type="file" name="foo" id="foo" />', '->render() renders the widget as HTML');