<?php

require_once(dirname(__FILE__).'/../../../bootstrap/unit.php');

$t = new lime_test(2);

$w = new sfWidgetFormInputHidden();

// ->render()
$t->diag('->render()');
$t->is($w->render('foo'), '<input type="hidden" name="foo" id="foo" />', '->render() renders the widget as HTML');

// ->isHidden()
$t->diag('->isHidden()');
$t->is($w->isHidden(), true, '->isHidden() returns true');
