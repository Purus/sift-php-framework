<?php

require_once(dirname(__FILE__).'/../../../bootstrap/unit.php');

$t = new lime_test(4);

$w = new sfWidgetFormInputPassword();

// ->render()
$t->diag('->render()');
$t->is($w->render('foo'), '<input type="password" name="foo" id="foo" />', '->render() renders the widget as HTML');

$t->is($w->render('foo', 'bar'), '<input type="password" name="foo" id="foo" />', '->render() renders the widget as HTML');

$w->setOption('always_render_empty', false);
$t->is($w->render('foo', 'bar'), '<input type="password" name="foo" value="bar" id="foo" />', '->render() renders the widget as HTML');

$t->diag('strength meter');
$w->setOption('strength_meter', true);

$meter = '<div class="password-strength-meter"><div class="password-strength-meter-bar"></div></div>';

$t->is($w->render('foo', 'bar'), '<input type="password" name="foo" value="bar" id="foo" />' . "\n" . $meter, '->render() renders the widget with strength meter');
